<?php

use App\Http\Controllers\SessionPurchaseController;
use App\Http\Controllers\SationController;
use App\Http\Controllers\SetionNotAddedController;


Route::middleware('auth')->group(function () {
    Route::get('/new-sessions', [SetionNotAddedController::class, 'index'])->name('new-session.index');
    Route::get('/new-sessions/ajax', [SetionNotAddedController::class, 'ajaxNotAdded'])->name('sessions.notAdded.ajax');

    // ✅ غير GET إلى POST
    Route::post('/start-for-late', [SationController::class, 'start_for_late'])->name('session.start-for-late');
    Route::delete(
        '/session-not-added/bulk-delete',
        [SetionNotAddedController::class, 'bulkDelete']
    )->name('session-not-added-bulk-delete');

    Route::delete(
        '/session-not-added/clear-all',
        [SetionNotAddedController::class, 'clearAll']
    )->name('session-not-added-clear-all');

    // ❗️ آخر واحد خالص
    Route::delete(
        '/session-not-added/{id}',
        [SetionNotAddedController::class, 'destroy']
    )->name('session-not-added-delete');
    Route::post('/new-session/added', [SetionNotAddedController::class, 'store'])->name('new-session.store');
});
