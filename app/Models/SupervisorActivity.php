<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupervisorActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'supervisor_id',
        'action',
        'description',
    ];


    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
