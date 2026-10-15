<?php

use App\Http\Controllers\BlackListController;
use Illuminate\Support\Facades\Route;

Route::post(
    '/customers/{client}/black-list',
    [BlackListController::class, 'store']
)->name('black-list.store');

Route::delete(
    '/customers/{client}/black-list',
    [BlackListController::class, 'destroy']
)->name('black-list.destroy');
