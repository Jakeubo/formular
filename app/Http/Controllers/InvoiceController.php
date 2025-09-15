<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('created_at', 'desc')->get();
        $invoices = Invoice::with('order')->paginate(10);

        return view('invoices.index', compact('invoices', 'orders'));
    }

    public function create()
    {
        $orders = Order::all();
        return view('invoices.create', compact('orders'));
    }

    public function store(Request $request)
    {
        $invoice = Invoice::create([
            'order_id'       => $request->order_id,
            'invoice_number' => $request->invoice_number,
            'issue_date'     => $request->issue_date,
            'due_date'       => $request->due_date,
            'status'         => $request->status,
            'total_price'    => collect($request->items)->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            }),
        ]);

        foreach ($request->items as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'vat_rate'    => 21.00,
                'total'       => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('invoices.index')->with('success', 'Faktura byla vytvořena.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('items', 'order');
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items');
        $orders = Order::all();
        return view('invoices.edit', compact('invoice', 'orders'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $invoice->update([
            'order_id' => $request->order_id,
        ]);

        $invoice->items()->delete();
        $total = 0;

        foreach ($request->items as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'vat_rate'    => 21.00,
                'total'       => $itemTotal,
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
