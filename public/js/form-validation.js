document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("main_form");
    const listbox = document.getElementById("listboxDopravce");
    const carrierIdInput = document.getElementById("carrier_id");
    const carrierAddressInput = document.getElementById("carrier_address");

    if (!form) return;

    form.addEventListener("submit", function (e) {
        const carrier = listbox.value;

        if (
            (carrier === "Balikovna" || carrier === "Zasilkovna" || carrier === "PplParcelshop") &&
            (carrierIdInput.value.trim() === "" ||
                carrierAddressInput.value.trim() === "")
        ) {
            e.preventDefault(); // stopne submit
            alert("⚠️ Musíte vybrat výdejní místo pro " + carrier + ".");
            // 🔥 vrátí tlačítko do původního stavu
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = "Odeslat";
            }
            return false;
        }
    });
});
