<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order_item;
use App\Models\User;

class Orders extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $fillable = [
        'invoice_number',
        'total_amount',
        'status',
        'shipping_address',
        'user_id',
    ];

    public function order_item() {
        return $this->hasMany(Order_item::class, 'order_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
