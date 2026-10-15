<?php
use App\Http\Controllers\SupervisorActivityController;
use Illuminate\Support\Facades\Route;

Route::get('/supervisor-activities', [SupervisorActivityController::class, 'index'])->name('activities.index');
Route::put('/supervisor-activities/{id}', [SupervisorActivityController::class, 'update'])->name('activities.update');
Route::delete('/supervisor-activities/{id}', [SupervisorActivityController::class, 'destroy'])->name('activities.destroy');