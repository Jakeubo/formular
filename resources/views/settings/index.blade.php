<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            ⚙️ Nastavení
        </h2>
    </x-slot>

    <div class="py-8 mt-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-xl p-6">
                @if(session('success'))
                    <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                <h3 class="text-lg font-bold mb-4">Ceny dopravy</h3>

                <form method="POST" action="{{ route('settings.shipping.update') }}">
                    @csrf
                    <table class="w-full border rounded">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-4 py-2 text-left">Dopravce</th>
                                <th class="border px-4 py-2 text-left">Cena (Kč)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shippingMethods as $method)
                                <tr>
                                    <td class="border px-4 py-2">{{ $method->name }}</td>
                                    <td class="border px-4 py-2">
                                        <input type="number" step="1" name="shipping[{{ $method->id }}]" 
                                               value="{{ $method->price }}" class="border rounded p-1 w-32">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <button type="submit" class="mt-4 px-4 py-2 bg-pink-500 text-white rounded shadow hover:bg-pink-600">
                        💾 Uložit změny
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
