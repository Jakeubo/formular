<form method="POST" action="{{ route('invoices.store') }}" id="invoiceForm">
    @csrf

    <div class="grid grid-cols-2 gap-6">
        <!-- Levý sloupec -->
        <div>
            <!-- Zákazník -->
            <div class="mb-4 flex items-center gap-2">
                <div class="flex-1">
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
                <!-- Lupa -->
                <button type="button" id="showCustomerBtn"
                    class="mt-6 px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">
                    🔍
                </button>
            </div>

            <!-- Kontaktní údaje zákazníka (skryté) -->
            <div id="customerDetails" class="hidden border p-3 rounded bg-gray-50 mb-4">
                <p><strong>Jméno:</strong> <span id="custName"></span></p>
                <p><strong>Email:</strong> <span id="custEmail"></span></p>
                <p><strong>Telefon:</strong> <span id="custPhone"></span></p>
                <p><strong>Adresa:</strong> <span id="custAddress"></span></p>
                <p><strong>Město:</strong> <span id="custCity"></span></p>
                <p><strong>PSČ:</strong> <span id="custZip"></span></p>
            </div>

            <!-- Dopravce -->
            <!-- <div class="mb-4">
        <label class="block font-medium">Dopravce</label>
        <input type="text" name="carrier" id="carrier"
               class="w-full border rounded p-2 bg-gray-100" readonly>
    </div> -->

            <!-- Výdejní místo -->
            <!-- <div class="mb-4">
        <label class="block font-medium">Výdejní místo / adresa</label>
        <input type="text" name="carrier_address" id="carrier_address"
               class="w-full border rounded p-2 bg-gray-100" readonly>
    </div> -->
        </div>

        <!-- Pravý sloupec -->
        <!-- Číslo faktury -->
        <div class="mb-4">
            <label class="block font-medium">Číslo faktury</label>
            <input type="text" name="invoice_number"
                value="{{ 'FA ' . now()->format('Ymd') . '01' }}"
                class="w-full border rounded p-2 bg-gray-100" disabled>
        </div>

        <!-- Dopravce -->
        <div class="mb-4">
            <label class="block font-medium">Dopravce</label>
            <input type="text" name="carrier" id="carrier"
                class="w-full border rounded p-2 bg-gray-100" readonly>
        </div>

        <!-- Výdejní místo -->
        <div class="mb-4">
            <label class="block font-medium">Výdejní místo / adresa</label>
            <input type="text" name="carrier_address" id="carrier_address"
                class="w-full border rounded p-2 bg-gray-100" readonly>
        </div>

        <!-- Variabilní symbol -->
        <div class="mb-4">
            <label class="block font-medium">Variabilní symbol</label>
            <input type="text" name="variable_symbol"
                value="{{ now()->format('Ymd') . '01' }}"
                class="w-full border rounded p-2 bg-gray-100" readonly>

        </div>

        <!-- Datum vystavení -->
        <div class="mb-4">
            <label class="block font-medium">Datum vystavení</label>
            <input type="date" name="issue_date"
                value="{{ now()->toDateString() }}"
                class="w-full border rounded p-2" required>
        </div>

        <!-- Stav faktury -->
        <div class="mb-4">
            <label class="block font-medium">Stav faktury</label>
            <input type="text" name="status"
                value="new"
                class="w-full border rounded p-2 bg-gray-100" disabled>
        </div>

        <!-- Datum splatnosti -->
        <div class="mb-4">
            <label class="block font-medium">Datum splatnosti</label>
            <input type="date" name="due_date"
                value="{{ now()->addDays(14)->toDateString() }}"
                class="w-full border rounded p-2" required>
        </div>

    </div>


    <!-- Položky faktury -->
    <div class="mt-6">
        <label class="block font-medium mb-2">Položky faktury</label>
        <table class="w-full border" id="itemsTable">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-2 py-1 border w-20 text-center">Kusy</th>
                    <th class="px-2 py-1 border">Název položky</th>
                    <th class="px-2 py-1 border w-32 text-center">Cena/ks</th>
                    <th class="px-2 py-1 border w-12"></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <button type="button" onclick="addRow()"
            class="mt-2 bg-gray-200 text-gray-700 px-3 py-1 rounded w-full text-sm hover:bg-gray-300">
            ➕ Přidat položku
        </button>
    </div>


    <!-- Celková cena -->
    <div class="mt-4 text-right">
        <strong>Celková cena: </strong>
        <span id="grandTotal">0 Kč</span>
    </div>

    <button type="submit"
        class="mt-4 bg-pink-500 text-white px-4 py-2 rounded shadow hover:bg-pink-600">
        💾 Uložit fakturu
    </button>
