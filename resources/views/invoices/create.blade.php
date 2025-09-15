<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nová faktura') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('invoices.store') }}">
                    @csrf
                    <!-- Select zákazníka -->
                    <div>
                        <label for="customer_id">Zákazník</label>
                        <select name="customer_id" id="customer_id" required>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">
                                    {{ $customer->first_name }} {{ $customer->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Položky faktury -->
                    <div id="items">
                        <label>Položky</label>
                        <input type="text" name="items[0][description]" placeholder="Popis" required>
                        <input type="number" name="items[0][qty]" placeholder="Množství" required>
                        <input type="number" name="items[0][unit_price]" placeholder="Cena/ks" required>
                    </div>

                    <button type="submit">💾 Uložit fakturu</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
