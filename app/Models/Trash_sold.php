<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trash_sold extends Model
{
    use HasFactory;

    protected $table = 'trash_sold';
    protected $fillable = [
        'trash_type',
        'weight',
        'user_id',
        'officer',
    ];
}
