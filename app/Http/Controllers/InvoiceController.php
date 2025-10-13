<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;


class InvoiceController extends Controller
{




    public function send(Request $request, Invoice $invoice)
    {
        $invoice->load('items', 'order');

        // Validace vstupů (volitelně)
        $request->validate([
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        // QR kód
        $iban = 'CZ2408000000004396484053';
        $amount = number_format($invoice->total_price, 2, '.', '');
        $vs = $invoice->variable_symbol;
        $msg = iconv('UTF-8', 'ASCII//TRANSLIT', 'Faktura ' . $invoice->invoice_number);

        $qrString = "SPD*1.0*ACC:$iban*AM:$amount*CC:CZK*X-VS:$vs*MSG:$msg";
        $qrCode = base64_encode(
            \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(300)
                ->errorCorrection('M')
                ->generate($qrString)
        );

        $downloadUrl = route('invoices.download', ['token' => $invoice->download_token]);




        // Použijeme vlastní subject a body z modalu
        Mail::send('emails.invoice', [
            'invoice'     => $invoice,
            'downloadUrl' => $downloadUrl,
            'body'        => $request->body,   // to si můžeš přidat do view
        ], function ($message) use ($invoice, $request) {
            $message->to($invoice->order->email)
                ->subject($request->subject);
        });

        $invoice->update(['status' => 'sent']);

        return back()->with('success', "✅ Faktura {$invoice->invoice_number} byla úspěšně odeslána.");
    }


    public function download(string $token)
    {
        // 🔒 Najdeme fakturu podle jejího tokenu, ne podle objednávky
        $invoice = Invoice::where('download_token', $token)
            ->with(['items', 'order'])
            ->firstOrFail();

        $iban = 'CZ2408000000004396484053';
        $amount = number_format($invoice->total_price, 2, '.', '');
        $vs = substr(preg_replace('/\D/', '', (string) $invoice->variable_symbol), 0, 10);
        $msg = iconv('UTF-8', 'ASCII//TRANSLIT', "Zapichnito3D ");
        $qrString = "SPD*1.0*ACC:$iban*AM:$amount*CC:CZK*X-VS:$vs*MSG:$msg";

        $qrCode = base64_encode(
            \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(300)
                ->errorCorrection('M')
                ->generate($qrString)
        );

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', compact('invoice', 'qrCode'))
            ->download("faktura_{$invoice->invoice_number}.pdf");
    }




    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Invoice::with(['order', 'items'])
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('variable_symbol', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($sub) use ($search) {
                        $sub->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items', function ($sub) use ($search) {
                        $sub->where('description', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->paginate(10);
        $orders = Order::orderBy('created_at', 'desc')->get();
        $shippingMethods = \App\Models\ShippingMethod::all()->keyBy('code');

        return view('invoices.index', compact('invoices', 'orders', 'shippingMethods'));
    }




    public function markAsPaid(Invoice $invoice)
    {
        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return redirect()->route('invoices.index')->with('success', 'Faktura označena jako zaplacená.');
    }



    public function create()
    {
        $orders = \App\Models\Order::all();
        $shippingMethods = \App\Models\ShippingMethod::all()->keyBy('code');

        return view('invoices.create', compact('orders', 'shippingMethods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'company_ico' => 'nullable|string|max:12',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);


        $order = Order::findOrFail($validated['order_id']);

        // vygenerujeme variabilní symbol
        $today = now()->format('Ymd');
        $countToday = Invoice::whereDate('created_at', now()->toDateString())->count() + 1;
        $variableSymbol = $today . str_pad($countToday, 2, '0', STR_PAD_LEFT);

        $invoice = Invoice::create([
            'order_id'        => $order->id,
            'customer_id'     => $order->customer_id,
            'invoice_number'  => 'FA ' . $variableSymbol,
            'variable_symbol' => $variableSymbol,
            'issue_date'      => $validated['issue_date'],
            'due_date'        => $validated['due_date'],
            'company_ico'     => $validated['company_ico'] ?? null, // ✅ nové pole
            'status'          => 'new',
            'total_price'     => collect($validated['items'])->sum(
                fn($item) => $item['quantity'] * $item['unit_price']
            ),
            'carrier'         => $order->carrier ?? null,
            'carrier_address' => $order->carrier_address ?? null,
            'download_token' => Str::random(40),
        ]);

        foreach ($validated['items'] as $item) {
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
        // Načteme i související data
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
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'issue_date' => 'required|date',
            'status' => 'required|in:new,draft,sent,paid,shipped',
            'company_ico' => 'nullable|string|max:12',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $invoice->update([
            'order_id'     => $validated['order_id'],
            'issue_date'   => $validated['issue_date'],
            'status'       => $validated['status'],
            'company_ico'  => $validated['company_ico'] ?? null, // ✅ nové pole
        ]);

        // smažeme původní položky
        $invoice->items()->delete();

        $total = 0;
        foreach ($validated['items'] as $item) {
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

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Faktura byla upravena.');
    }



    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Faktura byla smazána');
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $newStatus = $request->input('status');

        // Kontrola logiky: zásilku lze odeslat jen po zaplacení
        if ($newStatus === 'shipped' && $invoice->status !== 'paid') {
            return back()->with('error', 'Zásilku lze označit jako odeslanou pouze po zaplacení faktury.');
        }

        $invoice->update(['status' => $newStatus]);

        return back()->with('success', "✅ Status faktury {$invoice->invoice_number} byl změněn na {$newStatus}.");
    }
}
