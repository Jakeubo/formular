@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded-xl shadow">
    <h1 class="text-2xl font-bold mb-4">Nová faktura</h1>

    <form action="{{ route('invoices.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block mb-1 font-medium">Zákazník</label>
            <select name="customer_id" class="w-full border rounded p-2">
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">
                        {{ $customer->first_name }} {{ $customer->last_name }} ({{ $customer->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-medium">Datum vystavení</label>
            <input type="date" name="invoice_date" class="w-full border rounded p-2" required>
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-medium">Datum splatnosti</label>
            <input type="date" name="due_date" class="w-full border rounded p-2" required>
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-medium">Poznámka</label>
            <textarea name="note" class="w-full border rounded p-2"></textarea>
        </div>

        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
            💾 Uložit fakturu
        </button>
    </form>
</div>
@endsection
