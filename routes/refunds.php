<?php

use App\Http\Controllers\RefundRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // واجهة الموظف (عرض طلباته وإضافة طلب جديد)
    Route::get('/refund-requests', [RefundRequestController::class, 'index'])->name('refund-requests.index');
    Route::post('/refund-requests', [RefundRequestController::class, 'store'])->name('refund-requests.store');

    // واجهة المدير (إدارة الطلبات، التأكيد والرفض)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/refund-requests', [RefundRequestController::class, 'adminIndex'])->name('refund-requests.index');
        Route::post('/refund-requests/{refundRequest}/approve', [RefundRequestController::class, 'approve'])->name('refund-requests.approve');
        Route::post('/refund-requests/{refundRequest}/reject', [RefundRequestController::class, 'reject'])->name('refund-requests.reject');
    });
});
