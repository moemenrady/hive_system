<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeTransaction extends Model
{
    protected $fillable = ['employee_id', 'product_id', 'quantity', 'amount', 'type', 'notes'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
        public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
