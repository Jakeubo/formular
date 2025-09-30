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



class InvoiceController extends Controller
{


public function send(Invoice $invoice)
{
    $invoice->load('items', 'order');

    // Připrav QR kód
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

    // Generuj PDF (zatím jen do proměnné, neposíláme jako přílohu)
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', compact('invoice', 'qrCode'));

    try {
        // 👉 vytvoř dočasný podepsaný link platný 7 dní
        $downloadUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'invoices.download',
            now()->addDays(7),
            ['id' => $invoice->id]
        );

        // Odešli e-mail s odkazem na stažení
        \Illuminate\Support\Facades\Mail::send('emails.invoice', [
            'invoice'     => $invoice,
            'downloadUrl' => $downloadUrl,
        ], function ($message) use ($invoice) {
            $message->to($invoice->order->email)
                ->subject("Faktura {$invoice->invoice_number}");
        });

        // ✅ po úspěšném odeslání změnit status
        $invoice->status = 'sent';
        $invoice->save();

        return back()->with('success', "✅ Faktura {$invoice->invoice_number} byla úspěšně odeslána.");
    } catch (\Exception $e) {
        return back()->with('error', "❌ Odeslání selhalo: " . $e->getMessage());
    }
}




    public function download($id)
    {
        $invoice = Invoice::with('items', 'order')->findOrFail($id);

        // Tvůj reálný účet v IBAN formátu
        $iban = 'CZ2408000000004396484053';

        // Částka
        $amount = number_format($invoice->total_price, 2, '.', '');

        // Variabilní symbol – jen čísla a max. 10 znaků
        $vs = preg_replace('/\D/', '', (string) $invoice->variable_symbol);
        $vs = substr($vs, 0, 10);

        // zpráva
        $msg = iconv('UTF-8', 'ASCII//TRANSLIT', "Zapichnito3d ");

        // QR řetězec
        $qrString = "SPD*1.0*ACC:$iban*AM:$amount*CC:CZK*X-VS:$vs*MSG:$msg";


        Log::info('QR STRING: ' . $qrString);

        // QR kód
        $qrCode = base64_encode(
            QrCode::format('svg')->size(300)->errorCorrection('M')->generate($qrString)
        );

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'qrCode'));

        return $pdf->download("faktura_{$invoice->invoice_number}.pdf");
    }


    public function index(\Illuminate\Http\Request $request)
    {
        $query = Invoice::with('order')->orderBy('created_at', 'desc');

        // vyhledávání podle zákazníka nebo emailu
        if ($search = $request->input('search')) {
            $query->whereHas('order', function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        $invoices = $query->paginate(10);

        // seznam objednávek pro select v modalu
        $orders = \App\Models\Order::orderBy('created_at', 'desc')->get();

        return view('invoices.index', compact('invoices', 'orders'));
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
        $orders = Order::all();
        return view('invoices.create', compact('orders'));
    }

    public function store(Request $request)
    {
        $order = \App\Models\Order::findOrFail($request->order_id);

        // vygenerujeme variabilní symbol
        $today = now()->format('Ymd');
        $countToday = Invoice::whereDate('created_at', now()->toDateString())->count() + 1;
        $variableSymbol = $today . str_pad($countToday, 2, '0', STR_PAD_LEFT);

        $invoice = Invoice::create([
            'order_id'        => $order->id,
            'customer_id'     => $order->customer_id,
            'invoice_number'  => 'FA ' . $variableSymbol,
            'variable_symbol' => $variableSymbol,
            'issue_date'      => $request->issue_date,
            'due_date'        => $request->due_date,
            'status'          => 'new', // vždy defaultně Nová
            'total_price'     => collect($request->items)->sum(
                fn($item) => $item['quantity'] * $item['unit_price']
            ),
            // doplněno:
            'carrier'         => $order->carrier ?? null,
            'carrier_address' => $order->carrier_address ?? null,
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
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'issue_date' => 'required|date',
            'status' => 'required|in:new,draft,sent,paid',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // update hlavičky faktury
        $invoice->update([
            'order_id'   => $request->order_id,
            'issue_date' => $request->issue_date,
            'status'     => $request->status,
        ]);

        // smazat původní položky
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

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Faktura byla upravena');
    }


    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Faktura byla smazána');
    }
}
