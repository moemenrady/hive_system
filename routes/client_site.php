<?php

use App\Http\Controllers\ClientSite\ClientHomeController;
use Illuminate\Support\Facades\Route;

Route::get('/HomePage', [ClientHomeController::class, 'index'])->name('home');
Route::get('/search', [ClientHomeController::class, 'search'])->name('search');
Route::get('/profile', [ClientHomeController::class, 'profile'])->name('profile');
