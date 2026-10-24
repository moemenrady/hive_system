<?php
namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;

class BookingConflictService
{
    
    /**
     * التحقق من وجود تعارض مع فترة زمنية محددة
     */
    public function hasConflict(int $hallId, Carbon $start, Carbon $end, ?int $excludeBookingId = null): bool
    {
        $query = Booking::where('hall_id', $hallId)
            ->whereIn('status', ['scheduled', 'due', 'in_progress']) // ✅ نتحقق بس من الحالات الفعّالة
            ->where(function($q) use ($start, $end) {
                $q->where('start_at', '<', $end)
                  ->where('end_at', '>', $start);
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->exists();
    }

    /**
     * التحقق من وجود حجز جاري حالياً في نفس القاعة
     */
    public function hasInProgressConflict(int $hallId, ?int $excludeBookingId = null): bool
    {
        $query = Booking::where('hall_id', $hallId)
            ->where('status', 'in_progress');

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->exists();
    }

    /**
     * الحصول على الحجز الجاري حالياً في القاعة إن وجد
     */
    public function getInProgressBooking(int $hallId): ?Booking
    {
        return Booking::where('hall_id', $hallId)
            ->where('status', 'in_progress')
            ->with('client')
            ->first();
    }
}
