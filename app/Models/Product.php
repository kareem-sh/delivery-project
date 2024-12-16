<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
class Product extends Model
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */   
  public function store(){
    return $this->belongsTo(Store::class);
  }

  public function favorites(){
    return $this->belongsToMany(User::class,'favorites','product_id','user_id');
  }
}
