<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use App\Models\ShippingMethod;
use Illuminate\Support\Str;




class OrderController extends Controller
{
    public function create()
    {
        $shippingMethods = ShippingMethod::all();
        return view('order-form', compact('shippingMethods'));
    }
    public function show(Order $order)
    {
        return response()->json([
            'id'             => $order->id,
            'first_name'     => $order->first_name,
            'last_name'      => $order->last_name,
            'email'          => $order->email,
            'phone'          => $order->phone,
            'carrier'        => $order->carrier,
            'carrier_id'     => $order->carrier_id,
            'carrier_address' => $order->carrier_address,
            'address'        => $order->address,
            'city'           => $order->city,
            'zip'            => $order->zip,
        ]);
    }

    public function store(Request $request)
    {
        // Honeypot
        if ($request->filled('website')) {
            abort(403, 'Bot detected');
        }

        // Validace vstupu
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name'  => 'required|string|max:50',

            // Email – RFC + DNS kontrola
            'email'      => 'required|email:rfc,dns|max:255',

            // Telefon – povolen jen +420 nebo 9 číslic
            'phone'      => [
                'required',
                'regex:/^(\+420)?[0-9]{9}$/'
            ],

            'address'    => 'required|string|max:255',
            'city'       => 'required|string|max:100',

            // PSČ – 12345 nebo 123 45
            'zip'        => 'required|regex:/^\d{3}\s?\d{2}$/',

            'carrier'    => 'required|string',
            'country'    => 'required|in:CZ,SK',

            'carrier_id' => [
                function ($attribute, $value, $fail) use ($request) {
                    if (in_array($request->carrier, ['Balikovna', 'Zasilkovna']) && empty($value)) {
                        $fail("Výdejní místo je povinné pro {$request->carrier}.");
                    }
                }
            ],
            'carrier_address' => [
                function ($attribute, $value, $fail) use ($request) {
                    if (in_array($request->carrier, ['Balikovna', 'Zasilkovna']) && empty($value)) {
                        $fail("Výdejní místo je povinné pro {$request->carrier}.");
                    }
                }
            ],
        ]);

        // 🛡️ Kontrola duplicit
        $query = Order::where('email', $validated['email'])
            ->where('carrier', $validated['carrier'])
            ->where('created_at', '>=', now()->subMinutes(2));

        if (!empty($validated['carrier_id'])) {
            $query->where('carrier_id', $validated['carrier_id']);
        } elseif (!empty($validated['carrier_address'])) {
            $query->where('carrier_address', $validated['carrier_address']);
        }

        if ($query->exists()) {
            return back()->with('error', '⚠️ Tento formulář už byl odeslán nedávno.');
        }

        // ✅ Uložení objednávky s tokenem
        $validated['public_token'] = Str::random(40);
        $order = Order::create($validated);

        // 📢 Příprava zprávy pro Discord
        switch (strtolower($order->carrier)) {
            case "osobni":
                $content = "{$order->id}. {$order->first_name} {$order->last_name}\n\n"
                    . "**Mail:** {$order->email}\n"
                    . "**Telefon:** {$order->phone}\n"
                    . "**Osobní vyzvednutí:** Sushi hub\n\n"
                    . "[Vytisknout štítek](" . route('labels.wait_label', [
                        'token'   => $order->public_token,   // ✅ token
                        'carrier' => 'osobni'
                    ]) . ")\n\n"
                    . "Pro Zápicháře: <@1239282326601732238> a <@280429913130139648>\n";
                break;

            case "zasilkovna":
                $content = "{$order->id}. {$order->first_name} {$order->last_name}\n\n"
                    . "**Mail:** {$order->email}\n"
                    . "**Telefon:** {$order->phone}\n"
                    . "**Výdejní místo (Zásilkovna):** {$order->carrier_id}, {$order->carrier_address}\n\n"
                    . "[Vytisknout štítek](" . route('labels.wait_label', [
                        'token'   => $order->public_token,   // ✅ token
                        'carrier' => 'zasilkovna'
                    ]) . ")\n\n"
                    . "Pro Zápicháře: <@1239282326601732238> a <@280429913130139648>\n";
                break;

            case "balikovna":
                $content = "{$order->id}. {$order->first_name} {$order->last_name}\n\n"
                    . "**Mail:** {$order->email}\n"
                    . "**Telefon:** {$order->phone}\n"
                    . "**Výdejní místo (Balíkovna):** {$order->carrier_id}, {$order->carrier_address}\n\n"
                    . "[Vytisknout štítek](" . route('labels.wait_label', [
                        'token'   => $order->public_token,   // ✅ token
                        'carrier' => 'balikovna'
                    ]) . ")\n\n"
                    . "Pro Zápicháře: <@1239282326601732238> a <@280429913130139648>\n";
                break;

            case "ppl":
                $content = "{$order->id}. {$order->first_name} {$order->last_name}\n\n"
                    . "**Mail:** {$order->email}\n"
                    . "**Telefon:** {$order->phone}\n"
                    . "**Adresa:** {$order->address}, {$order->city}, {$order->zip}\n"
                    . "**Dopravce:** PPL Home\n\n"
                    . "[Vytisknout štítek](" . route('labels.wait_label', [
                        'token'   => $order->public_token,   // ✅ token
                        'carrier' => 'pplhome'
                    ]) . ")\n\n"
                    . "Pro Zápicháře: <@1239282326601732238> a <@280429913130139648>\n";
                break;

            case "pplparcelshop":
                $content = "{$order->id}. {$order->first_name} {$order->last_name}\n\n"
                    . "**Mail:** {$order->email}\n"
                    . "**Telefon:** {$order->phone}\n"
                    . "**Výdejní místo (PPL ParcelShop):** {$order->carrier_id}, {$order->carrier_address}\n\n"
                    . "[Vytisknout štítek](" . route('labels.wait_label', [
                        'token'   => $order->public_token,   // ✅ token
                        'carrier' => 'pplparcel'
                    ]) . ")\n\n"
                    . "Pro Zápicháře: <@1239282326601732238> a <@280429913130139648>\n";
                break;

            default:
                $content = "{$order->id}. {$order->first_name} {$order->last_name}\n\n"
                    . "**Mail:** {$order->email}\n"
                    . "**Telefon:** {$order->phone}\n"
                    . "**Dopravce:** {$order->carrier}\n\n"
                    . "[Vytisknout štítek](" . route('labels.wait_label', [
                        'token'   => $order->public_token,   // ✅ token
                        'carrier' => 'other'
                    ]) . ")\n\n"
                    . "Pro Zápicháře: <@1239282326601732238> a <@280429913130139648>\n";
        }


        // Odeslání na Discord
        $webhookUrl = config('services.discord.webhook');
        Http::post($webhookUrl, ['content' => $content]);

        return back()->with('success', '✅ Děkujeme! Formulář byl úspěšně odeslán.');
    }
}
