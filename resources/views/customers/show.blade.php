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
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-bold mb-2">
                            {{ $customer->first_name }} {{ $customer->last_name }}
                        </h3>

                        <p><strong>Email:</strong> {{ $customer->email }}</p>
                        <p><strong>Telefon:</strong> {{ $customer->phone ?? '—' }}</p>
                        <p><strong>Adresa:</strong> {{ $customer->address }}, {{ $customer->zip }} {{ $customer->city }}</p>
                        <p><strong>Stát:</strong> {{ $customer->country ?? 'ČR' }}</p>

                        <hr class="my-4">

                        <p><strong>Dopravce:</strong> {{ $customer->carrier ?? '—' }}</p>
                        <p><strong>Výdejní místo:</strong> {{ $customer->carrier_address ?? '—' }}</p>

                        <p class="mt-4"><strong>IČO:</strong> {{ $customer->company_ico ?? '—' }}</p>
                        <p><strong>DIČ:</strong> {{ $customer->company_dic ?? '—' }}</p>
                    </div>

                    <div class="flex flex-col gap-2">
                        <button onclick="openEditModal()" 
                                class="px-4 py-2 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-50">
                            ✏️ Upravit
                        </button>

                        <button onclick="openDeleteModal()" 
                                class="px-4 py-2 bg-red-600 text-white rounded-xl shadow hover:bg-red-700">
                            🗑️ Odstranit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- 📝 Modal pro úpravu -->
<div id="editModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 relative">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">✕</button>
        <h2 class="text-xl font-bold mb-4">Upravit zákazníka</h2>

        <form method="POST" action="{{ route('customers.update', $customer) }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium">Email</label>
                    <input type="email" name="email" value="{{ $customer->email }}" 
                           class="w-full border rounded px-3 py-2" required>
                </div>

                <div>
                    <label class="block text-sm font-medium">Telefon</label>
                    <input type="text" name="phone" value="{{ $customer->phone }}" 
                           class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">IČO</label>
                    <input type="text" name="company_ico" value="{{ $customer->company_ico }}" 
                           maxlength="12" class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">DIČ</label>
                    <input type="text" name="company_dic" value="{{ $customer->company_dic }}" 
                           maxlength="15" class="w-full border rounded px-3 py-2">
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-200 rounded-lg">Zrušit</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Uložit</button>
            </div>
        </form>
    </div>
</div>

<!-- ❗ Modal pro smazání -->
<div id="deleteModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 relative">
        <button onclick="closeDeleteModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">✕</button>
        <h2 class="text-xl font-bold mb-2 text-red-600">Smazat zákazníka</h2>
        <p class="text-sm text-gray-700 mb-4">
            Opravdu chceš tohoto zákazníka trvale smazat?  
            Toto nelze vrátit. Pro potvrzení zadej e-mail <strong>{{ $customer->email }}</strong>.
        </p>

        <input id="confirmDeleteInput" type="text" 
               placeholder="Zadej e-mail pro potvrzení" 
               class="w-full border rounded px-3 py-2 mb-4">

        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 rounded-lg">Zrušit</button>

            <form id="deleteForm" method="POST" action="{{ route('customers.destroy', $customer) }}">
                @csrf
                @method('DELETE')
                <button id="deleteBtn" type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-60" 
                        disabled>✔️ Smazat</button>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal() {
    document.getElementById("editModal").classList.remove("hidden");
}
function closeEditModal() {
    document.getElementById("editModal").classList.add("hidden");
}

function openDeleteModal() {
    document.getElementById("deleteModal").classList.remove("hidden");
}
function closeDeleteModal() {
    document.getElementById("deleteModal").classList.add("hidden");
}

// kontrola potvrzení pro smazání
const input = document.getElementById("confirmDeleteInput");
const btn = document.getElementById("deleteBtn");
if (input) {
    input.addEventListener("input", () => {
        btn.disabled = input.value.trim() !== "{{ $customer->email }}";
    });
}
</script>
