<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ImportantProduct;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductRecipe;
use DB;
use Illuminate\Http\Request;

class ProductController extends Controller
{
  public function index()
  {
    $products = Product::with('recipe')->get();
    $countProducts = $products->count();
    $countItems = $products->sum('quantity');

    // 👇 نجيب الخامات بعلاقاتها
    $ingredients = Ingredient::with('unit')->get();
    $categories = Category::all();    // الأصناف (الأقسام)
    return view('products.index', compact(
      'products',
      'countProducts',
      'countItems',
      'ingredients',
      'categories'
    ));
  }



  public function create()
  {
    return view('products.create');
  }
  public function createImportant()
  {

    $importantProducts = ImportantProduct::with('product')->latest()->paginate(20);
    // $products not mandatory here (we use AJAX search), لكن لو بدك تمرر:
    // $products = \App\Models\Product::take(30)->get();

    return view('managment.changes.important_products.create', compact('importantProducts'));
  }
  public function storeImportant(Request $request)
  {
    $data = $request->validate([
      'name' => 'required|string|max:255',
      'product_id' => 'required|exists:products,id',
    ]);

    // التحقق إذا الاسم موجود بالفعل
    $nameExists = ImportantProduct::where('name', $data['name'])->exists();
    if ($nameExists) {
      return redirect()->back()->with('error', 'هذا الاسم مرتبط بمنتج مهم بالفعل.');
    }

    // التحقق إذا المنتج مرتبط بالفعل
    $productExists = ImportantProduct::where('product_id', $data['product_id'])->exists();
    if ($productExists) {
      return redirect()->back()->with('error', 'هذا المنتج موجود بالفعل كمنتج مهم.');
    }

    // إنشاء المنتج المهم
    ImportantProduct::create([
      'product_id' => $data['product_id'],
      'name' => $data['name'],
    ]);

    return redirect()->back()->with('success', 'تم حفظ المنتج المهم بنجاح.');
  }
  public function updateImportant(Request $request, ImportantProduct $importantProduct)
  {
    $data = $request->validate([
      'name' => 'required|string|max:255',
      'product_id' => 'nullable|exists:products,id',
    ]);

    // تحقق من الاسم المكرر (باستثناء نفسه)
    $existsByName = ImportantProduct::where('name', $data['name'])
      ->where('id', '!=', $importantProduct->id)
      ->exists();
    if ($existsByName) {
      return redirect()->back()->with('error', 'هذا الاسم مستخدم بالفعل.');
    }

    // تحقق من المنتج المرتبط (باستثناء نفسه)
    if (!empty($data['product_id'])) {
      $existsByProduct = ImportantProduct::where('product_id', $data['product_id'])
        ->where('id', '!=', $importantProduct->id)
        ->exists();
      if ($existsByProduct) {
        return redirect()->back()->with('error', 'هذا المنتج مرتبط بالفعل بمنتج مهم آخر.');
      }
    }

    // التحديث
    $importantProduct->update([
      'name' => $data['name'],
      'product_id' => $data['product_id'] ?? $importantProduct->product_id,
    ]);

    return redirect()->back()->with('success', 'تم تحديث المنتج المهم بنجاح.');
  }
  public function update(Request $request, Product $product)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'price' => 'required|numeric|min:0',
      'cost' => 'nullable|numeric|min:0',
      'quantity' => 'required|integer|min:0',
    ]);

    $product->update($validated);
    return back()->with('success', 'Product updated successfully');
  }

  public function show(Product $product)
  {
    $product->load('recipe.ingredient.unit');

    $importantProduct = ImportantProduct::where('product_id', $product->id)->first();
    // 👇 نجيب الخامات بعلاقاتها
    $ingredients = Ingredient::with('unit')->get();

    return view('products.show', compact('product', 'importantProduct', 'ingredients'));
  }


  public function addQuantityPage()
  {
    return view('products.add-quantity');
  }
  public function store(Request $request)
  {
    // 1️⃣ Validation
    $request->validate([
      'product_id'   => 'required|integer',
      'name'         => 'required|string|max:255',
      'price'        => 'required|numeric|min:0',
      'cost'         => 'required|numeric|min:0',
      'is_produced'  => 'nullable|boolean',
      'min_quantity' => 'required|integer|min:0',
      // فاليوميديشن مشروط: لو مصنع، الخامات مطلوبة
      'ingredients'  => 'required_if:is_produced,1|array',
      'amounts'      => 'required_if:is_produced,1|array',
      'category_id' => 'required|exists:categories,id',
    ]);

    // 2️⃣ التحقق من التكرار
    if (Product::where('id', $request->product_id)->exists()) {
      return redirect()->back()->with('error', "عذراً، هذا الـ ID مستخدم مسبقاً ❕");
    }

    try {
      \DB::transaction(function () use ($request) {
        // 3️⃣ إدخال المنتج
        $product = Product::create([
          'id'           => $request->product_id,
          'name'         => $request->name,
          'price'        => $request->price,
          'cost'         => $request->cost,
          'category_id' => $request->category_id,
          'is_produced'  => $request->has('is_produced') ? 1 : 0,
          'quantity'     => $request->has('is_produced') ? 0 : ($request->quantity ?? 0),
          'min_quantity' => $request->min_quantity,
        ]);

        // 4️⃣ إذا كان منتج مُصنع، سجل "الخلطة" في جدول الريسبي
        if ($request->has('is_produced') && $request->ingredients) {
          foreach ($request->ingredients as $index => $ingredientId) {
            if (!empty($ingredientId) && !empty($request->amounts[$index])) {
              // جلب وحدة الخامة تلقائياً من جدول الخامات
              $ingredient = Ingredient::find($ingredientId);

              ProductRecipe::create([
                'product_id'    => $product->id,
                'ingredient_id' => $ingredientId,
                'amount'        => $request->amounts[$index],
                'unit_id'       => $ingredient->unit_id, // نأخذ الوحدة الافتراضية للخامة
              ]);
            }
          }
        }
      });

      return redirect()->back()->with('success', 'تمت إضافة المنتج ومكوناته بنجاح ✅');
    } catch (\Exception $e) {
      return redirect()->back()->with('error', 'حدث خطأ أثناء الحفظ: ' . $e->getMessage());
    }
  }

  public function addQuantity(Request $request, $id)
  {
    $request->validate([
      'quantity' => 'required|integer|min:1',
    ]);

    $product = Product::findOrFail($id);
    $product->quantity += $request->quantity;
    $product->save();

    return redirect()->route('products.index')->with('success', 'تمت إضافة الكمية بنجاح ✅');
  }
 public function search(Request $request)
{
    $query = $request->get('query');

    if (!$query) {
        return response()->json([]);
    }

    $results = Product::with('ingredients')
        ->where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('id', 'LIKE', "%{$query}%")
              
              // 🔥 البحث في الخامات
              ->orWhereHas('ingredients', function ($q2) use ($query) {
                  $q2->where('name', 'LIKE', "%{$query}%");
              });
        })
        ->limit(15)
        ->get(['id', 'name', 'price', 'quantity']);

    return response()->json($results);
}
  public function searchId(Request $request)
  {
    $query = $request->get('query');

    $results = Product::where('name', 'LIKE', "{$query}%") // يبدأ بالحروف المكتوبة
      ->orWhere('id', $query) // إذا كتبت id
      ->select('id', 'name', 'price', 'cost', 'quantity')
      ->limit(10)
      ->get();

    return response()->json($results);
  }
  public function posIndex()
  {
    // جلب الأصناف مع منتجاتها
    $categories = Category::with('products')->get();
    return view('sales.pos', compact('categories'));
  }
  public function bulkDelete(Request $request)
  {
    $ids = $request->ids;

    if (!$ids || !is_array($ids)) {
      return response()->json(['status' => 'error']);
    }

    Product::whereIn('id', $ids)->delete(); // Soft Delete

    return response()->json([
      'status' => 'success',
      'message' => 'تم حذف المنتجات بنجاح'
    ]);
  }
}
