<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'to_email',
        'subject',
        'type',
        'invoice_id',
        'sent_at',
        'success',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'success' => 'boolean',
    ];
}
