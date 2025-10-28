<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products;

class Images_product extends Model
{
    use HasFactory;

    protected $table = 'images_product';
    protected $fillable = [
        'product_id',
        'image',
    ];

    public function product() {
        return $this->belongsTo(Products::class, 'product_id', 'id');
    }
}
