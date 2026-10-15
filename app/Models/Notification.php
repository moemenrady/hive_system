<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    // اسم الجدول (لو هتسميه افتراضياً "notifications" مش محتاج)
    protected $table = 'notifications';

    // الحقول القابلة للتعبئة (fillable) علشان نقدر نعمل create أو updateOrCreate
    protected $fillable = [
        'type',        // نوع الإشعار: 'product_stock', 'ingredient_stock', 'booking', 'subscription', ...
        'message',     // نص الإشعار
        'click_url',   // الرابط عند الضغط على الإشعار (اختياري)
        'is_read',     // حالة القراءة: true أو false
    ];

    // casts لتحويل الحقول تلقائياً
    protected $casts = [
        'is_read' => 'boolean',
    ];
}