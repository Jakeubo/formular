<?php

namespace App\Http\Controllers;

use App\Models\BankPayment;

class BankPaymentController extends Controller
{
    public function index()
    {
        $payments = BankPayment::orderByDesc('created_at')->paginate(20);

        return view('bank_payments.index', compact('payments'));
    }
}
