document.addEventListener("DOMContentLoaded", function () {
    const listbox = document.getElementById("listboxDopravce");
    const modal = document.getElementById("osobniModal");
    const closeBtn = document.getElementById("closeOsobni");

    listbox.addEventListener("change", function () {
        if (listbox.value === "osobni") {
            modal.classList.remove("hidden");
        } else {
            modal.classList.add("hidden");
        }
    });

    closeBtn.addEventListener("click", function () {
        modal.classList.add("hidden");
    });

    // zavření kliknutím mimo box
    modal.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.classList.add("hidden");
        }
    });
});
