<?php

namespace App\Http\Controllers;

use App\Models\Order;

class CustomerController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = Order::selectRaw('MIN(id) as id, first_name, last_name, email, address, city, zip, country, COUNT(*) as orders_count')
            ->groupBy('email', 'first_name', 'last_name', 'address', 'city', 'zip', 'country');

        // vyhledávání
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        // nejnovější zákazníci nahoře
        $customers = $query->orderByDesc('id')->paginate(20);

        return view('customers.index', compact('customers'));
    }


    public function show($id)
    {
        // Najdeme konkrétní objednávku (reprezentující zákazníka)
        $customer = Order::findOrFail($id);

        return view('customers.show', compact('customer'));
    }
}
