<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Application;
use App\Models\Call_service;
use App\Models\Cart;
use App\Models\Membership;
use App\Models\Orders;
use App\Models\Point;
use App\Models\Products;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'address',
        'password',
        'role',
        'profile_picture',
        'email_verified',
        'phone_number_verified',
    ];
    protected $appends = ['profile'];

    public function getProfileAttribute()
    {
        if ($this->profile_picture) 
        {
            return (env('APP_URL').'/uploads/profile/'.$this->profile_picture);
        }
        return null;
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function application() {
        return $this->hasMany(Application::class, 'user_id', 'id');
    }

    public function call_service() {
        return $this->hasMany(Call_service::class, 'user_id', 'id');
    }

    public function cart() {
        return $this->hasMany(Cart::class, 'user_id', 'id');
    }

    public function membership() {
        return $this->hasOne(Membership::class, 'user_id', 'id');
    }

    public function order() {
        return $this->hasMany(Orders::class, 'user_id', 'id');
    }

    public function point() {
        return $this->hasMany(Point::class, 'user_id', 'id');
    }

    public function product() {
        return $this->hasMany(Products::class, 'user_id', 'id');
    }

    public function review() {
        return $this->hasMany(Review::class, 'user_id', 'id');
    }
}
