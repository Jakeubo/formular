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
                <table class="w-full border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">Jméno</th>
                            <th class="px-3 py-2 text-left">Email</th>
                            <th class="px-3 py-2 text-left">Telefon</th>
                            <th class="px-3 py-2 text-left">Adresa</th>
                            <th class="px-3 py-2 text-left">Město</th>
                            <th class="px-3 py-2 text-left">IČO</th>
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
                            <td class="px-3 py-2">
                                <a href="{{ route('customers.show', $customer->id) }}"
                                   class="text-indigo-600 hover:underline">🔍 Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-3 py-4 text-center text-gray-500">
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
