const packetaApiKey = "28a9659f77109cf3"; // tvůj API klíč

document.addEventListener("DOMContentLoaded", function () {
    const carrierSelect = document.getElementById("listboxDopravce");
    const selectedPoint = document.getElementById("selectedPoint");

    const carrierIdInput = document.getElementById("carrier_id");
    const carrierAddressInput = document.getElementById("carrier_address");

    carrierSelect.addEventListener("change", function () {
        if (carrierSelect.value === "Zasilkovna") {
            Packeta.Widget.pick(packetaApiKey, function (point) {
                if (point) {
                    carrierIdInput.value = point.id;
                    carrierAddressInput.value = `${point.name}, ${point.city}, ${point.street}`;

                    selectedPoint.classList.remove("hidden"); // ✅ ukáže se
                    selectedPoint.textContent = `📍 Vybráno: ${point.name}, ${point.city}, ${point.street}`;

                    console.log(
                        "Uloženo:",
                        carrierIdInput.value,
                        carrierAddressInput.value
                    );
                } else {
                    carrierIdInput.value = "";
                    carrierAddressInput.value = "";
                    selectedPoint.textContent =
                        "⚠️ Nebylo vybráno žádné výdejní místo.";
                    selectedPoint.classList.remove("hidden"); // ať se ukáže i chyba
                }
            });
        } else {
            carrierIdInput.value = "";
            carrierAddressInput.value = "";
            selectedPoint.textContent = "";
        }
    });
});
