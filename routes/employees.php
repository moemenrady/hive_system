<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {


Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    
    // تحديث بيانات الموظف الأساسية
    Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
    

  Route::post('/employees/discount-percent', [EmployeeController::class, 'storeDiscountPercent'])->name('employees.storeDiscountPercent');
  Route::put('/employees/discount-percent/{id}', [EmployeeController::class, 'updateDiscountPercent'])->name('employees.updateDiscountPercent');
  Route::delete('/employees/discount-percent/{id}', [EmployeeController::class, 'destroyDiscountPercent'])->name('employees.destroyDiscountPercent');
  Route::get(
    '/employees/discount-percent',
    [EmployeeController::class, 'indexDiscountPercent']
  )->name('employees.discountPercent');
  Route::resource('employees', EmployeeController::class);
});
