<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientStock extends Model
{
    protected $fillable = [
        'ingredient_id',
        'amount',
        'unit_id',
        'type',
        'before_amount',
        'after_amount',
        'note',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (مهمين جدًا للتقارير)
    |--------------------------------------------------------------------------
    */

    public function scopeIn($query)
    {
        return $query->where('type', 'in');
    }

    public function scopeOut($query)
    {
        return $query->where('type', 'out');
    }
}
