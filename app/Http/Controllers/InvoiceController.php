<?php

// app/Http/Controllers/InvoiceController.php
namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Client;
use App\Models\IngredientStock;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Shift;
use App\Models\ShiftAction;
use App\Services\ShiftService;
use App\Support\InvoiceNumber;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SystemAction;
use App\Services\IngredientStockService;
use Illuminate\Support\Facades\Log;
use Schema;

class InvoiceController extends Controller
{

  public function index()
  {
    $invoices = Invoice::with('items')->get();
    return view('invoices.index', compact('invoices'));
  }

  public function show(Invoice $invoice)
  {
    // صلاحيات: عرض الفاتورة للأدمن أو صاحب الفاتورة أو حسب policy


    // eager load relations: items, customer/user, any linked models
    $invoice->load(['items', 'user']); // تأكد أن relations موجودة

    // حسابات ملخص
    $total = $invoice->items->sum('total');
    $cost = $invoice->items->sum('cost') * 1; // مجموع تكلفة الوحدات (cost * qty إذا مخزن غير مضروب)
    // إذا لديك cost مخزن لكل item كمجموع، عدّل حسب الحاجة
    $profit = $total - $cost;

    return view('invoices.show', compact('invoice', 'total', 'cost', 'profit'));
  }
  // public function admin_show(Invoice $invoice)
  //   {
  //   $invoice->load(['items', 'user']); // تأكد أن relations موجودة

  //     // حسابات ملخص
  //     $total = $invoice->items->sum('total');
  //     $cost = $invoice->items->sum('cost') * 1; // مجموع تكلفة الوحدات (cost * qty إذا مخزن غير مضروب)
  //     // إذا لديك cost مخزن لكل item كمجموع، عدّل حسب الحاجة
  //     $profit = $total - $cost;

  //     return view('invoices.admin-show', compact('invoice', 'total', 'cost', 'profit'));

  //   }


  public function client_show(Invoice $invoice)
  {
    // تحميل العلاقات المطلوبة
    $invoice->load([
      'client',
      'items',
      'booking' => function ($query) {
        $query->with([
          'hall', // تحميل قاعة الحجز أو أي علاقة مرتبطة
        ]);
      },
    ]);

    // ترتيب البنود حسب النوع
    $groupedItems = [
      'product' => $invoice->items->where('item_type', 'product'),
      'subscription' => $invoice->items->where('item_type', 'subscription'),
      'booking' => $invoice->items->where('item_type', 'booking'),
      'session' => $invoice->items->where('item_type', 'session'),
      'deposit' => $invoice->items->where('item_type', 'deposit'),
    ];

    // ✅ أي فاتورة فيها منتجات تظهر مشترياتها (تمت استعادتها)
    $purchaseItems = collect();
    if ($groupedItems['product']->isNotEmpty()) {
      $purchaseItems = $groupedItems['product'];
    }

    // حساب الإجمالي الكلي
    $totalAmount = $invoice->items->sum('total');

    // نوع الفاتورة
    $invoiceType = $invoice->type;

    // بيانات إضافية لو نوعها deposit
    $extraData = [];
    if ($invoiceType === 'deposit') {
      $extraData = [
        'client_name' => $invoice->client->name ?? 'غير معروف',
        'booking_date' => optional($invoice->booking)->date ?? '-',
      ];
    }

    // التحقق من وجود مشتريات داخل فاتورة جلسة أو حجز
    $isHasPurchase = false;
    if (
      in_array($invoiceType, ['booking', 'session']) &&
      $invoice->items->where('item_type', 'product')->isNotEmpty()
    ) {
      $isHasPurchase = true;
    }

    // بيانات الحجز لو نوع الفاتورة booking
    $bookingData = null;
    $hourlyRate = null;
    $actualDurationMinutes = null;
    if ($invoiceType === 'booking' && $invoice->booking) {

      $bookingData = $invoice->booking;

      // التأكد إن القيم موجودة قبل الحساب
      if (!empty($bookingData->duration_minutes) && !empty($bookingData->estimated_total)) {
        $hours = $bookingData->duration_minutes / 60;
        if ($hours > 0) {
          $hourlyRate = $bookingData->estimated_total / $hours;
        }
      }
      // حساب المدة الفعلية بالدقائق من real_start_at و real_end_at
      if (!empty($bookingData->real_start_at) && !empty($bookingData->real_end_at)) {
        $start = Carbon::parse($bookingData->real_start_at);
        $end = Carbon::parse($bookingData->real_end_at);

        $secondsDiff = abs($end->diffInSeconds($start, false));
        $actualDurationMinutes = (int) round($secondsDiff / 60);
      }
    }

    // بيانات الجلسة المأخوذة من invoice_item -> session
    $sessionData = null;
    if ($invoiceType === 'session') {
      // نجيب أول item من نوع session
      $sessionItem = $invoice->items->firstWhere('item_type', 'session');
      if ($sessionItem && $sessionItem->session) {
        $sessionData = $sessionItem->session;

        // نحسب مدة الجلسة الفعلية لو فيها start/end
        if (!empty($sessionData->start_time) && !empty($sessionData->end_time)) {
          $start = Carbon::parse($sessionData->start_time);
          $end = Carbon::parse($sessionData->end_time);
          $sessionData->actual_duration_minutes = $end->diffInMinutes($start);
        }
      }
    }

    // تمرير كل البيانات إلى الواجهة
    return view('invoices.client_show', [
      'invoice' => $invoice,
      'groupedItems' => $groupedItems,
      'totalAmount' => $totalAmount,
      'invoiceType' => $invoiceType,
      'extraData' => $extraData,
      'isHasPurchase' => $isHasPurchase,
      'bookingData' => $bookingData,
      'hourlyRate' => $hourlyRate,
      'actualDurationMinutes' => $actualDurationMinutes,
      'sessionData' => $sessionData,
      'purchaseItems' => $purchaseItems, // ← تمت إعادتها للتمرير إلى الواجهة
    ]);
  }



