<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year); // načte rok z URL nebo aktuální

        $years = range(2025, now()->year); // dostupné roky

        $shippingPrices = \App\Models\ShippingMethod::pluck('price', 'code')
            ->mapWithKeys(fn($price, $code) => [strtolower(trim($code)) => (float)$price]);

        $invoices = \App\Models\Invoice::whereYear('issue_date', $year)->get();

        $incomeData = [];
        $shippingData = [];
        $carrierStatsByMonth = [];

        foreach (range(1, 12) as $m) {
            $monthInvoices = $invoices->filter(fn($inv) => optional($inv->issue_date)->month == $m);

            $incomeData[$m] = $monthInvoices->sum('total_price');

            $shippingSum = 0;
            $carrierCounts = [];

            foreach ($monthInvoices as $invoice) {
                $carrier = strtolower(trim($invoice->carrier));
                if (isset($shippingPrices[$carrier])) {
                    $shippingSum += $shippingPrices[$carrier];
                }
                $carrierCounts[$carrier] = ($carrierCounts[$carrier] ?? 0) + 1;
            }

            $shippingData[$m] = $shippingSum;
            $carrierStatsByMonth[$m] = $carrierCounts;
        }

        $totalShipping = array_sum($shippingData);
        $carrierStats = $invoices->groupBy('carrier')->map(fn($r) => $r->count())->sortDesc()->toArray();

        return view('dashboard.index', [
            'year' => $year,
            'incomeData' => array_values($incomeData),
            'shippingData' => array_values($shippingData),
            'months' => collect($incomeData),
            'carrierStats' => $carrierStats,
            'carrierStatsByMonth' => $carrierStatsByMonth,
            'totalShipping' => $totalShipping,
            'years' => $years,
            'selectedYear' => $year,
        ]);
    }
}
