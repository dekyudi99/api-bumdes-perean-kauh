<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction_history extends Model
{
    use HasFactory;

    protected $table = 'transaction_history';
    protected $fillable = [
        'date',
        'invoice_number',
        'channel',
        'status',
        'value',
        'email_customer',
    ];
}
