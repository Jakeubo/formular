<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

// Zobrazení formuláře
Route::get('/', function () {
    return view('order-form'); // náš Blade formulář
});

// Uložení dat z formuláře
Route::post('/order', [OrderController::class, 'store'])->name('order.store');
