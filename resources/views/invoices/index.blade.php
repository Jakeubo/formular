<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900">
                Faktury
            </h2>
            <button onclick="openInvoiceModal()"
                class="inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-xl shadow hover:bg-indigo-700 transition">
                ➕ Nová faktura
            </button>
        </div>

        @if(session('success'))
    <div id="flash-message" class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg shadow-md">
            {{ session('success') }}
        </div>
    </div>

    <script>
        setTimeout(() => {
            const el = document.getElementById('flash-message');
            if (el) el.style.display = 'none';
        }, 8000);
    </script>
@endif

        
    </x-slot>

    <div class="py-10 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow border border-gray-100 p-6">

                <!-- Tabulka -->
                <div class="overflow-x-auto">
                    <div class="bg-white rounded-2xl shadow border border-gray-100 p-6">

                        <!-- Vyhledávání -->
                        <form method="GET" action="{{ route('invoices.index') }}" class="mb-4 flex gap-2">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Hledat podle jména nebo emailu..."
                                class="flex-grow border border-gray-300 rounded-lg px-3 py-2 text-sm 
                      focus:ring-indigo-500 focus:border-indigo-500">
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                🔍 Hledat
                            </button>

                            @if(request('search'))
                            <a href="{{ route('invoices.index') }}"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                ❌ Zrušit filtr
                            </a>
                            @endif
                        </form>

                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-gray-700 text-left">
                                    <th class="px-4 py-3 font-medium">Číslo</th>
                                    <th class="px-4 py-3 font-medium">Zákazník</th>
                                    <th class="px-4 py-3 font-medium">Cena</th>
                                    <th class="px-4 py-3 font-medium">Status</th>
                                    <th class="px-4 py-3 font-medium">Splatnost / Platba</th>
                                    <th class="px-4 py-3 font-medium text-center">Akce</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($invoices as $invoice)
                                <tr class="hover:bg-gray-50">
                                    <!-- Číslo -->
                                    <td class="px-4 py-3 font-semibold text-indigo-600">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="hover:underline">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                    </td>

                                    <!-- Zákazník -->
                                    <td class="px-4 py-3">
                                        @if($invoice->order)
                                        {{ $invoice->order->first_name }} {{ $invoice->order->last_name }}
                                        @else
                                        <em class="text-gray-400">—</em>
                                        @endif
                                    </td>

                                    <!-- Cena -->
                                    <td class="px-4 py-3 font-medium">
                                        {{ number_format($invoice->total_price, 2, ',', ' ') }} Kč
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3">
                                        @switch($invoice->status)
                                        @case('new')
                                        <span class="px-2 py-1 rounded-lg bg-gray-100 text-gray-700 text-xs font-medium">🆕 Nová</span>
                                        @break
                                        @case('sent')
                                        <span class="px-2 py-1 rounded-lg bg-blue-100 text-blue-700 text-xs font-medium">📤 Odeslaná</span>
                                        @break
                                        @case('paid')
                                        <span class="px-2 py-1 rounded-lg bg-green-100 text-green-700 text-xs font-medium">✅ Zaplacená</span>
                                        @break
                                        @case('overdue')
                                        <span class="px-2 py-1 rounded-lg bg-red-100 text-red-700 text-xs font-medium">⏰ Po splatnosti</span>
                                        @break
                                        @default
                                        <span class="px-2 py-1 rounded-lg bg-gray-200 text-gray-800 text-xs font-medium">{{ $invoice->status }}</span>
                                        @endswitch
                                    </td>

                                    <!-- Splatnost / Platba -->
                                    <td class="px-4 py-3 text-sm">
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

                                    <!-- Akce -->
                                    <td class="px-4 py-3 text-center relative dropdown-wrapper">
                                        <button onclick="toggleDropdown('{{ $invoice->id }}')"
                                            class="px-2 py-1 rounded hover:bg-gray-100 text-gray-500">⋮</button>

                                        <!-- Dropdown -->
                                        <div id="dropdown-{{ $invoice->id }}"
                                            class="hidden absolute right-0 mt-2 w-44 bg-white border rounded-xl shadow-lg z-10 overflow-hidden">
                                            <a href="{{ route('invoices.send', $invoice) }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">✉️ Odeslat fakturu</a>
                                            <a href="{{ route('invoices.download', $invoice) }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-50">⬇️ Stáhnout fakturu</a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginace -->
                    <div class="mt-6">
                        {{ $invoices->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div id="invoiceModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl p-6 relative overflow-y-auto max-h-[90vh]">
                <button onclick="closeInvoiceModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">
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

            function toggleDropdown(id) {
                document.querySelectorAll("[id^='dropdown-']").forEach(el => {
                    if (el.id === "dropdown-" + id) {
                        el.classList.toggle("hidden");
                    } else {
                        el.classList.add("hidden");
                    }
                });
            }

            // Klik mimo dropdown-wrapper zavře všechny dropdowny
            document.addEventListener("click", function(e) {
                if (!e.target.closest(".dropdown-wrapper")) {
                    document.querySelectorAll("[id^='dropdown-']").forEach(el => el.classList.add("hidden"));
                }
            });
        </script>
</x-app-layout>