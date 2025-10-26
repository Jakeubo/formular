<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <a href="{{ route('invoices.show', $invoice) }}"
                class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-xl shadow hover:bg-gray-900 transition">
                ← Zpět na detail
            </a>
            <h2 class="font-semibold text-2xl text-gray-900">
                Upravit fakturu
            </h2>
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <form action="{{ route('invoices.update', $invoice) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Horní karty -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                    <!-- Levý blok -->
                    <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Zákazník</h3>
                        <select name="order_id" class="w-full rounded-md border-gray-300 shadow-sm">
                            @foreach($orders as $order)
                            <option value="{{ $order->id }}" @selected($order->id == $invoice->order_id)>
                                {{ $order->first_name }} {{ $order->last_name }} – {{ $order->email }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pravý blok -->
                    <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Status -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Status</h3>
                                <select name="status" class="w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="new" @selected($invoice->status === 'new')>Nová</option>
                                    <option value="draft" @selected($invoice->status === 'draft')>Koncept</option>
                                    <option value="sent" @selected($invoice->status === 'sent')>Odeslaná</option>
                                    <option value="paid" @selected($invoice->status === 'paid')>Zaplacená</option>
                                    <option value="overdue" @selected($invoice->status === 'overdue')>Po splatnosti</option>
                                </select>
                            </div>

                            <!-- Termíny -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Termíny</h3>
                                <div class="mb-2">
                                    <label class="block text-sm font-medium text-gray-700">Datum vystavení</label>
                                    <input type="date" name="issue_date"
                                        value="{{ old('issue_date', \Carbon\Carbon::parse($invoice->issue_date)->format('Y-m-d')) }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Datum splatnosti</label>
                                    <input type="date" name="due_date"
                                        value="{{ old('due_date', \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d')) }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Položky faktury -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
                    <h3 class="text-xl font-semibold text-gray-900 mb-6">💼 Položky faktury</h3>

                    <div class="overflow-hidden rounded-lg border border-gray-200 shadow-sm">
                        <table id="items-table" class="w-full text-sm">
                            <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                                <tr>
                                    <th class="px-3 py-3 text-center font-semibold w-20">Kusy</th>
                                    <th class="px-4 py-3 text-left font-semibold">Popis</th>
                                    <th class="px-3 py-3 text-right font-semibold w-32">Cena/ks</th>
                                    <th class="px-3 py-3 text-right font-semibold w-32">Celkem</th>
                                    <th class="px-3 py-3 w-12"></th>
                                </tr>
                            </thead>
                            <tbody id="invoice-body" class="divide-y divide-gray-100">
                                @foreach($invoice->items as $i => $item)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="text-center py-3">
                                        <input type="number" name="items[{{ $i }}][quantity]" value="{{ $item->quantity }}"
                                            class="item-quantity w-16 border-gray-300 rounded-md text-center focus:ring-indigo-500 focus:border-indigo-500">
                                    </td>
                                    <td class="py-3">
                                        <input type="text" name="items[{{ $i }}][description]" value="{{ $item->description }}"
                                            class="w-full border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                    </td>
                                    <td class="py-3 text-right">
                                        <input type="number" step="0.01" name="items[{{ $i }}][unit_price]" value="{{ $item->unit_price }}"
                                            class="item-price w-28 border-gray-300 rounded-md text-right focus:ring-indigo-500 focus:border-indigo-500">
                                    </td>
                                    <td class="py-3 text-right text-gray-800 font-semibold item-total">
                                        {{ number_format($item->quantity * $item->unit_price, 2) }} Kč
                                    </td>
                                    <td class="text-center">
                                        <button type="button" onclick="removeRow(this)"
                                            class="text-red-500 hover:text-red-700 transition">✕</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Součet + přidat položku -->
                    <div class="flex flex-col sm:flex-row justify-between items-center mt-6 border-t border-gray-200 pt-4">
                        <button type="button" onclick="addItemRow()"
                            class="order-2 sm:order-1 mt-4 sm:mt-0 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg shadow-sm hover:bg-indigo-700 transition">
                            <span class="text-lg">＋</span> Přidat položku
                        </button>

                        <div class="order-1 sm:order-2 text-2xl font-bold text-gray-900">
                            Celkem: <span id="invoice-total" class="text-indigo-600">{{ number_format($invoice->total_price, 2) }}</span> Kč
                        </div>
                    </div>
                </div>

                <!-- Uložit -->
                <div class="flex justify-end mt-8">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white text-lg font-semibold rounded-xl shadow-md hover:bg-green-700 transition">
                        💾 Uložit změny
                    </button>
                </div>

            </form>
        </div>
    </div>
    <div id="invoice-data" data-count="{{ count($invoice->items) }}"></div>
    <script>
        let itemIndex = parseInt(document.getElementById('invoice-data').dataset.count);

        function addItemRow() {
            const table = document.querySelector('#items-table tbody');
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50 transition';
            row.innerHTML = `
        <td class="text-center py-3">
            <input type="number" name="items[${itemIndex}][quantity]" value="1"
                class="item-quantity w-16 border-gray-300 rounded-md text-center focus:ring-indigo-500 focus:border-indigo-500">
        </td>
        <td class="py-3">
            <input type="text" name="items[${itemIndex}][description]" value=""
                class="w-full border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
        </td>
        <td class="py-3 text-right">
            <input type="number" step="0.01" name="items[${itemIndex}][unit_price]" value="0"
                class="item-price w-28 border-gray-300 rounded-md text-right focus:ring-indigo-500 focus:border-indigo-500">
        </td>
        <td class="py-3 text-right text-gray-800 font-semibold item-total">0.00 Kč</td>
        <td class="text-center">
            <button type="button" onclick="removeRow(this)"
                class="text-red-500 hover:text-red-700 transition">✕</button>
        </td>
    `;
            table.appendChild(row);
            itemIndex++;
            attachListeners(row);
            recalcTotal();
        }

        function removeRow(button) {
            button.closest('tr').remove();
            recalcTotal();
        }

        function attachListeners(row) {
            const qtyInput = row.querySelector('.item-quantity');
            const priceInput = row.querySelector('.item-price');
            qtyInput.addEventListener('input', recalcTotal);
            priceInput.addEventListener('input', recalcTotal);
        }

        function recalcTotal() {
            let total = 0;
            document.querySelectorAll('#items-table tbody tr').forEach(tr => {
                const qty = parseFloat(tr.querySelector('.item-quantity')?.value || 0);
                const price = parseFloat(tr.querySelector('.item-price')?.value || 0);
                const subtotal = qty * price;
                tr.querySelector('.item-total').textContent = subtotal.toFixed(2) + ' Kč';
                total += subtotal;
            });
            document.getElementById('invoice-total').textContent = total.toFixed(2);
        }

        // při načtení připojíme eventy
        document.querySelectorAll('#items-table tbody tr').forEach(attachListeners);
    </script>
</x-app-layout>