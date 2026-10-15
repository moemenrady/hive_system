<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name', 'symbol'];

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }

    public function recipeItems()
    {
        return $this->hasMany(ProductRecipe::class);
    }

    public function fromConversions()
    {
        return $this->hasMany(UnitConversion::class, 'from_unit_id');
    }

    public function toConversions()
    {
        return $this->hasMany(UnitConversion::class, 'to_unit_id');
    }
}