</form>

<!-- JS pro načtení detailu objednávky -->
<script>
document.getElementById("orderSelect").addEventListener("change", function() {
    let orderId = this.value;
    if (!orderId) {
        document.getElementById("carrier").value = "";
        document.getElementById("carrier_address").value = "";
        return;
    }

    fetch(`/orders/${orderId}`)
        .then(res => res.json())
        .then(order => {
            // 👉 sem to patří
            document.getElementById("carrier").value = order.carrier ?? '';
            document.getElementById("carrier_address").value = order.carrier_address ?? '';

            // další logika (např. položky)
            let tbody = document.querySelector("#itemsTable tbody");
            tbody.innerHTML = "";

            if (order.carrier) {
                addRow(1, order.carrier, 0);
                addRow();
            } else {
                addRow();
            }
        })
        .catch(err => console.error("Chyba při načítání objednávky:", err));
});
</script>



<!-- JS pro načtení detailu -->
<script>
    function updateTotals() {
        let grandTotal = 0;
        document.querySelectorAll("#itemsTable tbody tr").forEach(row => {
            let qty = parseFloat(row.querySelector(".quantity").value) || 0;
            let unitPrice = parseFloat(row.querySelector(".unit_price").value) || 0;
            grandTotal += qty * unitPrice;
        });
        document.getElementById("grandTotal").innerText = grandTotal.toFixed(2) + " Kč";
    }

    function addRow(qty = 1, description = "", price = 0) {
        let tbody = document.querySelector("#itemsTable tbody");
        let index = tbody.querySelectorAll("tr").length;
        let newRow = document.createElement("tr");
        newRow.innerHTML = `
            <td class="border p-1 text-center w-20">
                <input type="number" name="items[${index}][quantity]" value="${qty}" min="1"
                       class="border rounded p-1 w-full quantity text-center" required>
            </td>
            <td class="border p-1">
                <input type="text" name="items[${index}][description]" value="${description}"
                       class="border rounded p-1 w-full" required>
            </td>
            <td class="border p-1 text-center w-32">
                <input type="number" step="0.01" name="items[${index}][unit_price]" value="${price}"
                       class="border rounded p-1 w-full unit_price text-center" required>
            </td>
            <td class="border p-1 text-center w-12">
                <button type="button" onclick="removeRow(this)" class="text-red-500">✕</button>
            </td>
        `;
        tbody.appendChild(newRow);
        attachEvents(newRow);
        updateTotals();
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

    // ✅ Při otevření modalu rovnou přidat první řádek
    document.addEventListener("DOMContentLoaded", () => {
        addRow();
    });
</script>

<!-- Js pro lupu -->
<script>
    document.getElementById("showCustomerBtn").addEventListener("click", function() {
        let orderId = document.getElementById("orderSelect").value;
        let detailsBox = document.getElementById("customerDetails");

        // Pokud už je vidět → schováme a končíme
        if (!detailsBox.classList.contains("hidden")) {
            detailsBox.classList.add("hidden");
            return;
        }

        if (!orderId) {
            alert("Nejdřív vyberte zákazníka.");
            return;
        }

        fetch(`/orders/${orderId}`)
            .then(res => res.json())
            .then(order => {
                document.getElementById("custName").innerText = order.first_name + " " + order.last_name;
                document.getElementById("custEmail").innerText = order.email;
                document.getElementById("custPhone").innerText = order.phone ?? '';
                document.getElementById("custAddress").innerText = order.address ?? '';
                document.getElementById("custCity").innerText = order.city ?? '';
                document.getElementById("custZip").innerText = order.zip ?? '';
                detailsBox.classList.remove("hidden");
            })
            .catch(err => console.error("Chyba při načítání objednávky:", err));
    });
</script>