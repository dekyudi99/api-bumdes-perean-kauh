<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Vacancy;

class Conditions extends Model
{
    use HasFactory;

    protected $table = 'conditions';
    protected $fillable = [
        'condition',
        'vacancy_id',
    ];

    public function vacancy() {
        return $this->belongsTo(Vacancy::class, 'vacancy_id', 'id');
    }
}
