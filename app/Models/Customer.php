<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'zip',
        'country',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
