<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Zákazníci
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">

                <!-- Vyhledávání -->
                <form method="GET" action="{{ route('customers.index') }}" class="mb-4 flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Hledat podle jména, emailu nebo města..."
                        class="flex-grow border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">

                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        🔍 Hledat
                    </button>

                    @if(request('search'))
                    <a href="{{ route('customers.index') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        ❌ Zrušit filtr
                    </a>
                    @endif
                </form>

                <!-- Tabulka zákazníků -->
                <!-- Tabulka zákazníků -->
                <table class="w-full border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">Jméno</th>
                            <th class="px-3 py-2 text-left">Email</th>
                            <th class="px-3 py-2 text-left">Telefon</th>
                            <th class="px-3 py-2 text-left">Adresa</th>
                            <th class="px-3 py-2 text-left">Město</th>
                            <th class="px-3 py-2 text-left">IČO</th>
                            <th class="px-3 py-2 text-left">Štítek</th>
                            <th class="px-3 py-2 text-left">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-3 py-2">{{ $customer->first_name }} {{ $customer->last_name }}</td>
                            <td class="px-3 py-2">{{ $customer->email }}</td>
                            <td class="px-3 py-2">{{ $customer->phone }}</td>
                            <td class="px-3 py-2">{{ $customer->address }}</td>
                            <td class="px-3 py-2">{{ $customer->city }}</td>
                            <td class="px-3 py-2">{{ $customer->company_ico ?? '—' }}</td>

                            <!-- 🟢 Nový sloupec ŠTÍTEK -->
                            <td class="px-3 py-2">
                                @php
                                $order = \App\Models\Order::where('public_token', $customer->public_token)->first();
                                $carrier = strtolower($order->carrier ?? '');
                                @endphp

                                @if(!$order)
                                <span class="text-gray-400 text-sm">—</span>
                                @elseif(Str::contains($carrier, 'zasilkovna'))
                                <a href="{{ route('labels.zasilkovna', $order->public_token) }}" target="_blank"
                                    class="px-2 py-1 bg-red-100 text-red-700 rounded-lg text-xs hover:bg-red-200 transition">
                                    📦 Zásilkovna
                                </a>
                                @elseif(Str::contains($carrier, 'balikovna'))
                                <a href="{{ route('labels.balikovna', $order->public_token) }}" target="_blank"
                                    class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-lg text-xs hover:bg-yellow-200 transition">
                                    📦 Balíkovna
                                </a>
                                @elseif(Str::contains($carrier, 'pplparcelshop'))
                                <a href="{{ url('/label/wait/' . $order->public_token . '?carrier=ppl-parcelshop') }}" target="_blank"
                                    class="px-2 py-1 bg-blue-100 text-blue-700 rounded-lg text-xs hover:bg-blue-200 transition">
                                    📦 PPL Parcelshop
                                </a>

                                @elseif(Str::contains($carrier, 'ppl'))
                                <a href="{{ url('/label/wait/' . $order->public_token . '?carrier=ppl') }}" target="_blank"
                                    class="px-2 py-1 bg-blue-100 text-blue-700 rounded-lg text-xs hover:bg-blue-200 transition">
                                    🚚 PPL Domů
                                </a>
                                @elseif(Str::contains($carrier, 'osobni'))
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-lg text-xs">
                                    🏠 Osobní odběr
                                </span>
                                @else
                                <span class="text-gray-400 text-sm">—</span>
                                @endif
                            </td>

                            <td class="px-3 py-2">
                                <a href="{{ route('customers.show', $customer->id) }}"
                                    class="text-indigo-600 hover:underline">🔍 Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-3 py-4 text-center text-gray-500">
                                Žádní zákazníci
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>


                <div class="mt-4">
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>