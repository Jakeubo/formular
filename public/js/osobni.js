document.addEventListener("DOMContentLoaded", function () {
    const listbox = document.getElementById("listboxDopravce");
    const modal = document.getElementById("osobniModal");
    const closeBtn = document.getElementById("closeOsobni");
    const carrierId = document.getElementById("carrier_id");
    const carrierAddress = document.getElementById("carrier_address");
    const osobniSelected = document.getElementById("osobniSelected");

    if (!listbox || !modal) return;

    listbox.addEventListener("change", function () {
        if (listbox.value === "osobni") {
            // zobraz modal
            modal.classList.remove("hidden");

            // vyplň hidden pole pro backend
            carrierId.value = "OSOBNI";
            carrierAddress.value = "SushiHub, Sokolská 123, 779 00 Olomouc";

            // zobraz potvrzení
            if (osobniSelected) osobniSelected.classList.remove("hidden");
        } else {
            modal.classList.add("hidden");

            // reset
            carrierId.value = "";
            carrierAddress.value = "";

            if (osobniSelected) osobniSelected.classList.add("hidden");
        }
    });

    // Zavření modalu kliknutím na křížek
    closeBtn?.addEventListener("click", function () {
        modal.classList.add("hidden");
    });

    // Zavření kliknutím mimo box
    modal.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.classList.add("hidden");
        }
    });
});
