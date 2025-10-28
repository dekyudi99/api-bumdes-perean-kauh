<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Products;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'cart';
    protected $fillable = [
        'quantity',
        'user_id',
        'product_id',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function product() {
        return $this->belongsTo(Products::class, 'product_id', 'id');
    }
}