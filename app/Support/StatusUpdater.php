<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\Ingredient;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Shift;
use App\Models\ShiftAction;
use App\Models\Subscription;
use App\Models\Sation;
use App\Models\User;
use Carbon\Carbon;

use function Symfony\Component\Translation\t;

class StatusUpdater
{

  // داخل كلاس StatusUpdater
  public static function run()
  {
    Sation::where('status', 'active')
      ->where('isPayed', true)
      ->where('start_time', '<=', now()->subHours(24))
      ->update([
        'status' => 'closed',
        'end_time' => now(),
      ]);
    $user = \Auth::user();
    if ($user && $user->hasRole('admin')) {

      // 1. فحص المنتجات (is_produced = false والكمية <= الحد الأدنى)
      $lowStockProducts = Product::where('is_produced', false)
        ->whereColumn('quantity', '<=', 'min_quantity')
        ->get();

      foreach ($lowStockProducts as $product) {
        Notification::updateOrCreate(
          [
            'type' => 'product_stock',
            'click_url' => "/products/{$product->id}",
            'is_read' => false
          ],
          [
            'message' => "انتباه: المنتج ({$product->name}) وصل للحد الأدنى ({$product->quantity}/{$product->min_quantity})",
          ]
        );
      }

      // 2. فحص الخامات (stock <= alert_stock)
      $lowStockIngredients = Ingredient::with('unit')
        ->whereColumn('stock', '<=', 'alert_stock')
        ->get();

      foreach ($lowStockIngredients as $ingredient) {
        $unitName = $ingredient->unit ? $ingredient->unit->name : 'وحدة';
        Notification::updateOrCreate(
          [
            'type' => 'ingredient_stock',
            'click_url' => route('ingredients.index') . "?open_id={$ingredient->id}",
            'is_read' => false
          ],
          [
            'message' => "نقص خامات: {$ingredient->name} الحالي {$ingredient->stock} {$unitName} (الحد الآمن {$ingredient->alert_stock} {$unitName})",
          ]
        );
      }
    }
    // تحديث الاشتراكات المنتهية
    Subscription::where('is_active', true)
      ->where('end_date', '<=', now()) // كامل التاريخ والوقت
      ->update(['is_active' => false]);

    // تحديث الحجوزات scheduled اللي ميعاد بدايتها جه
    Booking::where('status', 'scheduled')
      ->where('start_at', '<=', now()) // كامل التاريخ والوقت
      ->update(['status' => 'due']);

    Subscription::where('is_active', true)
      ->whereNotNull('visit_date')
      ->where('visit_date', '<=', now()->subDay()->toDateString()) // لو عدّى يوم
      ->update(['attendees' => false]);
    Subscription::where('is_active', false)
      ->whereNotNull('visit_date')
      ->update(['attendees' => false]);


    if ($user->hasRole('admin')) {
      $now = Carbon::now();
      $todayMidnight = Carbon::today(); // اليوم 00:00 بحسب timezone التطبيق

      // أحدث شيفت مفتوح (إن وجد)
      $openShift = Shift::where('user_id', $user->id)
        ->whereNull('end_time')
        ->latest('start_time')
        ->first();

      // إذا فيه شيفت مفتوح وبدأ قبل منتصف الليل -> نغلقه عند منتصف الليل ونفتح شيفت جديد يبدأ عند منتصف الليل
      if ($openShift) {
        // لو الشيفت بدأ قبل بداية اليوم الحالي => لازم نقسمه (close عند midnight وفتح جديد)
        if (Carbon::parse($openShift->start_time)->lt($todayMidnight)) {



          // اغلاق الشيفت القديم عند بداية اليوم (midnight)
          $openShift->end_time = $todayMidnight;
          $openShift->duration = Carbon::parse($openShift->start_time)->diffInMinutes($todayMidnight);
          $openShift->save();

          ShiftAction::create([
            'shift_id' => $openShift->id,
            'action_type' => 'end_shift',
            'notes' => 'تم غلق الشيفت تلقائياً عند بداية اليوم (تقسيم شيفت بدأ قبل منتصف الليل)',
            'amount' => 0,
            'expense_amount' => 0,
          ]);

          // تأكد أنه مفيش شيفت مفتوح تاني بعد الحفظ (حماية من تكرار)
          $exists = Shift::where('user_id', $user->id)->whereNull('end_time')->exists();

          if (!$exists) {
            // نفتح شيفت جديد يبدأ عند منتصف الليل
            $newShift = Shift::create([
              'user_id' => $user->id,
              'start_time' => $todayMidnight,
              'total_amount' => 0,
              'total_expense' => 0,
            ]);

            ShiftAction::create([
              'shift_id' => $newShift->id,
              'action_type' => 'start_shift',
              'notes' => 'تم فتح شيفت تلقائياً بعد تقسيم الشيفت عند بداية اليوم',
              'amount' => 0,
              'expense_amount' => 0,
            ]);
          }


          return;
        }

        // الحالة: فيه شيفت مفتوح وبدأ اليوم نفسه => لا نفعل شي الآن.
        return;
      }
      $newShift = Shift::create([
        'user_id' => $user->id,
        'start_time' => $todayMidnight,
        'total_amount' => 0,
        'total_expense' => 0,
      ]);

      ShiftAction::create([
        'shift_id' => $newShift->id,
        'action_type' => 'start_shift',
        'notes' => 'تم فتح شيفت تلقائياً لبداية اليوم (start_time = اليوم 00:00)',
        'amount' => 0,
        'expense_amount' => 0,
      ]);
    }
  }
}
