<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Orders;
use App\Models\Products;

class Order_item extends Model
{
    use HasFactory;

    protected $table = 'order_item';
    protected $fillable = [
        'quantity',
        'name_at_purchase',
        'description_at_purchase',
        'price_at_purchase',
        'subtotal',
        'order_id',
        'product_id',
    ];

    public function order() {
        return $this->belongsTo(Orders::class, 'order_id', 'id');
    }

    public function product() {
        return $this->belongsTo(Products::class, 'product_id', 'id');
    }
}
