<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftAction extends Model
{
    protected $fillable = [
        'shift_id',
        'action_type',
        'invoice_id',
        'expense_type_id', // 👈 مهم
        'amount',
        'expense_amount',
        'payment_type',
        'expense_draft_id', // تأكد أنه موجود هنا
        'notes',
    ];
    protected $table = 'shift_actions';

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    // الاسم الصحيح للعلاقة
    public function expenseDraft()
    {
        return $this->belongsTo(ExpenseDraft::class, 'expense_draft_id');
    }
    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class, 'expense_type_id');
    }
}
