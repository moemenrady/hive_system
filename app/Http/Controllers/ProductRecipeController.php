<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductRecipe;
use Illuminate\Http\Request;

class ProductRecipeController extends Controller
{
    public function index()
    {

        $recipes = ProductRecipe::with(['product', 'ingredient', 'unit'])->get();
        return view("product_recipes.index", compact('recipes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'    => 'required|exists:products,id',
            'ingredient_id' => 'required|exists:ingredients,id',
            'amount'        => 'required|numeric|min:0.01',
            'unit_id'       => 'required|exists:units,id',
        ]);

        return ProductRecipe::create($data);
    }

    public function show(ProductRecipe $productRecipe)
    {
        return $productRecipe->load(['product', 'ingredient', 'unit']);
    }

    public function update(Request $request, ProductRecipe $productRecipe)
    {
        $data = $request->validate([
            'product_id'    => 'required|exists:products,id',
            'ingredient_id' => 'required|exists:ingredients,id',
            'amount'        => 'required|numeric|min:0.01',
            'unit_id'       => 'required|exists:units,id',
        ]);

        $productRecipe->update($data);
        return $productRecipe;
    }

   public function updateRecipe(Request $request, Product $product)
{
    $request->validate([
        'ingredients' => 'required|array',
        'amounts' => 'required|array',
    ]);

    $syncData = [];

    foreach ($request->ingredients as $index => $ingredientId) {

        if(!$ingredientId) continue;

        $ingredient = Ingredient::find($ingredientId); // جلب بيانات الخامه

        $syncData[$ingredientId] = [
            'amount' => $request->amounts[$index],
            'unit_id' => $ingredient->unit_id, // <-- خد الوحدة من الخامه نفسها
        ];
    }

    // حذف القديم وإضافة الجديد
    $product->ingredients()->sync($syncData);

    return back()->with('success', 'تم تحديث الريسبي بنجاح');
}


    public function destroy(ProductRecipe $productRecipe)
    {
        $productRecipe->delete();
        return response()->noContent();
    }
}
