<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;
    protected $fillable = [
        'full_name',
        'phone_number',
        'fcm_token',
        'verification_code',
        'verification_code_expiry',
        'is_verified',
        'lang',
        'role',
        'latitude',
        'longitude',
        'theme_mode',
        'allow_gps',
        'allow_notifications'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function store(){
       return $this->hasOne(Store::class);
    }
    public function orders(){
        return $this->hasMany(Order::class);
     }
    public function favorites(){
        return $this->belongsToMany(Product::class,'favorites','user_id','product_id');
    }

    public function ToggleToFavorities($product_id){
        $product = Product::find($product_id);
        $this->favorites()->toggle($product);
    }
}
