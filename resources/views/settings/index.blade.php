<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            ⚙️ Nastavení
        </h2>
    </x-slot>

    <div class="py-8 mt-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-10">

            {{-- 🧾 Ceny dopravy --}}
            <div class="bg-white shadow rounded-xl p-6 border border-gray-100">
                @if(session('success'))
                    <div class="mb-4 p-3 rounded bg-green-100 text-green-800 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <h3 class="text-lg font-bold mb-4">🚚 Ceny dopravy</h3>

                <form method="POST" action="{{ route('settings.shipping.update') }}">
                    @csrf
                    <table class="w-full border rounded text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-4 py-2 text-left">Dopravce</th>
                                <th class="border px-4 py-2 text-left">Cena (Kč)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shippingMethods as $method)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="border px-4 py-2 font-medium">{{ $method->name }}</td>
                                    <td class="border px-4 py-2">
                                        <input type="number" step="1" name="shipping[{{ $method->id }}]"
                                            value="{{ $method->price }}"
                                            class="border rounded-md p-1 w-32 text-right focus:ring-indigo-500 focus:border-indigo-500">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <button type="submit"
                        class="mt-4 px-5 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 transition">
                        💾 Uložit změny
                    </button>
                </form>
            </div>

            {{-- 💬 Zákaznické e-maily --}}
            <div class="bg-white shadow rounded-xl p-6 border border-gray-100">
                <h3 class="text-lg font-bold mb-4">💬 Zákaznické e-maily</h3>

                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    <div class="flex items-center space-x-8">
                        <label class="flex items-center space-x-2">
                            <input type="radio" name="satisfaction_emails_enabled" value="1"
                                   {{ $satisfactionEnabled == '1' ? 'checked' : '' }}
                                   class="text-indigo-600 focus:ring-indigo-500">
                            <span>Zapnuto (odesílat 4 dny po odeslání objednávky)</span>
                        </label>

                        <label class="flex items-center space-x-2">
                            <input type="radio" name="satisfaction_emails_enabled" value="0"
                                   {{ $satisfactionEnabled == '0' ? 'checked' : '' }}
                                   class="text-gray-400 focus:ring-gray-400">
                            <span>Vypnuto</span>
                        </label>
                    </div>

                    <button type="submit"
                        class="mt-4 px-5 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 transition">
                        💾 Uložit nastavení
                    </button>
                </form>
            </div>

            {{-- 💌 Log odeslaných e-mailů --}}
            <div class="bg-white shadow rounded-xl p-6 border border-gray-100 mt-10">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold">💌 Odeslané e-maily</h3>

                    <form method="GET" class="flex items-center space-x-2">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Hledat..."
                            class="border rounded-md p-2 text-sm w-48 focus:ring-indigo-500 focus:border-indigo-500">
                        <button type="submit" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm">
                            🔍
                        </button>
                    </form>
                </div>

                @if($logs->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border border-gray-200 rounded-md">
                            <thead class="bg-gray-50 border-b text-gray-700 uppercase text-xs">
                                <tr>
                                    <th class="px-3 py-2 text-left">Příjemce</th>
                                    <th class="px-3 py-2 text-left">Předmět</th>
                                    <th class="px-3 py-2 text-left">Typ</th>
                                    <th class="px-3 py-2 text-left">Datum</th>
                                    <th class="px-3 py-2 text-left">Stav</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-3 py-2 font-medium text-gray-900">{{ $log->to_email }}</td>
                                        <td class="px-3 py-2 text-gray-700">{{ $log->subject }}</td>
                                        <td class="px-3 py-2 text-gray-500">{{ ucfirst($log->type ?? 'jiný') }}</td>
                                        <td class="px-3 py-2 text-gray-500">{{ optional($log->sent_at)->format('d.m.Y H:i') }}</td>
                                        <td class="px-3 py-2">
                                            @if($log->success)
                                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded">✅ Úspěšně</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded">❌ Chyba</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($log->error_message)
                                        <tr class="bg-red-50 text-xs text-red-700">
                                            <td colspan="5" class="px-4 py-2">
                                                ⚠️ {{ $log->error_message }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                @else
                    <p class="text-gray-500 italic">Zatím nebyly odeslány žádné e-maily.</p>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
