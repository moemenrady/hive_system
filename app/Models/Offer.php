<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{

    protected $fillable = ['title', 'sub_title', 'discount_percentage', 'image', 'link', 'expires_at', 'is_active'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
