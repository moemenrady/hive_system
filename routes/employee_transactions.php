<?php

use App\Http\Controllers\EmployeeTransactionsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    Route::get('/employee_transactions', [EmployeeTransactionsController::class, 'index'])
        ->name('employee_transactions.index');

    Route::get('/employee_transactions/create', [EmployeeTransactionsController::class, 'create'])
        ->name('employee_transactions.create');

    Route::post('/employee_transactions', [EmployeeTransactionsController::class, 'store'])
        ->name('employee_transactions.store');

    Route::get('/employee_transactions/{employee_transaction}', [EmployeeTransactionsController::class, 'show'])
        ->name('employee_transactions.show');

    Route::delete('/employee_transactions/{employee_transaction}', [EmployeeTransactionsController::class, 'destroy'])
        ->name('employee_transactions.destroy');
    Route::get('/employees/{id}/free-drink-status', [EmployeeTransactionsController::class, 'freeDrinkStatus']);


    // إدارة العمليات المالية (Transactions)
    Route::post('/employees/{id}/transactions', [EmployeeTransactionsController::class, 'storeTransaction'])->name('employees.transactions.store');
    Route::put('/transactions/{id}', [EmployeeTransactionsController::class, 'updateTransaction'])->name('employees.transactions.update');
    Route::delete('/transactions/{id}', [EmployeeTransactionsController::class, 'destroyTransaction'])->name('employees.transactions.destroy');
});
