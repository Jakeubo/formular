{{-- resources/views/invoices/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Faktury
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Tlačítko otevření modalu -->
                    <button onclick="openInvoiceModal()"
                        class="bg-pink-500 text-white px-4 py-2 rounded shadow hover:bg-pink-600">
                        ➕ Nová faktura
                    </button>

                    <table class="w-full mt-6 border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Číslo</th>
                                <th class="px-4 py-2 text-left">Zákazník</th>
                                <th class="px-4 py-2 text-left">Cena</th>
                                <th class="px-4 py-2 text-left">Stav</th>
                                <th class="px-4 py-2 text-left">Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $invoice)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $invoice->invoice_number }}</td>
                                <td class="px-4 py-2">
                                    @if($invoice->order)
                                    {{ $invoice->order->first_name }} {{ $invoice->order->last_name }}
                                    @else
                                    <em>-</em>
                                    @endif
                                </td>
                                <td class="px-4 py-2">{{ number_format($invoice->total_price, 2) }} Kč</td>
                                <td class="px-4 py-2">{{ $invoice->status }}</td>
                                <td class="px-4 py-2 space-x-2">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600">👁 Zobrazit</a>
                                    <a href="{{ route('invoices.edit', $invoice) }}" class="text-yellow-600">✏ Upravit</a>
                                    <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600" onclick="return confirm('Opravdu smazat?')">🗑 Smazat</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $invoices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="invoiceModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-6 relative">

            <!-- Zavírací tlačítko -->
            <button onclick="closeInvoiceModal()"
                class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">
                ✕
            </button>

            <h2 class="text-xl font-bold mb-4">Nová faktura</h2>

            <!-- Formulář pro vytvoření faktury -->
            @include('invoices.partials.create-form')
        </div>
    </div>

    <script>
        function openInvoiceModal() {
            document.getElementById('invoiceModal').classList.remove('hidden');
        }

        function closeInvoiceModal() {
            document.getElementById('invoiceModal').classList.add('hidden');
        }
    </script>
</x-app-layout>