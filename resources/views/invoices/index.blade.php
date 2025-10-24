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
                                placeholder="Hledat podle jména, e-mailu, čísla faktury nebo názvu položky..."
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

                                    <td class="px-4 py-3 relative status-wrapper">
                                        <div class="flex items-center gap-1">
                                            @switch($invoice->status)
                                            @case('new')
                                            <span class="px-2 py-1 rounded-lg bg-gray-100 text-gray-700 text-xs font-medium">🆕 Nová</span>
                                            @break

                                            @case('sent')
                                            <span class="px-2 py-1 rounded-lg bg-blue-100 text-blue-700 text-xs font-medium">📧 E-mail odeslán</span>
                                            @break

                                            @case('paid')
                                            <span class="px-2 py-1 rounded-lg bg-green-100 text-green-700 text-xs font-medium">💰 Zaplacená</span>
                                            <!-- 🔽 šipka pouze pro zaplacené -->
                                            <button onclick="toggleStatusDropdown('{{ $invoice->id }}')"
                                                class="text-gray-400 hover:text-gray-600 text-xs ml-1">▼</button>
                                            @break

                                            @case('shipped')
                                            <span class="px-2 py-1 rounded-lg bg-purple-100 text-purple-700 text-xs font-medium">📦 Zásilka odeslaná</span>
                                            @break

                                            @case('overdue')
                                            <span class="px-2 py-1 rounded-lg bg-red-100 text-red-700 text-xs font-medium">⏰ Po splatnosti</span>
                                            @break

                                            @default
                                            <span class="px-2 py-1 rounded-lg bg-gray-200 text-gray-800 text-xs font-medium">{{ $invoice->status }}</span>
                                            @endswitch
                                        </div>

                                        <!-- Dropdown statusů -->
                                        @if($invoice->status === 'paid')
                                        <div id="status-dropdown-{{ $invoice->id }}"
                                            class="hidden absolute bg-white border rounded-lg shadow-lg mt-1 z-10 w-44">
                                            <form method="POST" action="{{ route('invoices.updateStatus', $invoice) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button name="status" value="shipped"
                                                    class="block w-full text-left px-3 py-2 text-sm hover:bg-purple-50 text-purple-600">
                                                    📦 Označit jako odeslanou
                                                </button>
                                            </form>
                                        </div>
                                        @endif
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
                                            <button type="button"
                                                onclick="openSendModal('{{ $invoice->id }}', '{{ $invoice->invoice_number }}', '{{ $invoice->order->email }}')"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50">
                                                📧 Odeslat fakturu
                                            </button>

                                            @if($invoice->download_token)
                                            <a href="{{ route('invoices.download', ['token' => $invoice->download_token]) }}" class="btn btn-sm btn-primary">
                                                📎 Stáhnout
                                            </a>
                                            @else
                                            <span class="text-gray-400">Není dostupné</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <form method="GET" action="{{ route('invoices.index') }}" class="mb-4 flex mt-5 items-center gap-2">
                        <label for="per_page" class="text-sm text-gray-600">Zobrazit na stránku:</label>
                        <select name="per_page" id="per_page"
                            onchange="this.form.submit()"
                            class="border border-gray-300 rounded-lg px-2 py-1 text-sm">
                            @foreach ([10, 20, 50, 100] as $num)
                            <option value="{{ $num }}" {{ $perPage == $num ? 'selected' : '' }}>
                                {{ $num }}
                            </option>
                            @endforeach
                        </select>
                    </form>
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

<!-- Modal pro úpravu emailu -->
<div id="sendModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 relative">
        <button onclick="closeSendModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">✕</button>
        <h2 class="text-xl font-bold mb-4">Odeslat fakturu</h2>

        <form id="sendForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Předmět</label>
                <input type="text" name="subject" id="emailSubject" required
                    class="w-full border rounded-lg px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Zpráva</label>
                <textarea name="body" id="emailBody" rows="6" required
                    class="w-full border rounded-lg px-3 py-2"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeSendModal()" class="px-4 py-2 bg-gray-200 rounded-lg">Zrušit</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">📧 Odeslat</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openSendModal(invoiceId, invoiceNumber, email) {
        const modal = document.getElementById("sendModal");
        modal.classList.remove("hidden");

        // Nastavení formuláře
        const form = document.getElementById("sendForm");
        form.action = `/invoices/${invoiceId}/send`;

        // Předvyplnění polí
        document.getElementById("emailSubject").value = `Faktura ${invoiceNumber}`;
        document.getElementById("emailBody").value =
            `Dobrý den,

v příloze zasíláme fakturu č. ${invoiceNumber}.

S pozdravem,
Zapichnito3D tým`;
    }

    function closeSendModal() {
        document.getElementById("sendModal").classList.add("hidden");
    }
</script>

<script>
    function toggleStatusDropdown(id) {
        document.querySelectorAll("[id^='status-dropdown-']").forEach(el => {
            if (el.id === `status-dropdown-${id}`) {
                el.classList.toggle("hidden");
            } else {
                el.classList.add("hidden");
            }
        });
    }

    // Klik mimo dropdown → zavřít
    document.addEventListener("click", e => {
        if (!e.target.closest(".status-wrapper")) {
            document.querySelectorAll("[id^='status-dropdown-']").forEach(el => el.classList.add("hidden"));
        }
    });
</script>