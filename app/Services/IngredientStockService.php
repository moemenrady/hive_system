<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\IngredientStock;
use App\Models\Product;
use App\Models\ProductRecipe;
use Illuminate\Support\Facades\DB;

class IngredientStockService
{
    /**
     * خصم خامات المنتج المصنوع عند بيعه
     *
     * @param Product $product
     * @param float $quantity عدد المنتجات المباعة
     */
    public function deductProductIngredients(Product $product, float $quantity): void
    {
        if (!$product->is_produced) {
            return; // لو مش مصنوع مفيش حاجة نعملها
        }

        DB::transaction(function () use ($product, $quantity) {

            // جلب كل الوصفات للخامات
            $recipes = $product->recipe()->with('ingredient')->get();

            foreach ($recipes as $recipe) {

                $ingredient = $recipe->ingredient;
                if (!$ingredient) continue; // skip لو الخامة مش موجودة

                $before = $ingredient->stock;
                $deductAmount = $recipe->amount * $quantity;
                $after = $before - $deductAmount;

                if ($after < 0) {
                    throw new \RuntimeException("الخامة {$ingredient->name} غير كافية للكمية المطلوبة", 422);
                }

                // تحديث المخزون
                $ingredient->update([
                    'stock' => $after,
                ]);

                // تسجيل الحركة
                IngredientStock::create([
                    'ingredient_id' => $ingredient->id,
                    'amount'        => $deductAmount,
                    'unit_id'       => $ingredient->unit_id,
                    'type'          => 'out',
                    'before_amount' => $before,
                    'after_amount'  => $after,
                    'note'          => "استهلاك لصناعة {$product->name} (x{$quantity})",
                ]);
            }
        });
    }
    
}
