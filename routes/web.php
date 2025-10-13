<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\BankPaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\DashboardController;
use App\Models\Order;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\URL;
use App\Models\ShippingMethod;

// 🏠 Domovská stránka – veřejný formulář pro objednávky
Route::get('/', function () {
    $shippingMethods = ShippingMethod::all();
    return view('order-form', compact('shippingMethods'));
})->name('form');

// 🧾 Veřejné stažení faktury přes token
Route::get('/invoices/download/{token}', [InvoiceController::class, 'download'])
    ->name('invoices.download');

// 🧾 Odeslání objednávky (z formuláře)
Route::post('/order', [OrderController::class, 'store'])->name('order.store');

// 📦 Výdejní štítky (čekající, balíkovna, zasilkovna, ppl, pplparcel)
Route::get('/labels/wait_label/{token}', [LabelController::class, 'waitLabel'])->name('labels.wait_label');
Route::get('/labels/pplparcel/{token}', [LabelController::class, 'pplParcelshop'])->name('labels.pplparcel');
Route::get('/labels/ppl/{token}', [LabelController::class, 'ppl'])->name('labels.ppl');
Route::get('/labels/zasilkovna/{token}', [LabelController::class, 'zasilkovna'])->name('labels.zasilkovna');
Route::get('/labels/balikovna/{token}', [LabelController::class, 'balikovna'])->name('labels.balikovna');

// 🔍 Detail objednávky (pro JS ve fakturačním formuláři)
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

// 📊 Admin dashboard – chráněný přístup
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/shipping', [SettingController::class, 'updateShipping'])->name('settings.shipping.update');

    // 💳 Bankovní platby
    Route::post('/bank-payments/check', [BankPaymentController::class, 'check'])->name('bank-payments.check');
    Route::get('/bank-payments', [BankPaymentController::class, 'index'])->name('bank-payments.index');

    // 📑 Faktury (admin)
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('/invoices/{invoice}/paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.paid');
    Route::resource('invoices', InvoiceController::class)->except(['download']);

    // 👥 Zákazníci
    Route::resource('customers', CustomerController::class);

    //změna statusu faktury
    Route::patch('/invoices/{invoice}/update-status', [InvoiceController::class, 'updateStatus'])
        ->name('invoices.updateStatus');

    Route::get('/label/ppl/{token}', [LabelController::class, 'ppl'])->name('labels.ppl');
    Route::get('/label/zasilkovna/{token}', [LabelController::class, 'zasilkovna'])->name('labels.zasilkovna');
    Route::get('/label/balikovna/{token}', [LabelController::class, 'balikovna'])->name('labels.balikovna');
    Route::get('/label/ppl-parcelshop/{token}', [\App\Http\Controllers\LabelController::class, 'pplParcelshop'])
        ->name('labels.pplParcelshop');

    Route::get('/label/wait/{token}', [LabelController::class, 'waitLabel'])->name('labels.wait');
    // 🔐 Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// ℹ️ phpinfo (jen pro admina, pokud nechceš veřejně)
Route::middleware(['auth'])->get('/phpinfo', fn() => phpinfo());
// 👤 Získání detailu objednávky (JSON pro faktury)

require __DIR__ . '/auth.php';
