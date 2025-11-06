<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <a href="{{ route('invoices.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-xl shadow hover:bg-gray-900 transition">
                ← Zpět na seznam
            </a>
            <h2 class="font-semibold text-2xl text-gray-900">
                Detail faktury
            </h2>
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
            }, 4000);
        </script>
        @endif

    </x-slot>

    <div class="py-10 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Horní karty -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- Levý sjednocený container -->
                <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Kontaktní údaje -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Zákazník</h3>
                            @if($invoice->order)
                            <dl class="space-y-2 text-sm text-gray-700">
                                <div>
                                    <dt class="font-medium">Jméno</dt>
                                    <dd>{{ $invoice->order->first_name }} {{ $invoice->order->last_name }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium">Email</dt>
                                    <dd>{{ $invoice->order->email }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium">Telefon</dt>
                                    <dd>{{ $invoice->order->phone }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium">Adresa</dt>
                                    <dd>{{ $invoice->order->address }}, {{ $invoice->order->zip }} {{ $invoice->order->city }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium">Země</dt>
                                    <dd>{{ $invoice->order->country ?? 'ČR' }}</dd>
                                </div>
                            </dl>
                            @else
                            <p class="italic text-gray-500">Zákazník není uveden</p>
                            @endif
                        </div>

                        <!-- Doprava -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Doprava</h3>
                            <dl class="space-y-2 text-sm text-gray-700">
                                @if(!empty($invoice->order->carrier))
                                <div>
                                    <dt class="font-medium">Dopravce</dt>
                                    <dd>{{ $invoice->order->carrier }}</dd>
                                </div>
                                @endif
                                @if(!empty($invoice->order->carrier_address))
                                <div>
                                    <dt class="font-medium">Výdejní místo / adresa</dt>
                                    <dd class="whitespace-pre-line">{{ $invoice->order->carrier_address }}</dd>
                                </div>
                                @endif
                                @if(optional($invoice->order)->company_ico)
                                <div>
                                    <dt class="font-medium">IČO</dt>
                                    <dd class="text-gray-900">{{ $invoice->order->company_ico }}</dd>
                                </div>
                                @endif
                                @if(optional($invoice->order)->company_dic)
                                <div>
                                    <dt class="font-medium">DIČ</dt>
                                    <dd class="text-gray-900">{{ $invoice->order->company_dic }}</dd>
                                </div>
                                @endif

                            </dl>

                        </div>

                    </div>
                </div>

                <!-- Pravý sjednocený container -->
                <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Základní údaje -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Základní údaje</h3>
                            <dl class="space-y-2 text-sm text-gray-700">
                                <div>
                                    <dt class="font-medium">Číslo faktury</dt>
                                    <dd class="text-indigo-600 font-semibold">{{ $invoice->invoice_number }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium">Variabilní symbol</dt>
                                    <dd class="text-indigo-600 font-semibold">{{ $invoice->variable_symbol ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium">Status</dt>
                                    <dd>
                                        @switch($invoice->status)
                                        @case('new') <span class="px-2 py-1 rounded-lg bg-gray-100 text-gray-700 text-xs">Nová</span> @break
                                        @case('sent') <span class="px-2 py-1 rounded-lg bg-blue-100 text-blue-700 text-xs">Odeslaná</span> @break
                                        @case('paid') <span class="px-2 py-1 rounded-lg bg-green-100 text-green-700 text-xs">Zaplacená</span> @break
                                        @case('overdue') <span class="px-2 py-1 rounded-lg bg-red-100 text-red-700 text-xs">Po splatnosti</span> @break
                                        @default <span class="px-2 py-1 rounded-lg bg-gray-200 text-gray-800 text-xs">{{ $invoice->status }}</span>
                                        @endswitch
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Termíny -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Termíny</h3>
                            <dl class="space-y-2 text-sm text-gray-700">
                                <div>
                                    <dt class="font-medium">Datum vystavení</dt>
                                    <dd>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium">Datum splatnosti</dt>
                                    <dd>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Akční tlačítka -->
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('invoices.edit', $invoice) }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-50">
                    ✏️ Upravit
                </a>
                <form action="{{ route('invoices.paid', $invoice) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-xl shadow hover:bg-green-700">
                        ✅ Uhradit
                    </button>
                </form>

                <!-- místo přímého submitu otevřeme modal -->
                <button type="button"
                    onclick="openSendModal('{{ $invoice->id }}', '{{ $invoice->invoice_number }}', '{{ $invoice->order->email }}')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl shadow hover:bg-blue-700">
                    📧 Odeslat fakturu
                </button>



                <a href="{{ route('invoices.download', ['token' => $invoice->download_token]) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-xl shadow hover:bg-gray-700">
                    ⬇️ Stáhnout
                </a>


                <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="inline"
                    onsubmit="return confirm('Opravdu smazat tuto fakturu?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-xl shadow hover:bg-red-700">
                        🗑️ Smazat
                    </button>
                </form>

            </div>

            <!-- Položky faktury -->
            <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Položky</h3>
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="px-2 py-2 text-center font-medium w-20">Kusy</th>
                            <th class="px-3 py-2 text-left font-medium">Název položky</th>
                            <th class="px-2 py-2 text-right font-medium w-32">Cena/ks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($invoice->items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-2 py-2 text-center">{{ $item->quantity }}</td>
                            <td class="px-3 py-2">{{ $item->description }}</td>
                            <td class="px-2 py-2 text-right">{{ number_format($item->unit_price, 2) }} Kč</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Celková cena -->
                <div class="mt-6 text-right text-xl font-bold text-gray-900">
                    Celková cena: <span class="text-indigo-600">{{ number_format($invoice->total_price, 2) }} Kč</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Modal pro odeslání faktury -->
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