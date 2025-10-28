<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Application;
use App\Models\Conditions;

class Vacancy extends Model
{
    use HasFactory;

    protected $table = 'vacancy';
    protected $fillable = [
        'units',
        'position',
        'location',
        'ex_date',
        'desription',
    ];

    public function application() {
        return $this->hasMany(Application::class, 'vacancy_id', 'id');
    }

    public function condition() {
        return $this->hasMany(Conditions::class, 'vacancy_id', 'id');
    }
}
