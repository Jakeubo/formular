<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShippingMethod;

class SettingController extends Controller
{
    public function index()
    {
        $shippingMethods = ShippingMethod::all();
        return view('settings.index', compact('shippingMethods'));
    }

    public function updateShipping(Request $request)
    {
        foreach ($request->shipping as $id => $price) {
            ShippingMethod::where('id', $id)->update(['price' => $price]);
        }

        return back()->with('success', '✅ Ceny dopravy byly úspěšně aktualizovány.');
    }
}
