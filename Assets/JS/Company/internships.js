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
    function initializeDeleteInternshipModal() {
        const modal = document.getElementById("deleteInternshipModal");
        const confirmButton = document.getElementById("confirmDeleteInternship");
        const deleteForms = document.querySelectorAll(".delete-form");

        if (!modal || !confirmButton || modal.dataset.modalInitialized === "true") return;
        modal.dataset.modalInitialized = "true";

        let pendingDeleteForm = null;
        let lastFocusedElement = null;

        function openModal(form) {
            pendingDeleteForm = form;
            lastFocusedElement = document.activeElement;
            modal.hidden = false;
            modal.setAttribute("aria-hidden", "false");
            document.body.classList.add("internship-modal-open");
            confirmButton.focus();
        }

        function closeModal() {
            modal.hidden = true;
            modal.setAttribute("aria-hidden", "true");
            document.body.classList.remove("internship-modal-open");
            pendingDeleteForm = null;

            if (lastFocusedElement && typeof lastFocusedElement.focus === "function") {
                lastFocusedElement.focus();
            }
        }

        deleteForms.forEach(function (form) {
            form.addEventListener("submit", function (event) {
                event.preventDefault();
                openModal(form);
            });
        });

        modal.querySelectorAll("[data-delete-modal-close]").forEach(function (closeButton) {
            closeButton.addEventListener("click", closeModal);
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape" && !modal.hidden) closeModal();
        });

        confirmButton.addEventListener("click", function () {
            if (!pendingDeleteForm) return;

            const form = pendingDeleteForm;
            closeModal();
            form.submit();
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initializeDeleteInternshipModal, { once: true });
    } else {
        initializeDeleteInternshipModal();
    }
})();

(function () {
    function initializeInternshipModal() {
        const modal = document.getElementById("internshipModal");
        const openButtons = document.querySelectorAll("#openInternshipModal, [data-open-internship-modal]");

        if (!modal || openButtons.length === 0 || modal.dataset.modalInitialized === "true") return;
        modal.dataset.modalInitialized = "true";

        let lastFocusedElement = null;

        function openModal() {
            lastFocusedElement = document.activeElement;
            modal.hidden = false;
            modal.setAttribute("aria-hidden", "false");
            document.body.classList.add("internship-modal-open");

            const firstInput = modal.querySelector("input:not([type='hidden'])");
            if (firstInput) firstInput.focus();
        }

        function closeModal() {
            modal.hidden = true;
            modal.setAttribute("aria-hidden", "true");
            document.body.classList.remove("internship-modal-open");

            if (lastFocusedElement && typeof lastFocusedElement.focus === "function") {
                lastFocusedElement.focus();
            }
        }

        openButtons.forEach(function (openButton) {
            openButton.addEventListener("click", openModal);
        });

        modal.querySelectorAll("[data-modal-close]").forEach(function (closeButton) {
            closeButton.addEventListener("click", closeModal);
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape" && !modal.hidden) closeModal();
        });

        if (!modal.hidden) document.body.classList.add("internship-modal-open");

        if (new URLSearchParams(window.location.search).get("open_add") === "1") {
            openModal();
            const url = new URL(window.location.href);
            url.searchParams.delete("open_add");
            window.history.replaceState({}, document.title, url.pathname + url.search + url.hash);
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initializeInternshipModal, { once: true });
    } else {
        initializeInternshipModal();
    }
})();

(function () {
    function initializeEditInternshipModal() {
        const modal = document.getElementById("editInternshipModal");
        const form = document.getElementById("editInternshipForm");
        const editButtons = document.querySelectorAll("[data-edit-internship]");

        if (!modal || !form || modal.dataset.modalInitialized === "true") return;
        modal.dataset.modalInitialized = "true";

        let lastFocusedElement = null;

        function openModal(internship) {
            if (internship) {
                form.elements.internship_id.value = internship.id || "";
                form.elements.title.value = internship.title || "";
                form.elements.industry.value = internship.industry || "";
                form.elements.duration.value = internship.duration || "";
                form.elements.tech_tags.value = internship.tech_tags || "";
                form.elements.deadline.value = internship.deadline || "";
            }

            lastFocusedElement = document.activeElement;
            modal.hidden = false;
            modal.setAttribute("aria-hidden", "false");
            document.body.classList.add("internship-modal-open");

            const titleInput = form.elements.title;
            if (titleInput) titleInput.focus();
        }

        function closeModal() {
            modal.hidden = true;
            modal.setAttribute("aria-hidden", "true");
            document.body.classList.remove("internship-modal-open");

            if (lastFocusedElement && typeof lastFocusedElement.focus === "function") {
                lastFocusedElement.focus();
            }
        }

        editButtons.forEach(function (editButton) {
            editButton.addEventListener("click", function () {
                try {
                    openModal(JSON.parse(editButton.dataset.editInternship));
                } catch (error) {
                    // The button is rendered by the server, so malformed data should not occur.
                }
            });
        });

        modal.querySelectorAll("[data-edit-modal-close]").forEach(function (closeButton) {
            closeButton.addEventListener("click", closeModal);
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape" && !modal.hidden) closeModal();
        });

        if (!modal.hidden) document.body.classList.add("internship-modal-open");
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initializeEditInternshipModal, { once: true });
    } else {
        initializeEditInternshipModal();
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


    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initializeInternshipControls, { once: true });
    } else {
        initializeInternshipControls();
    }
})();