  public function ajaxSearch(Request $request)
  {
    $query = Invoice::query()->with('client')->where('total', '>', 0);

    if ($q = $request->query('q')) {
      $query->whereHas('client', fn($q2) => $q2->where('name', 'like', "%{$q}%"))
        ->orWhere('invoice_number', 'like', "%{$q}%");
    }

    if ($types = $request->query('types')) {
      $types = explode(',', $types);
      $query->whereIn('type', $types);
    }

    if ($from = $request->query('from')) {
      $query->whereDate('updated_at', '>=', $from);
    }
    if ($to = $request->query('to')) {
      $query->whereDate('updated_at', '<=', $to);
    }

    return $query->orderByDesc('updated_at')->limit(50)->get()->map(function ($inv) {

      return [

        'id' => $inv->id,
        'invoice_number' => $inv->invoice_number,
        'client_name' => $inv->client->name ?? null,
        'type' => $inv->type,
        'total' => $inv->total,
        'created_at' => $inv->created_at,
        'updated_at' => $inv->updated_at,
      ];
    });
  }


  // لو عايز المعاينة بدون حفظ (يعتمد على نفس فورم الداتا)
  public function preview(StoreInvoiceRequest $request)
  {


    [$items, $totals] = $this->buildItemsFromRequest($request->validated()['items']);

    // نحدد نوع الفاتورة تلقائيًا
    $type = $this->determineInvoiceType($items);

    return view('sale_proccess.invoice', [
      'items' => collect($items)->map(fn($it) => [
        'qty' => $it['qty'],
        'name' => $it['name'],
        'total' => $it['total'],
        // لو محتاج تعرض price/cost في الـ Blade عدّل القالب
        'price' => $it['price'],
        'cost' => $it['cost'],
      ]),
      'type' => $type,
      'grandTotal' => $totals['total'],
    ]);
  }




