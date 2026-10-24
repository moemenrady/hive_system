<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'shift_id',
        'amount',
        'notes',
        'payment_type',
        'invoice_id',
        'client_id',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function shiftActions()
    {
        return $this->hasMany(ShiftAction::class, 'refund_request_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'معتمد',
            'rejected' => 'مرفوض',
            default => 'معلق (في الانتظار)',
        };
    }
}
