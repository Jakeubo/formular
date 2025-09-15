<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

protected $fillable = [
    'first_name',
    'last_name',
    'email',
    'phone',
    'address',
    'city',
    'zip',
    'carrier',
    'carrier_id',
    'carrier_address',
    'country',
];
}
