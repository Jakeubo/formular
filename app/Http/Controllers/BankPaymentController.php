<?php

namespace App\Http\Controllers;

use App\Models\BankPayment;
use App\Services\BankMailProcessor;

class BankPaymentController extends Controller
{
    public function index()
    {
       $payments = BankPayment::orderByDesc('received_at')->paginate(20);

        return view('bank_payments.index', compact('payments'));
    }
    

public function check()
{
    $processor = new BankMailProcessor();
    $count = $processor->process();

    return redirect()->route('bank-payments.index')
        ->with('success', "Zpracováno $count nových plateb.");
}

}
