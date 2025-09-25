document.addEventListener("DOMContentLoaded", function () {
    const listbox = document.getElementById("listboxDopravce");
    const modal = document.getElementById("balikovnaModal");
    const box = document.getElementById("balikovnaBox");
    const iframe = document.getElementById("balikovnaIframe");
    const closeBtn = document.getElementById("closeBalikovna");

    const carrierIdInput = document.getElementById("carrier_id");
    const carrierAddressInput = document.getElementById("carrier_address");
    const selectedText = document.getElementById("balikovnaSelected");

    // otevře modal
    listbox.addEventListener("change", function () {
        if (listbox.value === "Balikovna") {
            iframe.src = "https://b2c.cpost.cz/locations/?type=BALIKOVNY";
            modal.classList.remove("hidden");
        }
    });

    // zavře modal (společná funkce)
    function hideModal(reset = false) {
        modal.classList.add("hidden");
        if (reset) iframe.src = "";
    }

    // zavření křížkem
    closeBtn.addEventListener("click", () => hideModal(false));

    // zavření kliknutím mimo box
    modal.addEventListener("click", (event) => {
        if (!box.contains(event.target)) {
            hideModal(false);
        }
    });

    // zpráva z iframe (vybraná pobočka)
    window.addEventListener("message", function (event) {
        if (event.data.message === "pickerResult" && event.data.point) {
            const point = event.data.point;
            carrierIdInput.value = point.id;
            carrierAddressInput.value = point.address;

            selectedText.classList.remove("hidden"); // ✅ ukáže se
            selectedText.textContent = `📦 Vybráno: ${point.name}, ${point.address}`;

            hideModal(true); // zavřít + reset iframe
        }
    });

    // ochrana proti dvojímu submitu
    const form = document.getElementById("main_form");
    const submitBtn = document.getElementById("submitBtn");
    form.addEventListener("submit", function () {
        submitBtn.disabled = true;
        submitBtn.innerText = "Odesílám...";
    });
});
