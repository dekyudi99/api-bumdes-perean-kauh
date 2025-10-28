<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Temporary_token extends Model
{
    use HasFactory;

    protected $table = 'temporary_tokens';
    protected $fillable = [
        'email',
        'token',
        'expired_at'
    ];
}
