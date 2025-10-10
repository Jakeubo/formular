<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
public function index(\Illuminate\Http\Request $request)
{
    $query = \App\Models\Order::query();

    if ($search = $request->input('search')) {
        $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%$search%")
              ->orWhere('last_name', 'like', "%$search%")
              ->orWhere('email', 'like', "%$search%")
              ->orWhere('city', 'like', "%$search%");
        });
    }

    $customers = $query->orderByDesc('id')->paginate(20);

    return view('customers.index', compact('customers'));
}


    public function show($id)
    {
        $customer = Order::findOrFail($id);
        return view('customers.show', compact('customer'));
    }

    /**
     * ✏️ Upraví údaje zákazníka (email, telefon, IČO)
     */
    public function update(Request $request, Order $customer)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'company_ico' => 'nullable|string|max:12',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.show', $customer->id)
            ->with('success', '✅ Údaje zákazníka byly aktualizovány.');
    }

    /**
     * 🗑️ Smaže zákazníka (objednávku)
     */
    public function destroy(Order $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', '🗑️ Zákazník byl odstraněn.');
    }
}
