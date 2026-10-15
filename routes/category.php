<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

Route::post('/categories/store', [CategoryController::class, 'store'])->name('categories.store');

Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');