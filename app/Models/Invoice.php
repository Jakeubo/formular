<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'order_id',
        'customer_id',
        'issue_date',
        'due_date',
        'total_price',
        'status',
        'payment_status',
        'variable_symbol',   // ← doplnit
        'carrier',           // ← doporučené doplnit
        'carrier_address',   // ← doporučené doplnit (pokud máš ve struktuře)
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
