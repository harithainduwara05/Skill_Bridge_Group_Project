(function () {
    function initializeToast() {
        const toast = document.getElementById("companyToast");

        if (!toast || toast.dataset.toastInitialized === "true") {
            return;
        }

        toast.dataset.toastInitialized = "true";

        const toastClose = toast.querySelector(".toast-close");

        let toastTimer;
        let removalTimer;

        function removeToast() {
            window.clearTimeout(removalTimer);
            toast.remove();
        }

        function dismissToast() {
            if (toast.classList.contains("is-leaving")) {
                return;
            }

            window.clearTimeout(toastTimer);

            toast.classList.add("is-leaving");

            toast.addEventListener("animationend", function (event) {
                if (
                    event.target === toast &&
                    event.animationName === "companyToastOut"
                ) {
                    removeToast();
                }
            });

            removalTimer = window.setTimeout(removeToast, 300);
        }

        toastTimer = window.setTimeout(dismissToast, 4000);

        if (toastClose) {
            toastClose.addEventListener("click", dismissToast);
        }
    }

    initializeToast();

    if (document.readyState === "loading") {
        document.addEventListener(
            "DOMContentLoaded",
            initializeToast,
            { once: true }
        );
    }
})();

/* Admin suspension reason and correction dialog */
(function () {
    const modal = document.getElementById("adminReasonModal");
    if (!modal) return;
    const reasonText = document.getElementById("adminReasonText");
    const reasonTitle = document.getElementById("adminReasonTitle");
    const fixButton = document.getElementById("fixSuspendedInternship");
    let editButton = null;

    document.querySelectorAll("[data-admin-reason]").forEach(function (button) {
        button.addEventListener("click", function () {
            editButton = button.closest(".action-buttons")?.querySelector("[data-edit-internship]") || null;
            reasonText.textContent = button.dataset.adminReason || "No reason was provided by the administrator.";
            reasonTitle.textContent = button.dataset.reasonTitle || "Admin Reason";
            modal.hidden = false;
            modal.setAttribute("aria-hidden", "false");
            document.body.classList.add("internship-modal-open");
            fixButton.hidden = !editButton;
        });
    });

    function closeModal() {
        modal.hidden = true;
        modal.setAttribute("aria-hidden", "true");
        document.body.classList.remove("internship-modal-open");
    }

    modal.querySelectorAll("[data-reason-close]").forEach(function (button) {
        button.addEventListener("click", closeModal);
    });
    fixButton.addEventListener("click", function () {
        if (editButton) editButton.click();
        closeModal();
    });
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && !modal.hidden) closeModal();
    });
})();


/* =========================================
   DELETE INTERNSHIP MODAL
========================================= */

