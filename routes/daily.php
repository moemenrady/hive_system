<?php

use App\Http\Controllers\Admin\ShiftAdminController;
use App\Http\Controllers\ShiftController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

  Route::get('/shift-manager', [ShiftController::class, 'create'])->name('shift.create');
  Route::get('/shifts', [ShiftController::class, 'index'])->name('shift.index')->middleware('admin');

  Route::get('/shifts/{shift}', [ShiftController::class, 'show'])->name('shift.show');
  Route::post('/shift/start', [ShiftController::class, 'startShift'])->name('shift.start');
  Route::get('/shift/prompt', [ShiftController::class, 'prompt'])->name('shift.prompt');
  Route::post('/shift/end', [ShiftController::class, 'endShift'])->name('shift.end');
  Route::get('/shift/check-open', [ShiftController::class, 'checkOpen'])->name('shift.check');

  Route::get('/admin/calendar', [ShiftAdminController::class, 'calendar'])->name('admin.calendar')->middleware("admin");
  Route::get('/admin/active-shifts', [ShiftController::class, 'activeShifts'])
    ->name('shifts.active')->middleware("admin");

  Route::get('/admin/day-shifts', [ShiftAdminController::class, 'dayShifts'])->name('admin.day_shifts')->middleware("admin");
  Route::post('/admin/shifts/{id}/close', [ShiftController::class, 'closeShift'])
    ->name('shifts.close')->middleware("admin");

  Route::delete('/admin/shifts/{id}', [ShiftController::class, 'deleteShift'])
    ->name('shifts.delete')->middleware("admin");
});

// Calendar & day view
Route::middleware(['auth', 'admin'])->group(function () {});