  public function store(
    StoreInvoiceRequest $request,
    ShiftService $shiftService,
    IngredientStockService $ingredientStockService
  ) {
    $validated = $request->validated();
    $user = Auth::user();

    // الشيفت المفتوح للمستخدم الحالي
    $openShift = Shift::where('user_id', $user->id)
      ->whereNull('end_time')
      ->first();

    // غير الأدمن لازم يكون عنده شيفت
    if (!$openShift && !$user->hasRole('admin')) {
      session()->flash('shift_required', true);

      return redirect()
        ->back()
        ->with('error', '⚠️ لا يوجد شيفت مفتوح، ابدأ شيفت أولاً.');
    }

    [$items, $totals] = $this->buildItemsFromRequest($validated['items']);

    $type = $this->determineInvoiceType($items);

    try {

      $invoice = DB::transaction(function () use (
        $validated,
        $items,
        $totals,
        $type,
        $user,
        $openShift,
        $shiftService,
        $ingredientStockService
      ) {

        /*
            |--------------------------------------------------------------------------
            | 1. خصم المنتجات / الخامات من المخزن
            |--------------------------------------------------------------------------
            */

        foreach ($items as $it) {

          if (
            $it['item_type'] !== 'product' ||
            !isset($it['product_id'])
          ) {
            continue;
          }

          $product = Product::findOrFail($it['product_id']);

          if ($product->is_produced) {

            $ingredientStockService->deductProductIngredients(
              $product,
              $it['qty']
            );
          } else {

            if ($it['qty'] > 0) {
              $this->deductProductQuantity(
                $product,
                $it['qty']
              );
            }
          }
        }

        /*
            |--------------------------------------------------------------------------
            | 2. حساب الخصم
            |--------------------------------------------------------------------------
            */

        $subtotal = (float) $totals['total'];

        $discountType = $validated['discount_type'] ?? 'fixed';

        $discountValue = (float) (
          $validated['discount_value'] ?? 0
        );

        $discountReason = $validated['discount_reason'] ?? null;

        $discountAmount = 0;

        /*
            |--------------------------------------------------------------------------
            | حماية صلاحيات الخصم
            |--------------------------------------------------------------------------
            */

        if ($discountValue > 0) {

          if (
            !$user->hasRole('admin') &&
            !$user->hasRole('supervisor')
          ) {
            throw new \Exception(
              '⚠️ غير مصرح لك بتطبيق خصومات على الفاتورة.'
            );
          }

          /*
                | خصم بالنسبة المئوية
                */
          if ($discountType === 'percentage') {

            if ($discountValue > 100) {
              throw new \Exception(
                '⚠️ لا يمكن أن تتعدى نسبة الخصم 100%'
              );
            }

            $discountAmount =
              ($subtotal * $discountValue) / 100;
          }

          /*
                | خصم مبلغ ثابت
                */ elseif ($discountType === 'fixed') {

            if ($discountValue > $subtotal) {
              throw new \Exception(
                '⚠️ لا يمكن أن يتعدى مبلغ الخصم القيمة الإجمالية للفاتورة'
              );
            }

            $discountAmount = $discountValue;
          }
        }

        /*
            |--------------------------------------------------------------------------
            | 3. الإجمالي النهائي بعد الخصم
            |--------------------------------------------------------------------------
            */

        $finalTotal = max(
          0,
          $subtotal - $discountAmount
        );

        /*
            | الربح المتوقع بعد الخصم
            */
        $finalProfit = max(
          0,
          (float) $totals['profit'] - $discountAmount
        );

        /*
            |--------------------------------------------------------------------------
            | 4. إنشاء الفاتورة
            |--------------------------------------------------------------------------
            */

        $invoice = Invoice::create([

          'invoice_number' => InvoiceNumber::next(),

          'client_id' => $validated['client_id'] ?? null,

          'type' => $type,

          'subtotal' => $subtotal,

          'discount_type' => $discountAmount > 0
            ? $discountType
            : null,

          'discount_value' => $discountAmount > 0
            ? $discountValue
            : 0,

          'discount_amount' => $discountAmount,

          'discount_reason' => $discountAmount > 0
            ? $discountReason
            : null,

          'discount_by' => $discountAmount > 0
            ? $user->id
            : null,

          'total' => $finalTotal,

          'profit' => $finalProfit,

          'notes' => $validated['notes'] ?? null,
        ]);

        /*
            |--------------------------------------------------------------------------
            | 5. تسجيل بنود الفاتورة
            |--------------------------------------------------------------------------
            */

        foreach ($items as $it) {

          InvoiceItem::create([

            'invoice_id' => $invoice->id,

            'item_type' => $it['item_type'],

            'product_id' => $it['product_id'] ?? null,

            'subscription_id' =>
            $it['subscription_id'] ?? null,

            'booking_id' =>
            $it['booking_id'] ?? null,

            'session_id' =>
            $it['session_id'] ?? null,

            'name' => $it['name'],

            'qty' => $it['qty'],

            'price' => $it['price'],

            'cost' => $it['cost'],

            'total' => $it['total'],

            'description' =>
            $it['description'] ?? null,
          ]);
        }

        /*
            |--------------------------------------------------------------------------
            | 6. تسجيل System Action
            |--------------------------------------------------------------------------
            */

        SystemAction::create([

          'user_id' => $user->id,

          'action' =>
          SystemActionType::SALE_PROCESS->value,

          'actionable_type' => Invoice::class,

          'actionable_id' => $invoice->id,

          'invoice_id' => $invoice->id,

          'shift_id' => $openShift?->id,

          /*
                | مهم:
                | هنا نسجل المبلغ النهائي بعد الخصم
                */
          'amount' => $invoice->total,

          'note' =>
          "إنشاء فاتورة بيع منفصلة"
            . ($discountAmount > 0 ? " مع خصم" : "")
            . " - #{$invoice->invoice_number}",

          'meta' => json_encode([

            'client_id' => $invoice->client_id,

            'items' => $items,

            'totals' => [

              'subtotal' => $subtotal,

              'discount' => $discountAmount,

              'total' => $finalTotal,

              'profit' => $finalProfit,
            ],

            'discount' => [

              'type' => $discountAmount > 0
                ? $discountType
                : null,

              'value' => $discountAmount > 0
                ? $discountValue
                : 0,

              'amount' => $discountAmount,

              'reason' => $discountReason,
            ],

            'type' => $invoice->type,
          ]),

          'ip' => request()->ip(),

          'source' => 'web',
        ]);

        /*
            |--------------------------------------------------------------------------
            | 7. تسجيل العملية في الشيفت
            |--------------------------------------------------------------------------
            |
            | هنا النقطة المهمة:
            |
            | - لو فيه شيفت → سجل العملية
            | - لو المستخدم Admin ومفيش شيفت → لا تسجل ShiftAction
            |
            | والمبلغ المسجل في الشيفت هو invoice->total
            | أي بعد الخصم.
            |
            */

        if ($openShift) {

          $shiftService->logAction(
            actionType: 'separate_sale',
            invoiceId: $invoice->id,
            amount: (float) $invoice->total,
            paymentType: $validated['payment_type'] ?? 'cash',
            expenseAmount: null,
            notes: "عملية بيع منتجات"
              . ($discountAmount > 0 ? " بعد الخصم" : "")
              . " (فاتورة رقم {$invoice->invoice_number})"
          );
        }

        return $invoice->load('items');
      });
    } catch (\Throwable $e) {

      Log::error('Invoice Creation Failed', [

        'request_items' => $request->items ?? null,

        'user_id' => Auth::id(),

        'message' => $e->getMessage(),

        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'status' => 'error',
        'message' => $e->getMessage(),
      ], 500);
    }

    /*
    |--------------------------------------------------------------------------
    | 8. Response
    |--------------------------------------------------------------------------
    */

    return response()->json([
      'status' => 'success',
      'message' => 'Invoice created successfully.',
      'invoice' => $invoice,
    ]);
  }

