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
                        class="bg-blue-500 text-white px-4 py-2 rounded shadow hover:bg-blue-600 transition">
                        ➕ Nová faktura
                    </button>

                    <table class="w-full mt-6 border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Číslo</th>
                                <th class="px-4 py-2 text-left">Zákazník</th>
                                <th class="px-4 py-2 text-left">Cena</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Splatnost / Platba</th>
                                <th class="px-4 py-2 text-left">Akce</th> <!-- ✅ nový sloupec -->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $invoice)
                            <tr class="border-t">
                                <td class="px-4 py-2 font-bold">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:underline">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-2">
                                    @if($invoice->order)
                                    {{ $invoice->order->first_name }} {{ $invoice->order->last_name }}
                                    @else
                                    <em>-</em>
                                    @endif
                                </td>
                                <td class="px-4 py-2">{{ number_format($invoice->total_price, 2) }} Kč</td>

                                {{-- Status --}}
                                <td class="px-4 py-2">
                                    @switch($invoice->status)
                                    @case('new')
                                    <span class="px-2 py-1 rounded bg-gray-100 text-gray-700 text-sm font-medium">🆕 Nová</span>
                                    @break
                                    @case('sent')
                                    <span class="px-2 py-1 rounded bg-blue-100 text-blue-700 text-sm font-medium">📤 Odeslaná</span>
                                    @break
                                    @case('paid')
                                    <span class="px-2 py-1 rounded bg-green-100 text-green-700 text-sm font-medium">✅ Zaplacená</span>
                                    @break
                                    @case('overdue')
                                    <span class="px-2 py-1 rounded bg-red-100 text-red-700 text-sm font-medium">⏰ Po splatnosti</span>
                                    @break
                                    @default
                                    <span class="px-2 py-1 rounded bg-gray-200 text-gray-800 text-sm font-medium">{{ $invoice->status }}</span>
                                    @endswitch
                                </td>

                                {{-- Splatnost / Platba --}}
                                <td class="px-4 py-2">
                                    @if($invoice->paid_at)
                                    ✅ {{ \Carbon\Carbon::parse($invoice->paid_at)->format('d.m.Y') }}
                                    @else
                                    @php
                                    $due = \Carbon\Carbon::parse($invoice->due_date)->startOfDay();
                                    $today = now()->startOfDay();
                                    $diff = $today->diffInDays($due, false);
                                    @endphp

                                    @if($diff < 0)
                                        <span class="text-red-600">⏰ {{ abs($diff) }} dnů po splatnosti</span>
                                        @else
                                        <span class="text-gray-700">⏳ {{ $diff }} dnů do splatnosti</span>
                                        @endif
                                        @endif
                                </td>

                                {{-- Akce --}}
                                <td class="px-4 py-2 text-center relative dropdown-wrapper">
                                    <button onclick="toggleDropdown('{{ $invoice->id }}')"
                                        class="px-2 py-1 rounded hover:bg-gray-100">⋮</button>


                                    <!-- Dropdown -->
                                    <div id="dropdown-{{ $invoice->id }}"
                                        class="hidden absolute right-0 mt-2 w-40 bg-white border rounded shadow-md z-10">
                                        <a href="{{ route('invoices.send', $invoice) }}"
                                            class="block px-4 py-2 text-sm hover:bg-gray-100">✉️ Odeslat fakturu</a>
                                        <a href="{{ route('invoices.download', $invoice) }}"
                                            class="block px-4 py-2 text-sm hover:bg-gray-100">⬇️ Stáhnout fakturu</a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <script>
                        function toggleDropdown(id) {
                            document.querySelectorAll("[id^='dropdown-']").forEach(el => {
                                if (el.id === "dropdown-" + id) {
                                    el.classList.toggle("hidden");
                                } else {
                                    el.classList.add("hidden");
                                }
                            });
                        }

                        document.addEventListener("click", function(e) {
                            if (!e.target.closest("td")) {
                                document.querySelectorAll("[id^='dropdown-']").forEach(el => el.classList.add("hidden"));
                            }
                        });
                    </script>

                    <div class="mt-4">
                        {{ $invoices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="invoiceModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-6 relative overflow-y-auto max-h-[90vh]"><button onclick="closeInvoiceModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">
                ✕
            </button>
            <h2 class="text-xl font-bold mb-4">Nová faktura</h2>
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
    <script>
        function toggleDropdown(id) {
            document.querySelectorAll("[id^='dropdown-']").forEach(el => {
                if (el.id === "dropdown-" + id) {
                    el.classList.toggle("hidden");
                } else {
                    el.classList.add("hidden");
                }
            });
        }


        // Klik mimo .dropdown-wrapper zavře všechny dropdowny
        document.addEventListener("click", function(e) {
            if (!e.target.closest(".dropdown-wrapper")) {
                document.querySelectorAll("[id^='dropdown-']").forEach(el => el.classList.add("hidden"));
            }
        });
    </script>

</x-app-layout>