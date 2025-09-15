<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('customer')->latest()->paginate(10);
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::all();
        return view('invoices.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'items.*.description' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Vytvoří fakturu
        $invoice = Invoice::create([
            'invoice_number' => 'INV-' . time(),
            'customer_id' => $request->customer_id,
            'status' => 'new',
            'total_price' => 0,
            'payment_status' => 'unpaid',
            'due_date' => now()->addDays(14)
        ]);

        $total = 0;

        foreach ($request->items as $item) {
            $itemTotal = $item['qty'] * $item['unit_price'];
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'total' => $itemTotal
            ]);
            $total += $itemTotal;
        }

        $invoice->update(['total_price' => $total]);

        return redirect()->route('invoices.index')->with('success', 'Faktura byla vytvořena');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('items', 'customer');
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items');
        $customers = Customer::all();
        return view('invoices.edit', compact('invoice', 'customers'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'customer_id' => 'required',
            'items.*.description' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $invoice->update([
            'customer_id' => $request->customer_id,
        ]);

        $invoice->items()->delete();
        $total = 0;

        foreach ($request->items as $item) {
            $itemTotal = $item['qty'] * $item['unit_price'];
            $invoice->items()->create([
                'description' => $item['description'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'total' => $itemTotal
            ]);
            $total += $itemTotal;
        }

        $invoice->update(['total_price' => $total]);

        return redirect()->route('invoices.index')->with('success', 'Faktura byla upravena');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Faktura byla smazána');
    }
}
