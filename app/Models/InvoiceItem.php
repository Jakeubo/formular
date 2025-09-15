<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'qty',
        'unit_price',
        'total'
    ];

    // Položka patří faktuře
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
