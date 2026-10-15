<?php

use App\Http\Controllers\ProductRecipeController;
use App\Models\ProductRecipe;
use Illuminate\Support\Facades\Route;


Route::post('/product-recipes/create', [ProductRecipeController::class, 'store'])->name('product-recipes.store');

Route::put(
    '/products/{product}/update-recipe',
    [ProductRecipeController::class, 'updateRecipe']
)
    ->name('products.updateRecipe');
