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
                <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Položky</h3>
                    <table class="w-full text-sm border-collapse" id="items-table">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700">
                                <th class="px-2 py-2 text-center font-medium w-20">Kusy</th>
                                <th class="px-3 py-2 text-left font-medium">Název položky</th>
                                <th class="px-2 py-2 text-right font-medium w-32">Cena/ks</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($invoice->items as $i => $item)
                            <tr>
                                <td><input type="number" name="items[{{ $i }}][quantity]" value="{{ $item->quantity }}" class="w-16 border-gray-300 rounded-md"></td>
                                <td><input type="text" name="items[{{ $i }}][description]" value="{{ $item->description }}" class="w-full border-gray-300 rounded-md"></td>
                                <td><input type="number" step="0.01" name="items[{{ $i }}][unit_price]" value="{{ $item->unit_price }}" class="w-28 border-gray-300 rounded-md text-right"></td>
                                <td><button type="button" onclick="this.closest('tr').remove()" class="text-red-600">✖</button></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <button type="button" onclick="addItemRow()" class="mt-3 px-3 py-2 bg-indigo-600 text-white rounded-md shadow hover:bg-indigo-700">+ Přidat položku</button>
                </div>

                <!-- Uložit -->
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-xl shadow hover:bg-green-700">
                        💾 Uložit změny
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let itemIndex = document.getElementById('items-table').dataset.count;

        function addItemRow() {
            const table = document.querySelector('#items-table tbody');
            const row = document.createElement('tr');
            row.innerHTML = `
            <td><input type="number" name="items[${itemIndex}][quantity]" value="1" class="w-16 border-gray-300 rounded-md"></td>
            <td><input type="text" name="items[${itemIndex}][description]" value="" class="w-full border-gray-300 rounded-md"></td>
            <td><input type="number" step="0.01" name="items[${itemIndex}][unit_price]" value="0" class="w-28 border-gray-300 rounded-md text-right"></td>
            <td><button type="button" onclick="this.closest('tr').remove()" class="text-red-600">✖</button></td>
        `;
            table.appendChild(row);
            itemIndex++;
        }
    </script>

</x-app-layout>