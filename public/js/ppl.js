document.addEventListener("DOMContentLoaded", function () {
    const listbox = document.getElementById("listboxDopravce");
    const modal = document.getElementById("pplModal");
    const closeModal = document.getElementById("closePpl");

    const carrierIdInput = document.getElementById("carrier_id");
    const carrierAddressInput = document.getElementById("carrier_address");
    const selectedText = document.getElementById("pplSelected");

    function resetPpl() {
        carrierIdInput.value = "";
        carrierAddressInput.value = "";
        selectedText.textContent = "";
        selectedText.classList.add("hidden"); // ✅ schová badge
        modal.style.display = "none";
    }

    listbox.addEventListener("change", function () {
        if (listbox.value === "PplParcelshop") {
            modal.style.display = "block";
        } else {
            resetPpl();
        }
    });

    closeModal.addEventListener("click", function () {
        resetPpl();
    });

    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            resetPpl();
        }
    });

    // posluchač na výběr PPL výdejny
    document.addEventListener("ppl-parcelshop-map", function (event) {
        if (event.detail) {
            const detail = event.detail;

            carrierIdInput.value = detail.id || detail.code || "";
            carrierAddressInput.value = `${detail.name}, ${detail.street}, ${detail.city}`;

            selectedText.classList.remove("hidden"); // ✅ zobrazí badge
            selectedText.textContent = `📦 Vybráno PPL výdejna: ${carrierAddressInput.value}`;

            modal.style.display = "none";
        }
    });
});
