<?php

use App\Http\Controllers\IngredientController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\UnitController;

Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('ingredients', [IngredientController::class, 'index'])->name('ingredients.index');
    Route::post('ingredient/store', [IngredientController::class, 'store'])->name('ingredients.store');
    Route::post('ingredient/{ingredient}/update', [IngredientController::class, 'update'])
        ->name('ingredients.update');

    Route::post('units/store', [UnitController::class, 'store'])->name('units.store');

    Route::post('ingredient/{id}/add-stock', [IngredientController::class, 'addStock'])->name('ingredients.addStock');

    Route::delete('ingredient/{ingredient}/delete', [IngredientController::class, 'destroy'])
        ->name('ingredients.destroy');
    Route::get('/ingredients/history', [IngredientController::class, 'history'])->name('ingredients.history');
});
