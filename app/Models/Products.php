<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cart;
use App\Models\Images_product;
use App\Models\Order_item;
use App\Models\User;
use App\Models\Review;

class Products extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'user_id',
    ];
    protected $appends = ['rating', 'image_url'];

    protected $hidden = ['images'];

    public function getRatingAttribute() {
        return round($this->review()->avg('rating') ?? 0, 1);
    }

    public function getImageUrlAttribute()
    {
        return $this->images->map(function ($img) {
            return env('APP_URL') . "/storage/" . $img->image; 
        })->toArray();
    }

    public function cart() {
        return $this->hasMany(Cart::class, 'product_id', 'id');
    }

    public function images() {
        return $this->hasMany(Images_product::class, 'product_id', 'id');
    }

    public function order_item() {
        return $this->hasMany(Order_item::class, 'product_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function review() {
        return $this->hasMany(Review::class, 'product_id', 'id');
    }
}