(function () {
    function initializeDeleteInternshipModal() {
        const modal = document.getElementById("deleteInternshipModal");
        const confirmButton = document.getElementById(
            "confirmDeleteInternship"
        );
        const deleteForms = document.querySelectorAll(".delete-form");
        const validationMessage = document.getElementById("deleteValidationMessage");

        if (
            !modal ||
            !confirmButton ||
            modal.dataset.modalInitialized === "true"
        ) {
            return;
        }

        modal.dataset.modalInitialized = "true";

        let pendingDeleteForm = null;
        let lastFocusedElement = null;

        function openModal(form) {
            pendingDeleteForm = form;
            if (validationMessage) validationMessage.hidden = true;
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

            if (
                lastFocusedElement &&
                typeof lastFocusedElement.focus === "function"
            ) {
                lastFocusedElement.focus();
            }
        }

        deleteForms.forEach(function (form) {
            form.addEventListener("submit", function (event) {
                event.preventDefault();

                const applicantCount = Number(form.dataset.applicantCount || 0);
                const status = (form.dataset.status || "").toLowerCase();
                let blockedMessage = "";

                if (status === "suspended") {
                    blockedMessage = "Suspended internships cannot be deleted while under review.";
                } else if (applicantCount >= 1 && status !== "terminated") {
                    blockedMessage = "This internship cannot be deleted because applications have already been submitted.";
                }

                if (blockedMessage) {
                    if (validationMessage) {
                        validationMessage.textContent = blockedMessage;
                        validationMessage.hidden = false;
                    }
                    return;
                }

                openModal(form);
            });
        });

        modal
            .querySelectorAll("[data-delete-modal-close]")
            .forEach(function (button) {
                button.addEventListener("click", closeModal);
            });

        document.addEventListener("keydown", function (event) {
            if (
                event.key === "Escape" &&
                !modal.hidden
            ) {
                closeModal();
            }
        });

        confirmButton.addEventListener("click", function () {
            if (!pendingDeleteForm) {
                return;
            }

            const form = pendingDeleteForm;

            closeModal();

            form.submit();
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener(
            "DOMContentLoaded",
            initializeDeleteInternshipModal,
            { once: true }
        );
    } else {
        initializeDeleteInternshipModal();
    }
})();


/* =========================================
   ADD INTERNSHIP MODAL
========================================= */

(function () {
    function initializeInternshipModal() {
        const modal = document.getElementById("internshipModal");

        const openButtons = document.querySelectorAll(
            "#openInternshipModal, [data-open-internship-modal]"
        );

        if (
            !modal ||
            openButtons.length === 0 ||
            modal.dataset.modalInitialized === "true"
        ) {
            return;
        }

        modal.dataset.modalInitialized = "true";

        let lastFocusedElement = null;

        function openModal() {
            lastFocusedElement = document.activeElement;

            modal.hidden = false;
            modal.setAttribute("aria-hidden", "false");

            document.body.classList.add("internship-modal-open");

            const firstInput = modal.querySelector(
                "input:not([type='hidden'])"
            );

            if (firstInput) {
                firstInput.focus();
            }
        }

        function closeModal() {
            modal.hidden = true;
            modal.setAttribute("aria-hidden", "true");

            document.body.classList.remove("internship-modal-open");

            if (
                lastFocusedElement &&
                typeof lastFocusedElement.focus === "function"
            ) {
                lastFocusedElement.focus();
            }
        }

        openButtons.forEach(function (button) {
            button.addEventListener("click", openModal);
        });

        modal
            .querySelectorAll("[data-modal-close]")
            .forEach(function (button) {
                button.addEventListener("click", closeModal);
            });

        document.addEventListener("keydown", function (event) {
            if (
                event.key === "Escape" &&
                !modal.hidden
            ) {
                closeModal();
            }
        });

        if (!modal.hidden) {
            document.body.classList.add("internship-modal-open");
        }

        const params = new URLSearchParams(
            window.location.search
        );

        if (params.get("open_add") === "1") {
            openModal();

            const url = new URL(window.location.href);

            url.searchParams.delete("open_add");

            window.history.replaceState(
                {},
                document.title,
                url.pathname + url.search + url.hash
            );
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener(
            "DOMContentLoaded",
            initializeInternshipModal,
            { once: true }
        );
    } else {
        initializeInternshipModal();
    }
})();


/* =========================================
   EDIT INTERNSHIP MODAL
========================================= */

(function () {
    function initializeEditInternshipModal() {
        const modal = document.getElementById(
            "editInternshipModal"
        );

        const form = document.getElementById(
            "editInternshipForm"
        );

        const editButtons = document.querySelectorAll(
            "[data-edit-internship]"
        );
        const statusGroup = document.getElementById("editStatusGroup");
        const readonlyStatus = document.getElementById("edit_status_readonly");

        if (
            !modal ||
            !form ||
            modal.dataset.modalInitialized === "true"
        ) {
            return;
        }

        modal.dataset.modalInitialized = "true";

        let lastFocusedElement = null;

        function setValue(name, value) {
            const field = form.elements[name];

            if (!field) {
                return;
            }

            field.value = value ?? "";
        }

        function syncEditStipend() {
            const paidStatus = form.elements.paid_status;
            const stipend = form.elements.stipend;

            if (!paidStatus || !stipend) {
                return;
            }

            const isPaid = paidStatus.value === "Paid";

            stipend.disabled = !isPaid;

            if (!isPaid) {
                stipend.value = "";
            }
        }

        function syncEditStatusDeadline() {
            const deadline = form.elements.deadline;
            const status = form.elements.status;
            if (!deadline || !status) return;

            const activeOption = Array.from(status.options).find(function (option) {
                return option.value === "Active";
            });
            if (!activeOption) return;

            const now = new Date();
            const today = [now.getFullYear(), String(now.getMonth() + 1).padStart(2, "0"), String(now.getDate()).padStart(2, "0")].join("-");
            activeOption.disabled = !deadline.value || deadline.value < today;
            if (activeOption.disabled && status.value === "Active") status.value = "Closed";
        }

        function openModal(internship) {
            if (internship) {

                /* IMPORTANT FIX:
                   JSON uses "id"
                   hidden input uses "internship_id"
                */
                if (form.elements.internship_id) {
                    form.elements.internship_id.value =
                        internship.id || "";
                }

                const fields = [
                    "title",
                    "industry",
                    "description",
                    "tech_tags",
                    "academic_year",
                    "experience_level",
                    "vacancies",
                    "duration",
                    "internship_type",
                    "work_mode",
                    "location",
                    "start_date",
                    "deadline",
                    "paid_status",
                    "stipend",
                    "responsibilities",
                    "benefits",
                    "status"
                ];

                fields.forEach(function (name) {
                    setValue(
                        name,
                        internship[name]
                    );
                });

                const statusInput = form.elements.status;
                const statusIsCompanyEditable = ["Active", "Closed"].includes(internship.status);
                const statusIsSuspended = internship.status === "Suspended";
                if (statusGroup) statusGroup.hidden = !statusIsCompanyEditable && !statusIsSuspended;
                if (statusInput) {
                    statusInput.hidden = !statusIsCompanyEditable;
                    statusInput.disabled = !statusIsCompanyEditable;
                }
                if (readonlyStatus) readonlyStatus.hidden = !statusIsSuspended;
                syncEditStatusDeadline();
            }

            lastFocusedElement =
                document.activeElement;

            modal.hidden = false;

            modal.setAttribute(
                "aria-hidden",
                "false"
            );

            document.body.classList.add(
                "internship-modal-open"
            );

            syncEditStipend();

            if (form.elements.title) {
                form.elements.title.focus();
            }
        }

        function closeModal() {
            modal.hidden = true;

            modal.setAttribute(
                "aria-hidden",
                "true"
            );

            document.body.classList.remove(
                "internship-modal-open"
            );

            if (
                lastFocusedElement &&
                typeof lastFocusedElement.focus === "function"
            ) {
                lastFocusedElement.focus();
            }
        }

        editButtons.forEach(function (button) {
            button.addEventListener(
                "click",
                function () {
                    try {
                        const internship =
                            JSON.parse(
                                button.dataset
                                    .editInternship
                            );

                        openModal(internship);

                    } catch (error) {
                        console.error(
                            "Unable to load internship details.",
                            error
                        );
                    }
                }
            );
        });

        if (form.elements.paid_status) {
            form.elements.paid_status.addEventListener(
                "change",
                syncEditStipend
            );
        }

        if (form.elements.deadline) {
            form.elements.deadline.addEventListener("input", syncEditStatusDeadline);
            form.elements.deadline.addEventListener("change", syncEditStatusDeadline);
        }

        modal
            .querySelectorAll(
                "[data-edit-modal-close]"
            )
            .forEach(function (button) {
                button.addEventListener(
                    "click",
                    closeModal
                );
            });

        document.addEventListener(
            "keydown",
            function (event) {
                if (
                    event.key === "Escape" &&
                    !modal.hidden
                ) {
                    closeModal();
                }
            }
        );

        if (!modal.hidden) {
            document.body.classList.add(
                "internship-modal-open"
            );

            syncEditStipend();
            syncEditStatusDeadline();
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener(
            "DOMContentLoaded",
            initializeEditInternshipModal,
            { once: true }
        );
    } else {
        initializeEditInternshipModal();
    }
})();


/* =========================================
   SEARCH + STATUS FILTER
========================================= */

(function () {
    function initializeInternshipControls() {
        const searchInput =
            document.getElementById(
                "internshipSearch"
            );

        const statusFilter =
            document.getElementById(
                "statusFilter"
            );

        const rows =
            document.querySelectorAll(
                ".internship-row"
            );

        function filterInternships() {
            const searchValue =
                searchInput
                    ? searchInput.value
                        .toLowerCase()
                        .trim()
                    : "";

            const statusValue =
                statusFilter
                    ? statusFilter.value
                    : "all";

            rows.forEach(function (row) {
                const text =
                    row.innerText.toLowerCase();

                const rowStatus =
                    row.dataset.status;

                const matchesSearch =
                    text.includes(searchValue);

                const matchesStatus =
                    statusValue === "all" ||
                    rowStatus === statusValue;

                row.style.display =
                    matchesSearch &&
                    matchesStatus
                        ? ""
                        : "none";
            });
        }

        if (searchInput) {
            searchInput.addEventListener(
                "input",
                filterInternships
            );
        }

        if (statusFilter) {
            statusFilter.addEventListener(
                "change",
                filterInternships
            );
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener(
            "DOMContentLoaded",
            initializeInternshipControls,
            { once: true }
        );
    } else {
        initializeInternshipControls();
    }
})();


/* =========================================
   ADD FORM - PAID / UNPAID
========================================= */

(function () {
    function initializeAddPaidField() {
        const status =
            document.getElementById(
                "paid_status"
            );

        const stipend =
            document.getElementById(
                "stipend"
            );

        if (!status || !stipend) {
            return;
        }

        function sync() {
            const isPaid =
                status.value === "Paid";

            stipend.disabled = !isPaid;

            if (!isPaid) {
                stipend.value = "";
            }
        }

        status.addEventListener(
            "change",
            sync
        );

        sync();
    }

    if (document.readyState === "loading") {
        document.addEventListener(
            "DOMContentLoaded",
            initializeAddPaidField,
            { once: true }
        );
    } else {
        initializeAddPaidField();
    }
})();
