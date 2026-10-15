<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetionNotAdded extends Model

{
  protected $fillable = [
    'client_id',
    'start_time',
    'persons'
  ];


  public function client()
  {
    return $this->belongsTo(Client::class, 'client_id');

  }




}
