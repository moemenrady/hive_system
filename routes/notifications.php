<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    Route::get('/notifications', [NotificationController::class,'index'])
        ->name('notifications.index');

    Route::post('/notifications/read/{id}', [NotificationController::class,'markAsRead'])
        ->name('notifications.read');

    Route::delete('/notifications/{id}', [NotificationController::class,'destroy'])
        ->name('notifications.delete');

});