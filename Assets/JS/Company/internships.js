(function () {

    function initializeToast() {

    const toast = document.getElementById("companyToast");
    if (!toast || toast.dataset.toastInitialized === "true") return;
    toast.dataset.toastInitialized = "true";
    const toastClose = toast ? toast.querySelector(".toast-close") : null;
    let toastTimer;
    let removalTimer;

    function removeToast() {
        window.clearTimeout(removalTimer);
        toast.remove();
    }

    function dismissToast() {
        if (!toast || toast.classList.contains("is-leaving")) return;
        window.clearTimeout(toastTimer);
        toast.classList.add("is-leaving");
        toast.addEventListener("animationend", function (event) {
            if (event.target === toast && event.animationName === "companyToastOut") {
                removeToast();
            }
        });
        // Still remove the card when animations are disabled or interrupted.
        removalTimer = window.setTimeout(removeToast, 300);
    }

    if (toast) {
        toastTimer = window.setTimeout(dismissToast, 4000);
    }

    if (toastClose) {
        toastClose.addEventListener("click", dismissToast);
    }
    }

    // The page loads this script after the toast markup. Do not wait for
    // unrelated page resources or a DOMContentLoaded event that already fired.
    initializeToast();
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initializeToast, { once: true });
    }
})();

(function () {
    function initializeInternshipControls() {

    const searchInput = document.getElementById("internshipSearch");
    const statusFilter = document.getElementById("statusFilter");
    const rows = document.querySelectorAll(".internship-row");


    function filterInternships() {

        const searchValue = searchInput
            ? searchInput.value.toLowerCase().trim()
            : "";

        const statusValue = statusFilter
            ? statusFilter.value
            : "all";


        rows.forEach(function (row) {

            const text = row.innerText.toLowerCase();
            const rowStatus = row.dataset.status;

            const matchesSearch = text.includes(searchValue);

            const matchesStatus =
                statusValue === "all" ||
                rowStatus === statusValue;


            if (matchesSearch && matchesStatus) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }

        });

    }


    if (searchInput) {
        searchInput.addEventListener("input", filterInternships);
    }


    if (statusFilter) {
        statusFilter.addEventListener("change", filterInternships);
    }


    const deleteForms = document.querySelectorAll(".delete-form");


    deleteForms.forEach(function (form) {

        form.addEventListener("submit", function (event) {

            const confirmed = confirm(
                "Are you sure you want to delete this internship?"
            );

            if (!confirmed) {
                event.preventDefault();
            }

        });

    });

    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initializeInternshipControls, { once: true });
    } else {
        initializeInternshipControls();
    }
})();
