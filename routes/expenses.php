<?php

use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseDraftController;
Route::middleware('auth')->group(function () {
  //المصروف 
  Route::get('/expense-drafts', [ExpenseDraftController::class, 'index'])->name('expense-drafts.index');
  Route::post('/expense-drafts', [ExpenseDraftController::class, 'store'])->name('expense-drafts.store');
  Route::get('/draft-expenses', [ExpenseDraftController::class, "create"])->name('admin_draft.create')->middleware('admin');
  Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create')->middleware('admin');
  Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index')->middleware('admin');
  Route::post('/expenses/store', [ExpenseController::class, "store"])->name('expense.store')->middleware('admin');
  Route::post('/expense-drafts/{draft}/convert', [ExpenseController::class, 'convertFromDraft'])->name('expense-drafts.convert')->middleware('admin');
  Route::get('/expenses/ajaxSearch', [ExpenseController::class, 'ajaxSearch'])->name('expense.ajaxSearch')->middleware('admin');
  


});


