<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
  use SoftDeletes;
  public $incrementing = false;
  protected $keyType = 'int';

  protected $fillable = [
    'id',
    'name',
    'price',
    'cost',
    'quantity',
    'min_quantity',
    'category_id',
    'is_produced'
  ];



  public function purchases()
  {
    return $this->hasMany(SessionPurchase::class);
  }

  public function important_products()
  {
    return $this->hasMany(ImportantProduct::class);
  }

  public function recipe()
  {
    return $this->hasMany(ProductRecipe::class);
  }
  public function ingredients()
  {
    return $this->belongsToMany(Ingredient::class, 'product_recipes')
      ->withPivot('amount', 'unit_id');
  }
  public function category()
  {
    return $this->belongsTo(Category::class);
  }
  public function transactions()
  {
    return $this->hasMany(EmployeeTransaction::class);
  }
}
