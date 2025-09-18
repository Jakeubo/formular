<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InvoiceController;

use App\Http\Controllers\BankPaymentController;

Route::get('/bank-payments', [BankPaymentController::class, 'index'])->name('bank-payments.index');

Route::get('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');


Route::get('/orders/{order}', function (\App\Models\Order $order) {
    return response()->json($order);
});

Route::post('/invoices/{invoice}/paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.paid');


Route::middleware(['auth'])->group(function () {
    Route::resource('invoices', InvoiceController::class);
});

Route::get('/', function () {
    return view('order-form');
});

Route::post('/order', [OrderController::class, 'store'])->name('order.store');


Route::get('/dashboard', function () {
    return redirect()->route('invoices.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