  protected function deductProductQuantity(Product $product, $qty)
  {
    // 🔥 لو المنتج مُصنَّع
    if ($product->is_produced) {

      $missingIngredients = [];

      // تأكد من تحميل العلاقة
      $product->loadMissing('ingredients');

      foreach ($product->ingredients as $ingredient) {

        // الكمية المطلوبة = amount في pivot × الكمية المطلوبة من المنتج
        $requiredAmount = $ingredient->pivot->amount * $qty;

        // المخزون الحالي في جدول ingredients هو stock
        if ($ingredient->stock < $requiredAmount) {
          $missingIngredients[] = $ingredient->name;
        }
      }

      // لو فيه خامات ناقصة
      if (!empty($missingIngredients)) {

        $list = implode('، ', $missingIngredients);

        throw new \Exception(
          "⚠️ المنتج \"{$product->name}\" يحتاج الخامات التالية: {$list}"
        );
      }

      // ✅ خصم الخامات لو متوفرة
      foreach ($product->ingredients as $ingredient) {

        $requiredAmount = $ingredient->pivot->amount * $qty;

        $ingredient->decrement('stock', $requiredAmount);
      }

      return;
    }

    // 🔹 لو منتج عادي
    if ($product->quantity < $qty) {

      $available = $product->quantity;
      $requested = $qty;
      $name = $product->name;

      if ($available <= 0) {
        throw new \Exception("❌ المنتج \"{$name}\" غير متوفر حالياً في المخزون.");
      }

      throw new \Exception(
        "❌ المنتج \"{$name}\" المطلوب {$requested} – المتاح فقط {$available}."
      );
    }

    $product->decrement('quantity', $qty);
  }



