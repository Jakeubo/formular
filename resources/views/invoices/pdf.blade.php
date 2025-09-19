<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f5f5f5; }
        .text-right { text-align: right; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <h1>Faktura {{ $invoice->invoice_number }}</h1>

    <p><strong>Datum vystavení:</strong> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}</p>
    <p><strong>Datum splatnosti:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') }}</p>

    <h3>Odběratel</h3>
    <p>
        {{ $invoice->order->first_name }} {{ $invoice->order->last_name }}<br>
        {{ $invoice->order->address }}<br>
        {{ $invoice->order->zip }} {{ $invoice->order->city }}<br>
        {{ $invoice->order->email }}
    </p>

    <h3>Položky</h3>
    <table>
        <thead>
            <tr>
                <th>Množství</th>
                <th>Popis</th>
                <th>Cena/ks</th>
                <th>Celkem</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->description }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2, ',', ' ') }} Kč</td>
                <td class="text-right">{{ number_format($item->total, 2, ',', ' ') }} Kč</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="text-right total">Celkem: {{ number_format($invoice->total_price, 2, ',', ' ') }} Kč</p>
</body>
</html>
