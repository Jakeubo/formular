<form method="POST" action="{{ route('invoices.store') }}" id="invoiceForm">
    @csrf

    <!-- Zákazník z objednávky -->
    <div class="mb-4">
        <label class="block font-medium">Zákazník (z objednávek)</label>
        <select name="order_id" class="w-full border rounded p-2" required>
            <option value="">-- Vyberte zákazníka --</option>
            @foreach($orders as $order)
                <option value="{{ $order->id }}">
                    {{ $order->first_name }} {{ $order->last_name }}
                    ({{ $order->email }}, {{ $order->city }})
                </option>
            @endforeach
        </select>
    </div>

    <!-- Číslo faktury -->
    <div class="mb-4">
        <label class="block font-medium">Číslo faktury</label>
        <input type="text" name="invoice_number"
               value="{{ 'F' . now()->format('YmdHis') }}"
               class="w-full border rounded p-2" required>
    </div>

    <!-- Datum vystavení -->
    <div class="mb-4">
        <label class="block font-medium">Datum vystavení</label>
        <input type="date" name="issue_date"
               value="{{ now()->toDateString() }}"
               class="w-full border rounded p-2" required>
    </div>

    <!-- Datum splatnosti -->
    <div class="mb-4">
        <label class="block font-medium">Datum splatnosti</label>
        <input type="date" name="due_date"
               value="{{ now()->addDays(14)->toDateString() }}"
               class="w-full border rounded p-2" required>
    </div>

    <!-- Stav -->
    <div class="mb-4">
        <label class="block font-medium">Stav faktury</label>
        <select name="status" class="w-full border rounded p-2">
            <option value="new">Nová</option>
            <option value="sent">Odeslaná</option>
            <option value="paid">Zaplacená</option>
            <option value="overdue">Po splatnosti</option>
        </select>
    </div>

    <!-- Položky faktury -->
    <div class="mb-4">
        <label class="block font-medium mb-2">Položky faktury</label>
        <table class="w-full border" id="itemsTable">
            <thead>
            <tr class="bg-gray-100">
                <th class="px-2 py-1 border">Název položky</th>
                <th class="px-2 py-1 border">Množství</th>
                <th class="px-2 py-1 border">Cena/ks</th>
                <th class="px-2 py-1 border">Celkem</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="border p-1">
                    <input type="text" name="items[0][description]" class="w-full border rounded p-1" required>
                </td>
                <td class="border p-1">
                    <input type="number" name="items[0][quantity]" value="1" min="1"
                           class="w-full border rounded p-1 quantity" required>
                </td>
                <td class="border p-1">
                    <input type="number" step="0.01" name="items[0][unit_price]" value="0"
                           class="w-full border rounded p-1 unit_price" required>
                </td>
                <td class="border p-1 text-center total">0</td>
                <td class="border p-1 text-center">
                    <button type="button" onclick="removeRow(this)" class="text-red-500">✕</button>
                </td>
            </tr>
            </tbody>
        </table>
        <button type="button" onclick="addRow()"
                class="mt-2 bg-gray-200 px-3 py-1 rounded">➕ Přidat položku</button>
    </div>

    <!-- Celková cena -->
    <div class="mb-4 text-right">
        <strong>Celková cena: </strong>
        <span id="grandTotal">0 Kč</span>
    </div>

    <button type="submit"
            class="bg-pink-500 text-white px-4 py-2 rounded shadow hover:bg-pink-600">
        💾 Uložit fakturu
    </button>
</form>

<script>
    function updateTotals() {
        let grandTotal = 0;
        document.querySelectorAll("#itemsTable tbody tr").forEach(row => {
            let qty = parseFloat(row.querySelector(".quantity").value) || 0;
            let unitPrice = parseFloat(row.querySelector(".unit_price").value) || 0;
            let total = qty * unitPrice;
            row.querySelector(".total").innerText = total.toFixed(2);
            grandTotal += total;
        });
        document.getElementById("grandTotal").innerText = grandTotal.toFixed(2) + " Kč";
    }

    function addRow() {
        let tbody = document.querySelector("#itemsTable tbody");
        let index = tbody.querySelectorAll("tr").length;
        let newRow = document.createElement("tr");
        newRow.innerHTML = `
            <td class="border p-1">
                <input type="text" name="items[${index}][description]" class="w-full border rounded p-1" required>
            </td>
            <td class="border p-1">
                <input type="number" name="items[${index}][quantity]" value="1" min="1"
                       class="w-full border rounded p-1 quantity" required>
            </td>
            <td class="border p-1">
                <input type="number" step="0.01" name="items[${index}][unit_price]" value="0"
                       class="w-full border rounded p-1 unit_price" required>
            </td>
            <td class="border p-1 text-center total">0</td>
            <td class="border p-1 text-center">
                <button type="button" onclick="removeRow(this)" class="text-red-500">✕</button>
            </td>
        `;
        tbody.appendChild(newRow);
        attachEvents(newRow);
    }

    function removeRow(btn) {
        btn.closest("tr").remove();
        updateTotals();
    }

    function attachEvents(row) {
        row.querySelectorAll(".quantity, .unit_price").forEach(input => {
            input.addEventListener("input", updateTotals);
        });
    }

    document.querySelectorAll(".quantity, .unit_price").forEach(input => {
        input.addEventListener("input", updateTotals);
    });
</script>
