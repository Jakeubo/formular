<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingMethodsSeeder extends Seeder
{
    public function run()
    {
        DB::table('shipping_methods')->insert([
            ['code' => 'balikovna',     'name' => 'Balíkovna',     'price' => 79],
            ['code' => 'zasilkovna',    'name' => 'Zásilkovna',    'price' => 89],
            ['code' => 'ppl',           'name' => 'PPL Home',      'price' => 129],
            ['code' => 'ppl_parcelshop','name' => 'PPL Parcelshop','price' => 99],
            ['code' => 'osobni',        'name' => 'Osobní odběr',  'price' => 0],
        ]);
    }
}
