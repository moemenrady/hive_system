<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'stock',
        'unit_id',
        'alert_stock', // ← أضفناه هنا
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function recipes()
    {
        return $this->hasMany(ProductRecipe::class);
    }

    public function stocks()
    {
        return $this->hasMany(IngredientStock::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (مهمين جدًا للـ Dashboard)
    |--------------------------------------------------------------------------
    */

    // إجمالي الداخل
    public function totalIn()
    {
        return $this->stocks()->in()->sum('amount');
    }

    // إجمالي الخارج
    public function totalOut()
    {
        return $this->stocks()->out()->sum('amount');
    }

    // الاستهلاك في فترة
    public function consumedBetween($from, $to)
    {
        return $this->stocks()
            ->out()
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');
    }

    // آخر حركة
    public function lastMovement()
    {
        return $this->stocks()->latest()->first();
    }
}
