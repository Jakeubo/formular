<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>Faktura {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            line-height: 1.5;
        }

        h1,
        h2,
        h3 {
            margin: 0;
            padding: 0;
        }

        /* HLAVIČKA */
        .header {
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 15px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            page-break-inside: avoid;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
        }

        .invoice-number {
            font-size: 26px;
            font-weight: bold;
            color: #4f46e5;
        }

        /* SEKCÍ */
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        /* DODAVATEL / ODBĚRATEL */
        .parties {
            display: table;
            width: 100%;
            border-spacing: 20px 0;
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .party {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            border: 1px solid #e5e7eb;
            padding: 14px 18px;
            border-radius: 6px;
            background: #f9fafb;
        }

        .party h3 {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 6px;
            color: #374151;
        }

        /* TABULKA POLOŽEK */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: auto;
        }

        thead {
            display: table-header-group;
            /* hlavička se opakuje při zalomení */
        }

        tfoot {
            display: table-row-group;
        }

        tbody tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 10px;
        }

        th {
            background: #f3f4f6;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            color: #111827;
        }

        td {
            font-size: 12px;
        }

        tfoot td {
            font-weight: bold;
            font-size: 13px;
        }

        .total-row {
            background: #eef2ff;
        }

        /* PLATEBNÍ INFO + QR */
        .payment-info {
            display: table;
            width: 100%;
            margin-top: 25px;
            border-spacing: 20px 0;
            page-break-inside: avoid;
        }

        .payment-details,
        .payment-qr {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .payment-details {
            font-size: 13px;
            line-height: 1.8;
        }

        .payment-details strong {
            display: inline-block;
            width: 160px;
            color: #111827;
        }

        .amount {
            margin-top: 12px;
            font-size: 16px;
            font-weight: bold;
            color: #4f46e5;
        }

        .payment-qr {
            text-align: right;
        }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <!-- HLAVIČKA -->
    <div class="header">
        <div class="company-name">ZapichniTo3D</div>
        <div class="invoice-number">Faktura č. {{ $invoice->invoice_number }}</div>
    </div>

    <!-- DODAVATEL / ODBĚRATEL -->
    <div class="section parties">
        <div class="party">
            <h3>Dodavatel</h3>
            <p>
                ZapichniTo3D<br>
                Jakub Vašička<br>
                Žižkova 1031<br>
                78353 Velká Bystřice<br>
                Česká republika<br>
                IČO: 17343704<br>
                IBAN: CZ2408000000004396484053<br>
                SWIFT/BIC: GIBACZPX
            </p>
        </div>
        <div class="party">
            <h3>Odběratel</h3>
            @if($invoice->order)
            <p>
                {{ $invoice->order->first_name }} {{ $invoice->order->last_name }}<br>
                {{ $invoice->order->address }}<br>
                {{ $invoice->order->zip }} {{ $invoice->order->city }}<br>
                {{ $invoice->order->country ?? 'ČR' }}<br>
                {{ $invoice->order->email }}<br>
                @if(!empty($invoice->order))
                @if(!empty($invoice->order->company_ico))
                <strong>IČO:</strong> {{ $invoice->order->company_ico }}<br>
                @endif

                @if(!empty($invoice->order->company_dic))
                <strong>DIČ:</strong> {{ $invoice->order->company_dic }}<br>
                @endif
                @endif


            </p>
            @else
            <p>-</p>
            @endif
        </div>
    </div>

    <!-- TABULKA POLOŽEK -->
    <div class="section">
        <h3>Položky faktury</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Kusů</th>
                    <th style="width: 50%;">Popis</th>
                    <th style="width: 20%;">Cena/ks</th>
                    <th style="width: 20%;">Celkem</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ number_format($item->unit_price, 2, ',', ' ') }} Kč</td>
                    <td>{{ number_format($item->total, 2, ',', ' ') }} Kč</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">Celková cena</td>
                    <td>{{ number_format($invoice->total_price, 2, ',', ' ') }} Kč</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- PLATEBNÍ ÚDAJE + QR -->
    <div class="payment-info">
        <div class="payment-details">
            <p><strong>Datum vystavení:</strong> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}</p>
            <p><strong>Datum splatnosti:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') }}</p>
            <p><strong>Číslo účtu:</strong> 4396484053/0800</p>
            <p><strong>Variabilní symbol:</strong> {{ $invoice->variable_symbol }}</p>
            <p class="amount">Částka k úhradě: {{ number_format($invoice->total_price, 2, ',', ' ') }} Kč</p>
        </div>
        <div class="payment-qr">
            <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Platba" width="170" height="170">
        </div>
    </div>


    <!-- PATIČKA -->
    <div class="footer">
        Děkujeme za Vaši objednávku!<br>
        ZapichniTo3D – www.zapichnito3d.cz | info@zapichnito3d.cz
    </div>

</body>

</html>