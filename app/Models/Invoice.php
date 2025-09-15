<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'total_price',
        'status',
        'due_date',
        'payment_status'
    ];

    // Faktura má víc položek
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // Faktura patří zákazníkovi
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