  private function buildItemsFromRequest(array $requestItems): array
  {
    $items = [];
    $grandTotal = 0.0;
    $grandProfit = 0.0;

    foreach ($requestItems as $in) {
      $type = $in['item_type'];

      if ($type === 'product') {
        $product = Product::findOrFail($in['product_id']);
        $qty = (int) ($in['qty'] ?? 1);
        $price = (float) $product->price; // ممكن تسمح override من $in['price'] لو عايز
        $cost = (float) $product->cost;

        $total = $price * $qty;
        $profit = ($price - $cost) * $qty;

        $items[] = [
          'item_type' => 'product',
          'product_id' => $product->id,
          'name' => $product->name,
          'qty' => $qty,
          'price' => $price,
          'cost' => $cost,
          'total' => $total,
        ];
      } elseif ($type === 'subscription') {
        // TODO: هات الاشتراك الحقيقي من جدول subscriptions
        // مؤقتًا نفترض جالك price مع الطلب
        $qty = (int) ($in['qty'] ?? 1);
        $price = (float) ($in['price'] ?? 0);
        $cost = (float) ($in['cost'] ?? 0);

        $items[] = [
          'item_type' => 'subscription',
          'subscription_id' => $in['subscription_id'] ?? null,
          'name' => $in['name'] ?? 'اشتراك',
          'qty' => $qty,
          'price' => $price,
          'cost' => $cost,
          'total' => $price * $qty,
        ];
      } elseif ($type === 'booking') {
        // TODO: هات بيانات الحجز من جدول bookings لو موجود
        $qty = (int) ($in['qty'] ?? 1); // عدد الساعات مثلاً
        $price = (float) ($in['price'] ?? 0);
        $cost = (float) ($in['cost'] ?? 0);

        $items[] = [
          'item_type' => 'booking',
          'booking_id' => $in['booking_id'] ?? null,
          'name' => $in['name'] ?? 'حجز قاعة',
          'qty' => $qty,
          'price' => $price,
          'cost' => $cost,
          'total' => $price * $qty,
        ];
      } elseif ($type === 'session') {
        // TODO: هات بيانات الجلسة من جدول sessions لو موجود (سعر الساعة)
        $qty = (int) ($in['qty'] ?? 1); // عدد الساعات
        $price = (float) ($in['price'] ?? 0);
        $cost = (float) ($in['cost'] ?? 0);

        $items[] = [
          'item_type' => 'session',
          'session_id' => $in['session_id'] ?? null,
          'name' => $in['name'] ?? 'جلسة',
          'qty' => $qty,
          'price' => $price,
          'cost' => $cost,
          'total' => $price * $qty,
        ];
      } elseif ($type === 'deposit') {
        // دفعة مقدمة (ممكن تكون لفاتورة مستقبلية أو لحجز)
        $amount = (float) $in['amount'];
        $desc = $in['description'] ?? null;

        $items[] = [
          'item_type' => 'deposit',
          'name' => 'دفعة مقدمة',
          'qty' => 1,
          'price' => $amount,
          'cost' => 0,
          'total' => $amount,
          'description' => $desc,
        ];
      }

      $grandTotal += end($items)['total'];
      $grandProfit += (end($items)['price'] - end($items)['cost']) * end($items)['qty'];
    }

    return [$items, ['total' => round($grandTotal, 2), 'profit' => round($grandProfit, 2)]];
  }

