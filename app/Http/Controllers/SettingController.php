<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShippingMethod;
use App\Models\EmailLog;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Hlavní stránka nastavení – dopravy, logy a přepínač satisfaction emailů
     */
    public function index(Request $request)
    {
        $shippingMethods = ShippingMethod::all();

        // 🔧 Hodnota přepínače satisfaction mailů
        $satisfactionEnabled = Setting::get('satisfaction_emails_enabled', '0');

        // 📧 Email logy s vyhledáváním
        $logs = EmailLog::orderByDesc('sent_at')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->q;
                $query->where('to_email', 'like', "%$q%")
                      ->orWhere('subject', 'like', "%$q%");
            })
            ->paginate(10);

        return view('settings.index', compact('shippingMethods', 'logs', 'satisfactionEnabled'));
    }

    /**
     * Uložení cen dopravy
     */
    public function updateShipping(Request $request)
    {
        foreach ($request->shipping as $id => $price) {
            ShippingMethod::where('id', $id)->update(['price' => $price]);
        }

        return back()->with('success', '✅ Ceny dopravy byly úspěšně aktualizovány.');
    }

    /**
     * Uložení globálních nastavení (např. přepínač satisfaction mailů)
     */
    public function updateSettings(Request $request)
    {
        Setting::set('satisfaction_emails_enabled', $request->input('satisfaction_emails_enabled', '0'));

        return back()->with('success', '✅ Nastavení bylo úspěšně uloženo.');
    }
}
