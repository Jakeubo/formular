<form method="POST" action="{{ route('invoices.store') }}" id="invoiceForm">
    @csrf

    <div class="grid grid-cols-2 gap-6">
        <!-- Levý sloupec -->
        <div>
            <div class="mb-4">
                <label class="block font-medium">Zákazník (z objednávek)</label>
                <select name="order_id" id="orderSelect" class="w-full border rounded p-2" required>
                    <option value="">-- Vyberte zákazníka --</option>
                    @foreach($orders as $order)
                        <option value="{{ $order->id }}">
                            {{ $order->first_name }} {{ $order->last_name }} ({{ $order->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Kontaktní údaje zákazníka -->
            <div id="customerDetails" class="hidden border p-3 rounded bg-gray-50 mb-4">
                <p><strong>Jméno:</strong> <span id="custName"></span></p>
                <p><strong>Email:</strong> <span id="custEmail"></span></p>
                <p><strong>Telefon:</strong> <span id="custPhone"></span></p>
                <p><strong>Adresa:</strong> <span id="custAddress"></span></p>
                <p><strong>Město:</strong> <span id="custCity"></span></p>
                <p><strong>PSČ:</strong> <span id="custZip"></span></p>
            </div>

            <div class="mb-4">
                <label class="block font-medium">Dopravce</label>
                <input type="text" name="carrier" id="carrier"
                       class="w-full border rounded p-2 bg-gray-100" readonly>
            </div>

            <div class="mb-4">
                <label class="block font-medium">Výdejní místo / adresa</label>
                <input type="text" name="carrier_address" id="carrier_address"
                       class="w-full border rounded p-2 bg-gray-100" readonly>
            </div>
        </div>

        <!-- Pravý sloupec -->
        <div>
            <div class="mb-4">
                <label class="block font-medium">Číslo faktury</label>
                <input type="text" name="invoice_number"
                       value="{{ 'F' . now()->format('YmdHis') }}"
                       class="w-full border rounded p-2" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium">Datum vystavení</label>
                <input type="date" name="issue_date"
                       value="{{ now()->toDateString() }}"
                       class="w-full border rounded p-2" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium">Datum splatnosti</label>
                <input type="date" name="due_date"
                       value="{{ now()->addDays(14)->toDateString() }}"
                       class="w-full border rounded p-2" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium">Stav faktury</label>
                <select name="status" class="w-full border rounded p-2">
                    <option value="new">Nová</option>
                    <option value="sent">Odeslaná</option>
                    <option value="paid">Zaplacená</option>
                    <option value="overdue">Po splatnosti</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Položky faktury -->
    <div class="mt-6">
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
            <tbody></tbody>
        </table>
        <button type="button" onclick="addRow()" class="mt-2 bg-gray-200 px-3 py-1 rounded">
            ➕ Přidat položku
        </button>
    </div>

    <div class="mt-4 text-right">
        <strong>Celková cena: </strong>
        <span id="grandTotal">0 Kč</span>
    </div>

    <button type="submit" class="mt-4 bg-pink-500 text-white px-4 py-2 rounded shadow hover:bg-pink-600">
        💾 Uložit fakturu
    </button>
</form>

<!-- JS pro načtení detailu -->
<script>
document.getElementById("orderSelect").addEventListener("change", function() {
    let orderId = this.value;
    if (!orderId) {
        document.getElementById("customerDetails").classList.add("hidden");
        document.getElementById("carrier").value = "";
        document.getElementById("carrier_address").value = "";
        return;
    }

    fetch(`/orders/${orderId}`)
        .then(res => res.json())
        .then(order => {
            // zákazník
            document.getElementById("custName").innerText = order.first_name + " " + order.last_name;
            document.getElementById("custEmail").innerText = order.email;
            document.getElementById("custPhone").innerText = order.phone ?? '';
            document.getElementById("custAddress").innerText = order.address ?? '';
            document.getElementById("custCity").innerText = order.city ?? '';
            document.getElementById("custZip").innerText = order.zip ?? '';
            document.getElementById("customerDetails").classList.remove("hidden");

            // dopravce
            document.getElementById("carrier").value = order.carrier ?? '';
            document.getElementById("carrier_address").value = order.carrier_address ?? '';

            // pokud je dopravce, rovnou přidej položku do faktury
            let tbody = document.querySelector("#itemsTable tbody");
            tbody.innerHTML = "";
            if (order.carrier) {
                addRow(order.carrier, 1, 0); // description = dopravce, qty=1, cena zatím 0
            }
        })
        .catch(err => console.error("Chyba při načítání objednávky:", err));
});
</script>

<script>
    function addRow(description = "", qty = 1, price = 0) {
    let tbody = document.querySelector("#itemsTable tbody");
    let index = tbody.querySelectorAll("tr").length;
    let newRow = document.createElement("tr");
    newRow.innerHTML = `
        <td class="border p-1">
            <input type="text" name="items[${index}][description]" value="${description}" 
                   class="w-full border rounded p-1" required>
        </td>
        <td class="border p-1">
            <input type="number" name="items[${index}][quantity]" value="${qty}" min="1" 
                   class="w-full border rounded p-1 quantity" required>
        </td>
        <td class="border p-1">
            <input type="number" step="0.01" name="items[${index}][unit_price]" value="${price}" 
                   class="w-full border rounded p-1 unit_price" required>
        </td>
        <td class="border p-1 text-center total">${(qty * price).toFixed(2)}</td>
        <td class="border p-1 text-center">
            <button type="button" onclick="removeRow(this)" class="text-red-500">✕</button>
        </td>
    `;
    tbody.appendChild(newRow);
    attachEvents(newRow);
    updateTotals();
}

</script>