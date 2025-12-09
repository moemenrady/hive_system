<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiftAction;
use Illuminate\Http\Request;
use App\Models\Shift;
use Carbon\Carbon;

class ShiftAdminController extends Controller
{

public function dayShifts(Request $request) 
{
    // لو فيه تاريخ من الـ query نستخدمه
    $date = $request->query('date')
        ? Carbon::parse($request->query('date'))->toDateString()
        : Carbon::today()->toDateString();

    // نجيب شيفتات اليوم
    $shifts = Shift::with('user')
        ->whereDate('created_at', $date)
        ->orderBy('created_at')
        ->get();

    foreach ($shifts as $shift) {
        $isClosed = !is_null($shift->updated_at) && $shift->updated_at->gt($shift->created_at);

        if (empty($shift->duration) && $isClosed) {
            $minutes = $shift->created_at->diffInMinutes($shift->updated_at);
            $shift->duration = $minutes;
        }

        $shift->computed_end_time = $isClosed ? $shift->updated_at->format('Y-m-d H:i') : null;
        $shift->computed_start_time = $shift->created_at ? $shift->created_at->format('Y-m-d H:i') : null;
    }

    // المجاميع العادية
    $total_income = $shifts->sum(fn($s) => $s->total_amount ?? 0);
    $total_expense = $shifts->sum(fn($s) => $s->total_expense ?? 0);
    $total_net = $total_income - $total_expense;

    // ⭐ هنا أهم تعديل
    if ($shifts->count() > 0) {
        // نجيب كل IDs بتاعة الشيفتات بتاعة اليوم
        $shiftIds = $shifts->pluck('id');

        // إجمالي الكاش لليوم كله
        $totalCash = ShiftAction::whereIn('shift_id', $shiftIds)
            ->where('payment_type', 'cash')
            ->sum('amount');

        // إجمالي الديجيتال لليوم كله
        $totalDigital = ShiftAction::whereIn('shift_id', $shiftIds)
            ->where('payment_type', 'digital')
            ->sum('amount');
    } else {
        $totalCash = 0;
        $totalDigital = 0;
    }

    return view('daily.admin.day_shifts', compact(
        'date',
        'shifts',
        'total_income',
        'total_expense',
        'total_net',
        'totalCash',
        'totalDigital'
    ));
}

  public function calendar()
  {
    // هذه الصفحة لا تحتاج بيانات مسبقة؛ الJS سيجلب بيانات الـ bookings من route موجود عندك: bookings.calendar
    return view('daily.admin.create');
  }
}
