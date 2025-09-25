<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">
                Detail zákazníka
            </h2>

            <a href="{{ route('customers.index') }}"
               class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 text-sm 
                      rounded-md border border-gray-300 shadow-sm hover:bg-gray-200 
                      transition">
                ⬅️ Zpět
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">
                    {{ $customer->first_name }} {{ $customer->last_name }}
                </h3>

                <p><strong>Email:</strong> {{ $customer->email }}</p>
                <p><strong>Telefon:</strong> {{ $customer->phone ?? '—' }}</p>
                <p><strong>Adresa:</strong> {{ $customer->address }}, {{ $customer->zip }} {{ $customer->city }}</p>
                <p><strong>Stát:</strong> {{ $customer->country ?? 'ČR' }}</p>

                <hr class="my-4">

                <p><strong>Dopravce:</strong> {{ $customer->carrier ?? '—' }}</p>
                <p><strong>Výdejní místo:</strong> {{ $customer->carrier_address ?? '—' }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
