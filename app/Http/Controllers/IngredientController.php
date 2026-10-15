<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\IngredientStock;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\Unit;
use App\Services\SupervisorActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IngredientController extends Controller
{
    public function index()
    {
        $ingredients = Ingredient::with([
            'unit',
            'stocks' => fn($q) => $q->latest()->limit(1),
            'recipes.product',
            'recipes.unit'
        ])->get();

        $units = Unit::all();
        $products = Product::all();

        return view(
            "inventories.ingredients.index",
            compact('ingredients', 'units', 'products')
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string',
            'stock'       => 'required|numeric|min:0',
            'unit_id'     => 'required|exists:units,id',
            'alert_stock' => 'required|numeric|min:0', // ← تم إضافته
        ]);

        $ingredient = Ingredient::create($data);
        SupervisorActivityService::log(
            'create',
            'تم إضافة خامة جديدة: ' . $ingredient->name,
            $ingredient,
            null,
            $ingredient->toArray()
        );

        return redirect()->back()->with('success', 'تم حفظ الخامة بنجاح');
    }



    public function addStock(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note'   => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $id) {

            $ingredient = Ingredient::findOrFail($id);

            $before = $ingredient->stock;
            $addedAmount = $request->amount;
            $after = $before + $addedAmount;

            // 1️⃣ تحديث المخزون الأساسي
            $ingredient->update([
                'stock' => $after
            ]);

            // 2️⃣ تسجيل الحركة
            IngredientStock::create([
                'ingredient_id' => $ingredient->id,
                'amount'        => $addedAmount,
                'unit_id'       => $ingredient->unit_id,
                'type'          => 'in',
                'before_amount' => $before,
                'after_amount'  => $after,
                'note'          => $request->note,
            ]);
            SupervisorActivityService::log(
                'create',
                'تم إضافة كمية خامة جديدة: ' . $ingredient->name,
                $ingredient,
                null,
                $ingredient->toArray()
            );
        });

        return back()->with('success', 'تمت إضافة الكمية بنجاح ✅');
    }


    public function show(Ingredient $ingredient)
    {
        return $ingredient->load('unit');
    }

    public function update(Request $request, $id)
    {
        $ingredient = Ingredient::findOrFail($id);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'stock'       => 'required|numeric|min:0',
            'unit_id'     => 'nullable|exists:units,id',
            'alert_stock' => 'required|numeric|min:0',
        ]);

        // 1️⃣ الاحتفاظ بالقيم قبل التعديل للحسابات
        $beforeAmount = $ingredient->stock;
        $afterAmount  = $request->stock;
        $diff = $afterAmount - $beforeAmount;

        // 2️⃣ تحديث بيانات الخامة
        $ingredient->update($data);

        // 3️⃣ تسجيل حركة المخزون فقط إذا حدث تغيير في الكمية
        if ($diff != 0) {
            IngredientStock::create([
                'ingredient_id' => $ingredient->id,
                'amount'        => abs($diff), // القيمة المطلقة للفرق
                'unit_id'       => $ingredient->unit_id,
                'type'          => $diff > 0 ? 'in' : 'out', // لو الفرق موجب يبقى دخل، لو سالب يبقى خرج
                'before_amount' => $beforeAmount,
                'after_amount'  => $afterAmount,
                'note'          => $request->note ?? 'تعديل يدوي لبيانات الخامة',
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث بيانات ' . $ingredient->name . ' وسجل الحركة بنجاح ✅');
    }
    public function destroy(Ingredient $ingredient)
    {
        // حذف المخزون المرتبط
        $ingredient->stocks()->delete();

        // حذف الريسبي المرتبط
        $ingredient->recipes()->delete();

        $ingredient->delete();

        return response()->noContent();
    }
 public function history()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // 1. حساب الإحصائيات المطلوبة في أعلى الصفحة
        $stats = [
            // إجمالي عدد حركات الوارد في الشهر الحالي (أو يمكنك استبدال count() بـ sum('amount') لو كنت تريد إجمالي الكمية)
            'total_in' => IngredientStock::in()
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count(),

            // إجمالي عدد حركات المنصرف في الشهر الحالي
            'total_out' => IngredientStock::out()
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count(),

            // الخامات التي وصلت أو نزلت تحت الحد الأدنى للتنبيه
            'low_stock_count' => Ingredient::whereColumn('stock', '<=', 'alert_stock')->count(),

            // إجمالي عدد الوصفات أو المنتجات المرتبطة بالخامات
            'total_recipes' => ProductRecipe::count(),
        ];

        // 2. جلب كافة حركات المخزن مع جلب العلاقات المرتبطة بها (Eager Loading)
        // لضمان عمل البحث الذكي بكفاءة عالية وتجنب استعلامات قاعدة البيانات المتكررة
        $stocks = IngredientStock::with([
                'ingredient.recipes.product', // لجلب المنتجات المرتبطة بالخامة (مهم جداً للبحث الذكي في الـ View)
                'unit'                        // لجلب وحدة القياس
            ])
            ->latest()
            ->get();

        // 3. تجميع الحركات حسب التاريخ (Y-m-d) لتطابق التنسيق في الـ Blade
        $groupedStocks = $stocks->groupBy(function ($stock) {
            return $stock->created_at->format('Y-m-d');
        });

        // 4. تمرير البيانات إلى صفحة الـ Blade
        return view('inventories.ingredients.history', compact('stats', 'groupedStocks'));
    }
}
