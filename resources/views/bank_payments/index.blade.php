<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Zachycené bankovní platby
        </h2>
    </x-slot>

    <div class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <form action="{{ route('bank-payments.check') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700">
                        🔄 Zkontrolovat nové platby
                    </button>
                </form>
                @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                    {{ session('success') }}
                </div>
                @endif

            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <table class="w-full border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left">ID</th>
                                <th class="px-3 py-2 text-left">Variabilní symbol</th>
                                <th class="px-3 py-2 text-left">Částka</th>
                                <th class="px-3 py-2 text-left">Číslo účtu</th>
                                <th class="px-3 py-2 text-left">Datum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                            <tr class="border-t">
                                <td class="px-3 py-2">{{ $payment->id }}</td>
                                <td class="px-3 py-2">{{ $payment->variable_symbol ?? '—' }}</td>
                                <td class="px-3 py-2">{{ number_format($payment->amount, 2, ',', ' ') }} Kč</td>
                                <td class="px-3 py-2">{{ $payment->account_number ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $payment->received_at ? $payment->received_at->format('d.m.Y H:i') : '—' }}</td>

                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-gray-500">
                                    Zatím žádné platby
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $payments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>