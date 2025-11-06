<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Membership extends Model
{
    use HasFactory;

    protected $table = 'membership';
    protected $fillable = [
        'invoice_number',
        'type',
        'is_active',
        'expired_at',
        'user_id',
    ];
    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
