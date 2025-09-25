<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
public function index()
{
    $year = now()->year;

    $months = collect(range(1, 12))->mapWithKeys(function ($m) use ($year) {
        return [$m => \App\Models\Invoice::whereYear('issue_date', $year)
            ->whereMonth('issue_date', $m)
            ->sum('total_price')];
    });

    $incomeData = array_values($months->toArray()); // čisté pole čísel

    return view('dashboard.index', compact('year', 'months', 'incomeData'));
}


}
