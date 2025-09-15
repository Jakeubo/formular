document.addEventListener("DOMContentLoaded", function () {
    const listbox = document.getElementById("listboxDopravce");

    const carrierIdInput = document.getElementById("carrier_id");
    const carrierAddressInput = document.getElementById("carrier_address");

    // prvky pro reset
    const osobniSection = document.getElementById("osobniSection");
    const balikovnaModal = document.getElementById("balikovnaModal");
    const balikovnaSelected = document.getElementById("balikovnaSelected");
    const zasilkovnaSelected = document.getElementById("selectedPoint"); 
    const pplModal = document.getElementById("pplModal");
    const pplSelected = document.getElementById("pplSelected");

    function resetDeliveryUI() {
        // Skryj všechny sekce / modaly
        if (osobniSection) osobniSection.classList.add("hidden");
        if (balikovnaModal) balikovnaModal.classList.add("hidden");
        if (pplModal) pplModal.classList.add("hidden");

        // Vymaž texty
        if (balikovnaSelected) balikovnaSelected.textContent = "";
        if (zasilkovnaSelected) zasilkovnaSelected.textContent = "";
        if (pplSelected) pplSelected.textContent = "";

        // Vymaž inputy
        carrierIdInput.value = "";
        carrierAddressInput.value = "";
    }

    // Reset + spuštění správného widgetu podle dopravce
    listbox.addEventListener("change", function () {
        resetDeliveryUI();

        if (listbox.value === "osobni") {
            osobniSection.classList.remove("hidden");
            carrierIdInput.value = "osobni";
            carrierAddressInput.value = "SushiHub, Sokolská 123, 779 00 Olomouc";
        }

        if (listbox.value === "Balikovna") {
            balikovnaModal.classList.remove("hidden");
            document.getElementById("balikovnaIframe").src = "https://b2c.cpost.cz/locations/?type=BALIKOVNY";
        }

        if (listbox.value === "Zasilkovna") {
            Packeta.Widget.pick(packetaApiKey, function (point) {
                if (point) {
                    carrierIdInput.value = point.id;
                    carrierAddressInput.value = `${point.name}, ${point.city}, ${point.street}`;
                    zasilkovnaSelected.textContent = `Vybráno: ${point.name}, ${point.city}, ${point.street}`;
                }
            });
        }

        if (listbox.value === "PplParcelshop") {
            pplModal.classList.remove("hidden");
        }

        if (listbox.value === "Ppl") {
            // u PPL na adresu není modal → jen vyplní políčka v HTML formuláři
        }
    });

    // posluchač pro PPL výdejnu
    document.addEventListener("ppl-parcelshop-map", function (event) {
        if (event.detail) {
            const detail = event.detail;
            carrierIdInput.value = detail.id || detail.code || "";
            carrierAddressInput.value = `${detail.name}, ${detail.street}, ${detail.city}`;
            if (pplSelected) pplSelected.textContent = `Vybráno PPL výdejna: ${carrierAddressInput.value}`;
            if (pplModal) pplModal.classList.add("hidden");
        }
    });
});
