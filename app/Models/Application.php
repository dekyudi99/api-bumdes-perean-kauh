<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Vacancy;

class Application extends Model
{
    use HasFactory;

    protected $table = 'application';
    protected $fillable = [
        'no_telepon',
        'email',
        'additional_note',
        'user_id',
        'vacancy_id',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function vacancy() {
        return $this->belongsTo(Vacancy::class, 'vacancy_id', 'id');
    }
}
