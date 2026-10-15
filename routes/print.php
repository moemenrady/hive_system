<?php
use App\Http\Controllers\PrintController;
use Illuminate\Support\Facades\Route;

Route::prefix('print')->name('print.')->group(function () {

    Route::get('/session-purchases/{session}', [PrintController::class, 'sessionPurchases'])
        ->name('session-purchases');

    Route::get('/session/{session}', [PrintController::class, 'sessionInvoice'])
        ->name('session');
});