<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankPayment extends Model
{
protected $fillable = [
    'message_id',
    'variable_symbol',
    'amount',
    'account_number',
    'raw_text',
    'received_at',
];


    protected $casts = [
        'received_at' => 'datetime',
    ];
}