  private function determineInvoiceType(array $items): string
  {
    $types = collect($items)->pluck('item_type')->unique()->values()->all();
    return count($types) === 1 ? $types[0] : 'mixed';
  }




  public function print(Request $request)
  {
    $items = collect($request->items)->map(fn($item) => [
      'qty'   => $item['qty'],
      'name'  => $item['name'],
      'total' => $item['qty'] * $item['price'],
    ]);

    // استقبال الملاحظة إن وجدت
    $note = $request->input('note');

    return view('sale_proccess.print', compact('items', 'note'));
  }


  public function clientInvoices(Request $request, $clientId)
  {
    $client = Client::findOrFail($clientId);
    $invoice = Invoice::where('client_id', $client->id);
    $invoiceCount = $invoice->count();
    $invoiceTotal = $invoice->sum('total');
    $invoices = $invoice->select('id', 'type', 'invoice_number', 'total')->get();
    return view('clients.invoices', compact('invoiceCount', 'invoiceTotal', 'invoices', 'client'));
  }




  public function edit($id)
  {
    $invoice = Invoice::with('items.product')->findOrFail($id);
    // جلب المنتجات لزوم قائمة البحث الأولي أو الـ Autocomplete
    $products = Product::all();
    return view('invoices.edit', compact('invoice', 'products'));
  }
  public function update(Request $request, $id)
  {
    $returnStock = $request->boolean('return_stock', true);

    DB::beginTransaction();
    try {
      $invoice = Invoice::findOrFail($id);
      $oldTotal = $invoice->total;

      $oldItems = $invoice->items()->where('item_type', 'product')->get()->keyBy('product_id');
      $items = $request->items ?? [];
      $newProductIds = collect($items)->pluck('product_id')->map(fn($id) => intval($id));

      $newSubtotal = 0;
      $newProfit = 0;

      foreach ($items as $itemData) {
        $product = Product::with('recipe.ingredient')->find($itemData['product_id']);
        if (!$product) continue;

        $oldQty = $oldItems->has($product->id) ? $oldItems[$product->id]->qty : 0;
        $newQty = intval($itemData['qty']);
        $qtyDiff = $newQty - $oldQty;

        // تعديل المخزون حسب الريسبي
        if ($returnStock && $qtyDiff !== 0) {
          foreach ($product->recipe as $recipe) {
            $ingredient = $recipe->ingredient;
            $amountChange = $qtyDiff * $recipe->amount;

            if ($amountChange === 0) continue;

            $type = $amountChange > 0 ? 'out' : 'in';
            $ingredient->stock += $type === 'in' ? abs($amountChange) : -$amountChange;
            $ingredient->save();

            IngredientStock::create([
              'ingredient_id' => $ingredient->id,
              'amount' => abs($amountChange),
              'unit_id' => $recipe->unit_id,
              'type' => $type,
              'before_amount' => $ingredient->stock + ($type === 'out' ? $amountChange : -$amountChange),
              'after_amount' => $ingredient->stock,
              'note' => 'تعديل الفاتورة رقم ' . $invoice->invoice_number
            ]);
          }
        }

        $totalPrice = $product->price * $newQty;
        $costTotal  = ($product->cost ?? 0) * $newQty;

        if ($oldItems->has($product->id)) {
          $oldItems[$product->id]->update([
            'qty' => $newQty,
            'price' => $product->price,
            'total' => $totalPrice,
            'cost' => $product->cost ?? 0,
          ]);
        } else {
          $invoice->items()->create([
            'item_type' => 'product',
            'product_id' => $product->id,
            'name' => $product->name,
            'qty' => $newQty,
            'price' => $product->price,
            'total' => $totalPrice,
            'cost' => $product->cost ?? 0,
          ]);
        }

        $newSubtotal += $totalPrice;
        $newProfit   += ($totalPrice - $costTotal);
      }

      // التعامل مع المنتجات المحذوفة
      if ($returnStock) {
        foreach ($oldItems as $oldItem) {
          if (!$newProductIds->contains($oldItem->product_id)) {
            $product = Product::with('recipe.ingredient')->find($oldItem->product_id);
            foreach ($product->recipe as $recipe) {
              $ingredient = $recipe->ingredient;
              $ingredient->stock += $oldItem->qty * $recipe->amount;
              $ingredient->save();

              IngredientStock::create([
                'ingredient_id' => $ingredient->id,
                'amount' => $oldItem->qty * $recipe->amount,
                'unit_id' => $recipe->unit_id,
                'type' => 'in',
                'before_amount' => $ingredient->stock - ($oldItem->qty * $recipe->amount),
                'after_amount' => $ingredient->stock,
                'note' => 'منتج تم حذفه من الفاتورة رقم ' . $invoice->invoice_number
              ]);
            }
            $oldItem->delete();
          }
        }
      }

      // 🔹 إعادة احتساب الخصم بناءً على القيم الجديدة للفاتورة
      $otherItemsTotal = $invoice->items()->where('item_type', '!=', 'product')->sum('total');
      $finalSubtotal = $newSubtotal + $otherItemsTotal;

      // قراءة الخصم الجديد أو الاعتماد على الخصم السابق للفاتورة
      $discountType = $request->has('discount_type') ? $request->discount_type : $invoice->discount_type;
      $discountValue = $request->has('discount_value') ? floatval($request->discount_value) : floatval($invoice->discount_value);
      $discountAmount = 0;

      if ($discountType === 'percentage') {
        $discountAmount = ($finalSubtotal * $discountValue) / 100;
      } elseif ($discountType === 'fixed') {
        $discountAmount = $discountValue;
      }

      $finalTotal = max(0, $finalSubtotal - $discountAmount);
      $finalProfit = max(0, $newProfit - $discountAmount);

      // تحديث الفاتورة بالبيانات المالية الجديدة بعد الخصم
      $invoice->update([
        'subtotal' => $finalSubtotal,
        'discount_type' => $discountType,
        'discount_value' => $discountValue,
        'discount_amount' => $discountAmount,
        'discount_reason' => $request->has('discount_reason') ? $request->discount_reason : $invoice->discount_reason,
        'total' => $finalTotal,
        'profit' => $finalProfit
      ]);

      // تسجيل فرق السعر في الشيفت بناءً على الصافي الفعلي المستلم
      $difference = $finalTotal - $oldTotal;
      if ($difference != 0) {
        $activeShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
        if ($activeShift) {
          $p_type = $request->payment_type ?: $invoice->payment_type ?? 'cash';

          ShiftAction::create([
            'shift_id' => $activeShift->id,
            'action_type' => 'separate_sale',
            'invoice_id' => $invoice->id,
            'amount' => $difference > 0 ? $difference : 0,
            'expense_amount' => $difference < 0 ? abs($difference) : 0,
            'payment_type' => $p_type,
            'notes' => 'تعديل مالي (مرتجع/إضافة بخصم) للفاتورة رقم: ' . $invoice->invoice_number
          ]);
        }
      }

      DB::commit();
      return response()->json(['status' => 'success', 'message' => 'تم تحديث الفاتورة واحتساب الخصم بنجاح']);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
  }
}
