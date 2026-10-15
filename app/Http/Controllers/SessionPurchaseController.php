<?php

namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Models\Ingredient;
use App\Models\IngredientStock;
use App\Models\Product;
use App\Models\Sation;
use App\Models\SessionPurchase;
use App\Models\Shift;
use App\Models\SystemAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SessionPurchaseController extends Controller
{
  /**
   * خصم المنتجات / الخامات من المخزن
   */
  protected function deductStock(Product $product, int|float $qty, $sessionId, string $note): void
  {
    if ($product->is_produced) {
      $product->loadMissing('recipe.ingredient');
      foreach ($product->recipe as $recipeItem) {
        $ingredient = $recipeItem->ingredient;
        if ($ingredient) {
          $before = (float) $ingredient->stock;
          $deductAmount = (float) ($recipeItem->amount * $qty);
          $after = max(0, $before - $deductAmount);
          $ingredient->update(['stock' => $after]);

          IngredientStock::create([
            'ingredient_id' => $ingredient->id,
            'amount'        => $deductAmount,
            'unit_id'       => $recipeItem->unit_id ?? $ingredient->unit_id,
            'type'          => 'out',
            'before_amount' => $before,
            'after_amount'  => $after,
            'note'          => $note . " ({$product->name} x{$qty})",
          ]);
        }
      }
    } else {
      $product->decrement('quantity', $qty);
      if ($product->quantity < 0) {
        $product->quantity = 0;
        $product->save();
      }
    }
  }

  /**
   * استرجاع المنتجات / الخامات إلى المخزن (عند الحذف أو تقليل الكمية)
   */
  protected function restoreStock(Product $product, int|float $qty, $sessionId, string $note): void
  {
    if ($product->is_produced) {
      $product->loadMissing('recipe.ingredient');
      foreach ($product->recipe as $recipeItem) {
        $ingredient = $recipeItem->ingredient;
        if ($ingredient) {
          $before = (float) $ingredient->stock;
          $restoreAmount = (float) ($recipeItem->amount * $qty);
          $after = $before + $restoreAmount;
          $ingredient->update(['stock' => $after]);

          IngredientStock::create([
            'ingredient_id' => $ingredient->id,
            'amount'        => $restoreAmount,
            'unit_id'       => $recipeItem->unit_id ?? $ingredient->unit_id,
            'type'          => 'in',
            'before_amount' => $before,
            'after_amount'  => $after,
            'note'          => $note . " ({$product->name} x{$qty})",
          ]);
        }
      }
    } else {
      $product->increment('quantity', $qty);
    }
  }

  /**
   * تحديث كميات المشتريات وحذف العناصر مع مزامنة المخزن
   */
  public function updatePurchases(Request $request, $sessionId)
  {
    $quantities = $request->input('quantities', []);
    $removed = json_decode($request->input('removed', '[]'), true);

    DB::beginTransaction();
    try {
      // 🟩 تحديث الكميات واسترجاع/خصم الفرق في المخزن
      foreach ($quantities as $purchaseId => $newQty) {
        $newQty = max(1, (int) $newQty);
        $purchase = SessionPurchase::with('product.recipe.ingredient')
          ->where('id', $purchaseId)
          ->where('sation_id', $sessionId)
          ->first();

        if (!$purchase) continue;

        $oldQty = (int) $purchase->quantity;
        $diff = $newQty - $oldQty;

        if ($diff != 0) {
          $product = $purchase->product;
          if ($product) {
            if ($diff > 0) {
              // زيادة في الكمية: خصم إضافي من المخزن
              $this->deductStock($product, $diff, $sessionId, "تعديل بالزيادة لمشتريات جلسة #{$sessionId}");
            } else {
              // تقليل في الكمية: استرجاع الفارق للمخزن
              $this->restoreStock($product, abs($diff), $sessionId, "استرجاع لتعديل مشتريات جلسة #{$sessionId}");
            }
          }
          $purchase->update(['quantity' => $newQty]);
        }
      }

      // 🟥 حذف المنتجات اللي اتشالت واسترجاع كمياتها بالكامل للمخزن
      if (!empty($removed)) {
        $purchasesToDelete = SessionPurchase::with('product.recipe.ingredient')
          ->whereIn('id', $removed)
          ->where('sation_id', $sessionId)
          ->get();

        foreach ($purchasesToDelete as $p) {
          if ($p->product && $p->quantity > 0) {
            $this->restoreStock($p->product, $p->quantity, $sessionId, "استرجاع لحذف مشتريات من جلسة #{$sessionId}");
          }
          $p->delete();
        }
      }

      DB::commit();
      return response()->json(['status' => 'success', 'message' => 'تم تحديث المشتريات والمخزون بنجاح']);
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error('updatePurchases error: ' . $e->getMessage());
      return response()->json(['status' => 'error', 'message' => 'حدث خطأ أثناء تعديل المشتريات: ' . $e->getMessage()], 500);
    }
  }

  public function create(Request $request, Sation $session)
  {
    if ($session->end_time !== null || $session->status === 'closed') {
      return redirect()->back()->with('error', 'هذه الجلسه منتهيه لا يمكن اضافة مشتريات ');
    }
    return view("purchase.session.create", compact("session"));
  }

  /**
   * إضافة مشتريات للجلسة مع الخصم المباشر من المخزن
   */
  public function storeItem(Request $request, Sation $session)
  {
    // لو البيانات مبعوتة كـ JSON String من فورم عادي
    if (is_string($request->input('items'))) {
      $decoded = json_decode($request->input('items'), true);
      if (is_array($decoded)) {
        $request->merge(['items' => $decoded]);
      }
    }

    $validated = $request->validate([
      'items' => 'required|array|min:1',
      'items.*.id' => 'required|exists:products,id',
      'items.*.qty' => 'required|integer|min:1',
    ]);

    $itemsData = $validated['items'];
    $user = Auth::user();
    $openShift = Shift::where('user_id', $user->id)
      ->whereNull('end_time')
      ->first();

    if (!$session) {
      if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
        return response()->json(['status' => 'error', 'message' => 'الجلسة غير موجودة.'], 404);
      }
      return redirect()->back()->with('error', 'الجلسة غير موجودة.');
    }

    $isEnded = (!is_null($session->end_time) || (isset($session->status) && $session->status !== 'active'));
    if ($isEnded) {
      $msg = '⚠️ لا يمكن إضافة مشتريات: هذه الجلسة مُنتهية بالفعل.';
      if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
        return response()->json(['status' => 'error', 'message' => $msg], 422);
      }
      return redirect()->route('session.show', $session->id)->with('error', $msg);
    }

    DB::beginTransaction();
    try {
      $itemsResponse = [];
      foreach ($itemsData as $item) {
        if (!isset($item['id']) || !isset($item['qty']))
          continue;

        $product = Product::with('recipe.ingredient')->find($item['id']);
        if (!$product) {
          continue;
        }

        $qty = (int) $item['qty'];
        if ($qty <= 0)
          continue;

        $itemTotal = $product->price * $qty;

        // 🟢 1. الخصم الفوري من المخزن وتسجيل حركة الخامات أو المنتج
        $this->deductStock($product, $qty, $session->id, "استهلاك طلب جلسة #{$session->id}");

        $itemsResponse[] = [
          'id' => $product->id,
          'name' => $product->name,
          'price' => $product->price,
          'qty' => $qty,
          'total' => $itemTotal,
        ];

        // 🟢 2. تسجيل / زيادة المشتريات على الجلسة
        $purchase = SessionPurchase::where('sation_id', $session->id)
          ->where('product_id', $product->id)
          ->first();

        $actionType = SystemActionType::ADD_SESSION_PUNISHMENT->value;

        if ($purchase) {
          $purchase->quantity += $qty;
          $purchase->save();

          SystemAction::create([
            'user_id' => $user->id,
            'action' => $actionType,
            'actionable_type' => Sation::class,
            'actionable_id' => $session->id,
            'note' => "اضافة مشتريات للجلسه: {$product->name} ({$product->id})",
            'meta' => json_encode([
              'product_id' => $product->id,
              'qty_added' => $qty,
              'new_quantity_in_session' => $purchase->quantity,
              'price' => $product->price,
              'item_total' => $itemTotal,
            ]),
            'shift_id' => $openShift?->id,
            'ip' => request()->ip(),
            'source' => 'web',
          ]);
        } else {
          SessionPurchase::create([
            'sation_id' => $session->id,
            'product_id' => $product->id,
            'quantity' => $qty,
          ]);

          SystemAction::create([
            'user_id' => Auth::id(),
            'action' => $actionType,
            'actionable_type' => Sation::class,
            'actionable_id' => $session->id,
            'note' => "إضافة منتج جديد للجلسة: {$product->name} ({$product->id})",
            'meta' => json_encode([
              'product_id' => $product->id,
              'qty_added' => $qty,
              'price' => $product->price,
              'item_total' => $itemTotal,
            ]),
            'shift_id' => $openShift?->id,
            'ip' => request()->ip(),
            'source' => 'web',
          ]);
        }
      }

      DB::commit();

      $msg = '✅ تم إضافة المشروبات والخصم من المخزن بنجاح.';
      if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
        return response()->json([
          'status' => 'success',
          'message' => $msg,
          'items' => $itemsResponse
        ], 201);
      }

      return redirect()->route('session.show', $session->id)->with('success', $msg);
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error('storeItem error: ' . $e->getMessage());

      $msg = 'حدث خطأ أثناء إضافة المنتجات: ' . $e->getMessage();
      if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
        return response()->json(['status' => 'error', 'message' => $msg], 500);
      }

      return redirect()->back()->with('error', $msg);
    }
  }

  /**
   * حذف عنصر من مشتريات الجلسة مع استرجاع المخزن
   */
  public function destroy(Sation $session, SessionPurchase $purchase)
  {
    if ($purchase->sation_id != $session->id) {
      return redirect()->back()->with('error', 'العنصر غير مرتبط بهذه الجلسة.');
    }

    DB::beginTransaction();
    try {
      if ($purchase->product && $purchase->quantity > 0) {
        $this->restoreStock($purchase->product, $purchase->quantity, $session->id, "استرجاع لحذف عنصر من جلسة #{$session->id}");
      }
      $purchase->delete();
      DB::commit();

      return redirect()->back()->with('success', 'تم حذف العنصر واسترجاع الكمية للمخزن بنجاح.');
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error('destroy purchase error: ' . $e->getMessage());
      return redirect()->back()->with('error', 'حدث خطأ أثناء الحذف.');
    }
  }
}
