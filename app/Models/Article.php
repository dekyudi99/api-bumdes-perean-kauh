<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $table = 'article';
    protected $fillable = [
        'title',
        'description',
        'image',
        'post_by',
    ];
    protected $appends = ['url_image'];

    public function getUrlImageAttribute() {
        return env('APP_URL') . '/storage/' . $this->image;
    }
}
