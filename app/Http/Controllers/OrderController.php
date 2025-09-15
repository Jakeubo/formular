<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // Honeypot
        if ($request->filled('website')) {
            abort(403, 'Bot detected');
        }

        // Validace
        $validated = $request->validate([
            'first_name' => 'required',
            'last_name'  => 'required',
            'email'      => 'required|email',
            'phone'      => 'required',
            'address'    => 'required',
            'city'       => 'required',
            'zip'        => 'required',
            'carrier'    => 'required',
            'country'    => 'required',
            // 🔥 podmíněná validace pro výdejní místo
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

        $exists = $query->exists();

        if ($exists) {
            return back()->with('error', '⚠️ Tento formulář už byl odeslán nedávno.');
        }

        // ✅ Uložení objednávky (jen pokud není duplicita)
        $order = Order::create($validated);

        // 📢 Zpráva pro Discord
        $labelLink = "[Vytisknout štítek](https://zapichnito3d.cz/print/wait_label.html?token={$order->id})";

        switch (strtolower($order->carrier)) {
            case "osobni":
                $content = "{$order->id}. {$order->first_name} {$order->last_name}\n\n"
                    . "**Mail:** {$order->email}\n"
                    . "**Telefon:** {$order->phone}\n"
                    . "**Osobní vyzvednutí:** Sushi hub\n\n"
                    . "{$labelLink}";
                break;

            case "ppl":
                $content = "{$order->id}. {$order->first_name} {$order->last_name}\n\n"
                    . "**Mail:** {$order->email}\n"
                    . "**Telefon:** {$order->phone}\n"
                    . "**Adresa:** {$order->address}, {$order->city}, {$order->zip}\n"
                    . "**Dopravce:** PPL\n\n"
                    . "{$labelLink}";
                break;

            default:
                $content = "{$order->id}. {$order->first_name} {$order->last_name}\n\n"
                    . "**Mail:** {$order->email}\n"
                    . "**Telefon:** {$order->phone}\n"
                    . "**Dopravce:** {$order->carrier}\n\n"
                    . "{$labelLink}";
        }

        // Odeslání na Discord
        $webhookUrl = config('services.discord.webhook');

        Http::post($webhookUrl, [
            'content' => $content
        ]);

        return back()->with('success', '✅ Děkujeme! Formulář byl úspěšně odeslán.');
    }
}
