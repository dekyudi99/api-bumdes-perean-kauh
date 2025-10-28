<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Call_service extends Model
{
    use HasFactory;

    protected $table = 'call_service';
    protected $fillable = [
        'take_location',
        'additional_note',
        'status',
        'user_id',
        'officer_id',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
