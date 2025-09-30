<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\BankPaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\DashboardController;

use Illuminate\Support\Facades\URL;

Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])
    ->name('invoices.send');


Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])
    ->name('invoices.download')
    ->middleware('signed');

// Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])
//     ->name('invoices.download')
//     ->middleware('signed');


// 🏠 Domovská stránka = veřejný formulář
Route::get('/', fn () => view('order-form'))->name('form');
Route::post('/order', [OrderController::class, 'store'])->name('order.store');

// // 📑 Faktury – veřejné stažení
// Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])
//     ->name('invoices.download');

// … nahoře
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

// ✅ Kontrola prostředí (nechceš-li veřejné, dej to pod auth)
Route::get('/check-ini', function () {
    return [
        'loaded'      => php_ini_loaded_file(),
        'additional'  => php_ini_scanned_files(),
        'soap'        => class_exists(\SoapClient::class),
        'php_version' => PHP_VERSION,
        'sapi'        => php_sapi_name(),
    ];
});

// 📊 Admin dashboard – chráněný přístup
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');


    // 📦 Label routes
    Route::get('/labels/wait_label', fn () => view('labels.wait_label'))->name('labels.wait_label');
    Route::get('/labels/pplparcel/{order}', [LabelController::class, 'pplParcelshop'])->name('labels.pplparcel');
    Route::get('/labels/ppl/{order}', [LabelController::class, 'ppl'])->name('labels.ppl');
    Route::get('/labels/zasilkovna/{order}', [LabelController::class, 'zasilkovna'])->name('labels.zasilkovna');

    // 💳 Bankovní platby
    Route::post('/bank-payments/check', [BankPaymentController::class, 'check'])->name('bank-payments.check');
    Route::get('/bank-payments', [BankPaymentController::class, 'index'])->name('bank-payments.index');

    // 📑 Faktury
    // Route::get('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    // Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::post('/invoices/{invoice}/paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.paid');
    Route::resource('invoices', InvoiceController::class);

    // 👥 Zákazníci
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{email}', [CustomerController::class, 'show'])->name('customers.show');

    // 🔐 Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ℹ️ phpinfo (jen pro admina, pokud nechceš veřejně)
Route::middleware(['auth'])->get('/phpinfo', fn () => phpinfo());
    // 👤 Získání detailu objednávky (JSON pro faktury)

require __DIR__.'/auth.php';
