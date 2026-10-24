<?php
namespace App\Services;

use App\Models\Shift;
use App\Models\ShiftAction;
use Illuminate\Support\Facades\Auth;

class ShiftService
{
  /**
   * تسجيل عملية جديدة في الشيفت الحالي
   */
  public function logAction(string $actionType, ?int $invoiceId = null, float $amount = 0, ?string $paymentType = 'cash', ?float $expenseAmount = null, ?string $notes = null)
  {
    $user = Auth::user();

    $shift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();
    if (!$shift) {
      if ($user && $user->hasRole('admin')) {
        \Log::info("Admin #{$user->id} executed {$actionType} without an active shift. Shift log skipped.");
        return null;
      }

      session()->flash('shift_required', true);

      throw new \Exception("⚠️ لازم تفتح شيفت الأول قبل تسجيل أي عملية");
    }

   $action = ShiftAction::create([
  'shift_id' => $shift->id,
  'action_type' => $actionType,
  'invoice_id' => $invoiceId,
  'amount' => $amount,
  'payment_type' => $paymentType ?? 'cash', // 👈 مهم
  'expense_amount' => $expenseAmount,
  'notes' => $notes,
]);

    // تحديث إجماليات الشيفت
    if ($actionType === 'expense_note') {
      $shift->total_expense += $expenseAmount ?? 0;
    } else {
      $shift->total_amount += $amount ?? 0;
    }
    $shift->save();

    return $action;
  }
}