{{-- resources/views/invoices/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Detail faktury {{ $invoice->invoice_number }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">
                <!-- Info o zákazníkovi -->
                <h3 class="text-lg font-bold mb-2">Zákazník</h3>
                @if($invoice->order)
                    <p><strong>Jméno:</strong> {{ $invoice->order->first_name }} {{ $invoice->order->last_name }}</p>
                    <p><strong>Email:</strong> {{ $invoice->order->email }}</p>
                    <p><strong>Telefon:</strong> {{ $invoice->order->phone }}</p>
                    <p><strong>Adresa:</strong> {{ $invoice->order->address }}, {{ $invoice->order->city }} {{ $invoice->order->zip }}</p>
                    <p><strong>Dopravce:</strong> {{ $invoice->order->carrier }}</p>
                    <p><strong>Výdejní místo:</strong> {{ $invoice->order->carrier_address }}</p>
                @else
                    <em>Zákazník není přiřazen</em>
                @endif

                <hr class="my-4">

                <!-- Info o faktuře -->
                <h3 class="text-lg font-bold mb-2">Faktura</h3>
                <p><strong>Číslo faktury:</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>Datum vystavení:</strong> {{ $invoice->issue_date }}</p>
                <p><strong>Datum splatnosti:</strong> {{ $invoice->due_date }}</p>
                <p><strong>Stav:</strong> {{ $invoice->status }}</p>
                <p><strong>Celková cena:</strong> {{ number_format($invoice->total_price, 2) }} Kč</p>

                <hr class="my-4">

                <!-- Položky -->
                <h3 class="text-lg font-bold mb-2">Položky</h3>
                <table class="w-full border">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-2 py-1 border">Popis</th>
                            <th class="px-2 py-1 border">Množství</th>
                            <th class="px-2 py-1 border">Cena/ks</th>
                            <th class="px-2 py-1 border">Celkem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td class="border px-2 py-1">{{ $item->description }}</td>
                            <td class="border px-2 py-1">{{ $item->quantity }}</td>
                            <td class="border px-2 py-1">{{ number_format($item->unit_price, 2) }} Kč</td>
                            <td class="border px-2 py-1">{{ number_format($item->total, 2) }} Kč</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">
                    <a href="{{ route('invoices.index') }}" class="text-blue-600">⬅ Zpět na seznam</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
