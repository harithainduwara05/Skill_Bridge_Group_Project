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

/* Optional-note reactivation request dialog */
(function () {
    const modal = document.getElementById("reactivationRequestModal");
    const form = document.getElementById("reactivationRequestForm");
    const internshipId = document.getElementById("reactivationInternshipId");
    const note = document.getElementById("reactivationRequestNote");
    const submitButton = document.getElementById("sendReactivationRequest");
    if (!modal || !form || !internshipId) return;

    let previouslyFocused = null;
    function openModal(button) {
        previouslyFocused = button;
        internshipId.value = button.dataset.internshipId || "";
        if (note) note.value = "";
        modal.hidden = false;
        modal.setAttribute("aria-hidden", "false");
        document.body.classList.add("internship-modal-open");
        modal.querySelector("textarea")?.focus();
    }

    function closeModal() {
        modal.hidden = true;
        modal.setAttribute("aria-hidden", "true");
        document.body.classList.remove("internship-modal-open");
        previouslyFocused?.focus();
    }

    document.querySelectorAll("[data-open-reactivation-modal]").forEach(function (button) {
        button.addEventListener("click", function () { openModal(button); });
    });
    modal.querySelectorAll("[data-reactivation-close]").forEach(function (button) {
        button.addEventListener("click", closeModal);
    });
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && !modal.hidden) closeModal();
    });
    form.addEventListener("submit", function (event) {
        if (!internshipId.value) {
            event.preventDefault();
            return;
        }
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.setAttribute("aria-disabled", "true");
        }
    });
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
   DELETE INTERNSHIP MODAL & BLOCKED RESTRICTION MODAL
========================================= */

(function () {
    function initializeDeleteInternshipModal() {
        const modal = document.getElementById("deleteInternshipModal");
        const confirmButton = document.getElementById(
            "confirmDeleteInternship"
        );
        const deleteForms = document.querySelectorAll(".delete-form");
        const validationMessage = document.getElementById("deleteValidationMessage");

        // Blocked restriction modal elements
        const blockedModal = document.getElementById("deleteBlockedModal");
        const blockedIcon = document.getElementById("deleteBlockedIcon");
        const blockedTag = document.getElementById("deleteBlockedTag");
        const blockedTitle = document.getElementById("deleteBlockedModalTitle");
        const blockedSubtitle = document.getElementById("deleteBlockedModalSubtitle");
        const blockedInternshipTitle = document.getElementById("deleteBlockedInternshipTitle");
        const blockedApplicantBadge = document.getElementById("deleteBlockedApplicantBadge");
        const blockedApplicantCount = document.getElementById("deleteBlockedApplicantCount");
        const blockedStatusBadge = document.getElementById("deleteBlockedStatusBadge");
        const blockedReasonTitle = document.getElementById("deleteBlockedReasonTitle");
        const blockedReasonText = document.getElementById("deleteBlockedReasonText");
        const blockedTipBox = document.getElementById("deleteBlockedTipBox");
        const blockedTipText = document.getElementById("deleteBlockedTipText");
        const blockedEditBtn = document.getElementById("deleteBlockedEditBtn");
        const blockedEditBtnText = document.getElementById("deleteBlockedEditBtnText");

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
        let activeBlockedInternshipId = null;

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

        function openBlockedModal(data) {
            if (!blockedModal) return;
            if (validationMessage) validationMessage.hidden = true;
            lastFocusedElement = document.activeElement;
            activeBlockedInternshipId = data.id || null;

            if (blockedTitle) blockedTitle.textContent = data.title || "Cannot Delete Internship";
            if (blockedSubtitle) blockedSubtitle.textContent = data.subtitle || "This internship posting is protected by platform data policies.";
            if (blockedInternshipTitle) blockedInternshipTitle.textContent = data.internshipTitle || "Internship Opportunity";
            
            if (blockedApplicantCount) blockedApplicantCount.textContent = data.applicantCount || 0;
            if (blockedApplicantBadge) {
                blockedApplicantBadge.hidden = (data.applicantCount === 0 && data.reasonType === "suspended");
            }

            if (blockedStatusBadge) {
                const s = data.status || "Active";
                blockedStatusBadge.textContent = s;
                blockedStatusBadge.className = "delete-blocked-status-badge status " + s.toLowerCase();
            }

            if (blockedIcon) {
                blockedIcon.textContent = data.icon || (data.reasonType === "suspended" ? "gavel" : "shield_lock");
            }
            if (blockedTag) {
                blockedTag.textContent = data.tag || (data.reasonType === "suspended" ? "Under Review" : "Action Restricted");
            }

            if (blockedReasonTitle) blockedReasonTitle.textContent = data.reasonTitle || "Why is this action restricted?";
            if (blockedReasonText) blockedReasonText.textContent = data.reasonText || "This internship cannot be deleted.";

            if (blockedTipBox && blockedTipText) {
                if (data.recommendationText) {
                    blockedTipBox.hidden = false;
                    blockedTipText.innerHTML = data.recommendationText;
                } else {
                    blockedTipBox.hidden = true;
                }
            }

            if (blockedEditBtn) {
                const rowEditBtn = activeBlockedInternshipId 
                    ? document.querySelector(`.edit-action[data-internship-id="${activeBlockedInternshipId}"]`)
                    : null;
                
                if (rowEditBtn) {
                    blockedEditBtn.hidden = false;
                    if (blockedEditBtnText) {
                        blockedEditBtnText.textContent = data.reasonType === "suspended" 
                            ? "Fix / Update Internship" 
                            : "Edit Internship / Change Status";
                    }
                } else {
                    blockedEditBtn.hidden = true;
                }
            }

            blockedModal.hidden = false;
            blockedModal.setAttribute("aria-hidden", "false");
            document.body.classList.add("internship-modal-open");
        }

        function closeBlockedModal() {
            if (!blockedModal) return;
            blockedModal.hidden = true;
            blockedModal.setAttribute("aria-hidden", "true");
            document.body.classList.remove("internship-modal-open");
            activeBlockedInternshipId = null;

            if (
                lastFocusedElement &&
                typeof lastFocusedElement.focus === "function"
            ) {
                lastFocusedElement.focus();
            }
        }

        if (blockedModal) {
            blockedModal
                .querySelectorAll("[data-delete-blocked-close]")
                .forEach(function (button) {
                    button.addEventListener("click", closeBlockedModal);
                });

            if (blockedEditBtn) {
                blockedEditBtn.addEventListener("click", function () {
                    const idToEdit = activeBlockedInternshipId;
                    closeBlockedModal();
                    if (idToEdit) {
                        const targetEditBtn = document.querySelector(`.edit-action[data-internship-id="${idToEdit}"]`);
                        if (targetEditBtn) {
                            targetEditBtn.click();
                        }
                    }
                });
            }
        }

        deleteForms.forEach(function (form) {
            form.addEventListener("submit", function (event) {
                event.preventDefault();

                const applicantCount = Number(form.dataset.applicantCount || 0);
                const status = (form.dataset.status || "").toLowerCase();
                const internshipId = form.dataset.internshipId || form.querySelector('input[name="internship_id"]')?.value || "";
                const internshipTitle = form.dataset.internshipTitle || form.closest("tr")?.querySelector(".internship-title-cell h3, td:first-child strong, td:first-child")?.textContent?.trim() || "Internship Opportunity";

                if (status === "suspended") {
                    openBlockedModal({
                        id: internshipId,
                        internshipTitle: internshipTitle,
                        title: "Cannot Delete Suspended Internship",
                        subtitle: "Internships under review cannot be removed.",
                        icon: "gavel",
                        tag: "Suspended Listing",
                        status: "Suspended",
                        applicantCount: applicantCount,
                        reasonType: "suspended",
                        reasonTitle: "Administrative Policy Hold",
                        reasonText: "This internship has been suspended by the administrator and cannot be deleted while under review.",
                        recommendationText: "Update the listing details in the edit window or submit a reactivation request."
                    });
                    return;
                }

                if (applicantCount >= 1 && status !== "terminated") {
                    openBlockedModal({
                        id: internshipId,
                        internshipTitle: internshipTitle,
                        title: "Cannot Delete Internship",
                        subtitle: "Candidate applications have already been submitted.",
                        icon: "shield_lock",
                        tag: "Candidate Data Protected",
                        status: status.charAt(0).toUpperCase() + status.slice(1),
                        applicantCount: applicantCount,
                        reasonType: "applicants",
                        reasonTitle: "Why is this deletion restricted?",
                        reasonText: "This internship already has " + applicantCount + " student application" + (applicantCount === 1 ? "" : "s") + " submitted. Deleting it would remove candidate records and review history.",
                        recommendationText: "Change the status to <strong>Closed</strong> to unlist from students while preserving all applicant evaluations."
                    });
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
            if (event.key === "Escape") {
                if (modal && !modal.hidden) {
                    closeModal();
                }
                if (blockedModal && !blockedModal.hidden) {
                    closeBlockedModal();
                }
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

        // Check if page loaded with ?notice=delete_blocked
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get("notice") === "delete_blocked") {
            openBlockedModal({
                id: "",
                internshipTitle: "Internship Opportunity",
                title: "Cannot Delete Internship",
                subtitle: "Candidate applications have already been submitted.",
                icon: "shield_lock",
                tag: "Candidate Data Protected",
                status: "Active",
                applicantCount: 1,
                reasonType: "applicants",
                reasonTitle: "Why is this deletion restricted?",
                reasonText: "This internship has active applicant submissions. Deletion is prevented to ensure candidate application histories are not permanently erased.",
                recommendationText: "Change the status to <strong>Closed</strong> in the edit window to stop receiving applications while protecting applicant histories."
            });

            const cleanUrl = new URL(window.location.href);
            cleanUrl.searchParams.delete("notice");
            window.history.replaceState({}, document.title, cleanUrl.pathname + cleanUrl.search + cleanUrl.hash);
        }
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

                const locationSelect = form.elements.location;
                const locationOther = form.elements.location_other;
                if (locationOther) locationOther.value = "";
                if (locationSelect) {
                    const savedLocation = String(internship.location || "").trim();
                    const isDistrict = Array.from(locationSelect.options).some(function (option) {
                        return option.value === savedLocation;
                    });
                    if (savedLocation === "Remote" || savedLocation === "") {
                        locationSelect.value = "";
                    } else if (isDistrict) {
                        locationSelect.value = savedLocation;
                    } else {
                        locationSelect.value = "Other";
                        if (locationOther) locationOther.value = savedLocation;
                    }
                    locationSelect.dataset.originalValue = locationSelect.value;
                }
                if (locationOther) {
                    locationOther.dataset.originalValue = locationOther.value;
                }
                ["deadline", "start_date"].forEach(function (name) {
                    if (form.elements[name]) {
                        form.elements[name].dataset.originalValue = form.elements[name].value;
                    }
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
            document.dispatchEvent(new Event("internship:edit-filled"));

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

(function () {
    const districts = [
        "Ampara", "Anuradhapura", "Badulla", "Batticaloa", "Colombo",
        "Galle", "Gampaha", "Hambantota", "Jaffna", "Kalutara", "Kandy",
        "Kegalle", "Kilinochchi", "Kurunegala", "Mannar", "Matale", "Matara",
        "Monaragala", "Mullaitivu", "Nuwara Eliya", "Polonnaruwa", "Puttalam",
        "Ratnapura", "Trincomalee", "Vavuniya"
    ];
    const industries = [
        "Information Technology", "Software Development", "Data Science & AI",
        "Cyber Security", "Business & Management", "Finance & Accounting",
        "Marketing", "Engineering", "Design / UI-UX", "Human Resources", "Other"
    ];
    const today = function () {
        const now = new Date();
        return [now.getFullYear(), String(now.getMonth() + 1).padStart(2, "0"), String(now.getDate()).padStart(2, "0")].join("-");
    };
    const length = value => Array.from(value).length;

    function validDate(value) {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return false;
        const date = new Date(value + "T00:00:00");
        if (Number.isNaN(date.getTime())) return false;
        const normalized = [date.getFullYear(), String(date.getMonth() + 1).padStart(2, "0"), String(date.getDate()).padStart(2, "0")].join("-");
        return normalized === value;
    }

    function syncLocation(form) {
        const mode = form.elements.work_mode;
        const location = form.elements.location;
        const other = form.elements.location_other;
        const otherGroup = document.getElementById(form.id === "editInternshipForm" ? "editLocationOtherGroup" : "locationOtherGroup");
        if (!mode || !location || !other) return;

        const remote = mode.value === "Remote";
        const locationRequired = mode.value === "On-site" || mode.value === "Hybrid";
        location.disabled = remote;
        location.required = locationRequired;
        if (remote) {
            location.value = "";
            other.value = "";
        }
        const isOther = location.value === "Other" && !remote;
        other.disabled = !isOther;
        other.required = isOther;
        if (otherGroup) {
            otherGroup.hidden = !isOther;
            otherGroup.style.display = isOther ? "" : "none";
        }
        if (remote) {
            location.setCustomValidity("");
            other.setCustomValidity("");
        }
    }

    function validateForm(form) {
        const edit = form.id === "editInternshipForm";
        const field = name => form.elements[name];
        const value = name => field(name) ? String(field(name).value).trim() : "";
        const setError = (name, message) => {
            if (field(name)) field(name).setCustomValidity(message);
        };
        const title = value("title");
        const industry = value("industry");
        const description = value("description");
        const academicYear = value("academic_year");
        const experience = value("experience_level");
        const vacancies = value("vacancies");
        const duration = value("duration");
        const internshipType = value("internship_type");
        const internshipTypeOther = value("internship_type_other");
        const workMode = value("work_mode");
        const deadline = value("deadline");
        const startDate = value("start_date");
        const paidStatus = value("paid_status");
        const stipend = value("stipend");
        const responsibilities = value("responsibilities");
        const benefits = value("benefits");
        const deadlineField = field("deadline");
        const startDateField = field("start_date");
        const originalDeadline = deadlineField ? (deadlineField.dataset.originalValue || form.dataset.originalDeadline || "") : "";
        const originalStartDate = startDateField ? (startDateField.dataset.originalValue || form.dataset.originalStartDate || "") : "";
        const deadlineUnchanged = edit && deadline !== "" && deadline === originalDeadline;
        const startUnchanged = edit && startDate !== "" && startDate === originalStartDate;

        setError("title", length(title) < 3 || length(title) > 255 || !/\p{L}/u.test(title)
            ? "Title must be 3-255 characters and include at least one letter." : "");
        setError("industry", industries.includes(industry) ? "" : "Please select a valid industry category.");
        setError("description", ((!edit && description === "") || (description !== "" && length(description) < 20))
            ? "Description must be at least 20 characters."
            : (length(description) > 5000 ? "Description must not exceed 5,000 characters." : ""));
        setError("academic_year", ["", "3rd Year", "4th Year"].includes(academicYear)
            ? "" : "Please select a valid academic year.");
        setError("experience_level", ["", "Beginner", "Intermediate", "Advanced"].includes(experience)
            ? "" : "Please select a valid experience level.");
        setError("vacancies", /^\d+$/.test(vacancies) && Number(vacancies) >= 1 && Number(vacancies) <= 999
            ? "" : "Vacancies must be a whole number from 1 to 999.");
        setError("duration", /^\d+\s*(day|days|week|weeks|month|months|year|years)$/i.test(duration)
            ? "" : "Duration must include a number and a unit (day, week, month, or year).");
        const typeOptions = ["Full-time", "Part-time", "Contract", "Project-based", "Flexible Hours"];
        const typeOtherValid = /^[\p{L}][\p{L} /-]{0,48}[\p{L}]$|^[\p{L}]{2,50}$/u.test(internshipTypeOther) && length(internshipTypeOther) >= 2 && length(internshipTypeOther) <= 50;
        setError("internship_type", typeOptions.includes(internshipType) || internshipType === "Other"
            ? "" : "Please select a valid internship type.");
        const typeOther = field("internship_type_other");
        const typeOtherGroup = document.getElementById(edit ? "editInternshipTypeOtherGroup" : "internshipTypeOtherGroup");
        if (typeOther) {
            typeOther.required = internshipType === "Other";
            typeOther.disabled = internshipType !== "Other";
            if (typeOtherGroup) typeOtherGroup.hidden = internshipType !== "Other";
            setError("internship_type_other", internshipType === "Other" && !typeOtherValid
                ? "Other internship type must be 2-50 characters using letters, spaces, hyphens, or slashes." : "");
        }
        setError("work_mode", ["", "On-site", "Remote", "Hybrid"].includes(workMode)
            ? "" : "Please select a valid work mode.");

        const todayValue = today();
        if (deadlineField) {
            deadlineField.min = edit && deadlineUnchanged && deadline < todayValue ? deadline : todayValue;
        }
        if (startDateField) {
            startDateField.min = edit && deadlineUnchanged && startUnchanged ? startDate : deadline;
        }
        setError("deadline", !validDate(deadline)
            ? "Please select a valid application deadline."
            : (!deadlineUnchanged && deadline < todayValue ? "Application deadline cannot be in the past." : ""));
        setError("start_date", startDate !== "" && !validDate(startDate)
            ? "Please enter a valid start date."
            : (startDate !== "" && !(deadlineUnchanged && startUnchanged) && startDate < deadline
                ? "Start date must be on or after the application deadline." : ""));

        setError("paid_status", ["Paid", "Unpaid"].includes(paidStatus) ? "" : "Please select Paid or Unpaid.");
        const stipendNumber = Number(stipend);
        setError("stipend", stipend !== "" && (!Number.isFinite(stipendNumber) || stipendNumber < 0)
            ? "Stipend must be a valid amount of LKR 0-10,000,000."
            : (stipend !== "" && stipendNumber > 10000000
                ? "Stipend must not exceed LKR 10,000,000."
                : (paidStatus === "Paid" && (!stipend || !Number.isFinite(stipendNumber) || stipendNumber <= 0)
                    ? "Enter a stipend greater than 0 for a paid internship." : "")));
        setError("responsibilities", length(responsibilities) > 5000 ? "Responsibilities must not exceed 5,000 characters." : "");
        setError("benefits", length(benefits) > 5000 ? "Benefits must not exceed 5,000 characters." : "");

        const location = field("location");
        const other = field("location_other");
        if (location && other) {
            const selection = location.value;
            const otherValue = other.value.trim();
            let locationMessage = "";
            let otherMessage = "";
            if (workMode !== "Remote") {
                if ((workMode === "On-site" || workMode === "Hybrid") && selection === "") {
                    locationMessage = "Please select a work location.";
                } else if (selection !== "" && selection !== "Other" && !districts.includes(selection)) {
                    locationMessage = "Please select a valid work location.";
                } else if (selection === "Other" && (length(otherValue) < 2 || length(otherValue) > 100 || !/\p{L}/u.test(otherValue) || !/^[\p{L} ,]+$/u.test(otherValue))) {
                    otherMessage = "Enter a city and country using 2-100 letters, spaces, and commas only.";
                }
            }
            setError("location", locationMessage);
            setError("location_other", otherMessage);
        }

        return form.checkValidity();
    }

    function initialize() {
        ["internshipForm", "editInternshipForm"].forEach(function (id) {
            const form = document.getElementById(id);
            if (!form) return;
            const edit = id === "editInternshipForm";
            const mode = form.elements.work_mode;
            const location = form.elements.location;
            const paidStatus = form.elements.paid_status;
            const stipend = form.elements.stipend;
            const deadline = form.elements.deadline;
            const startDate = form.elements.start_date;

            if (edit) {
                if (deadline && !deadline.dataset.originalValue) deadline.dataset.originalValue = form.dataset.originalDeadline || deadline.value;
                if (startDate && !startDate.dataset.originalValue) startDate.dataset.originalValue = form.dataset.originalStartDate || startDate.value;
            } else if (deadline) {
                deadline.min = today();
            }

            const update = function () {
                syncLocation(form);
                if (paidStatus && stipend) {
                    const isPaid = paidStatus.value === "Paid";
                    stipend.disabled = !isPaid;
                    stipend.required = isPaid;
                    if (!isPaid) stipend.value = "";
                }
                validateForm(form);
            };

            form.addEventListener("input", update);
            form.addEventListener("change", update);
            form.addEventListener("submit", function (event) {
                update();
                if (!validateForm(form)) {
                    event.preventDefault();
                    const invalid = form.querySelector(":invalid");
                    if (invalid) invalid.reportValidity();
                }
            }, true);
            if (mode) mode.addEventListener("change", update);
            if (location) location.addEventListener("change", update);
            if (paidStatus) paidStatus.addEventListener("change", update);
            if (deadline) deadline.addEventListener("change", update);
            if (startDate) startDate.addEventListener("change", update);
            update();
        });

        document.addEventListener("internship:edit-filled", function () {
            const form = document.getElementById("editInternshipForm");
            if (!form) return;
            syncLocation(form);
            ["deadline", "start_date"].forEach(function (name) {
                if (form.elements[name]) form.elements[name].dataset.originalValue = form.elements[name].value;
            });
            validateForm(form);
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initialize, { once: true });
    } else {
        initialize();
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


/* =========================================
   VIEW INTERNSHIP MODAL
========================================= */

(function () {
    function initializeViewInternshipModal() {
        const modal = document.getElementById("viewInternshipModal");
        const viewButtons = document.querySelectorAll("[data-view-internship]");
        if (!modal || viewButtons.length === 0 || modal.dataset.modalInitialized === "true") {
            return;
        }

        modal.dataset.modalInitialized = "true";
        let lastFocusedElement = null;

        const coverContainer = document.getElementById("viewModalCoverContainer");
        const coverImg = document.getElementById("viewModalCoverImg");
        const titleEl = document.getElementById("viewModalMainTitle");
        const companyEl = document.getElementById("viewModalCompany");
        const industryEl = document.getElementById("viewModalIndustry");
        const statusBadge = document.getElementById("viewModalStatusBadge");
        const descEl = document.getElementById("viewModalDescription");

        const durationEl = document.getElementById("viewModalDuration");
        const typeEl = document.getElementById("viewModalType");
        const workModeEl = document.getElementById("viewModalWorkMode");
        const locationEl = document.getElementById("viewModalLocation");
        const vacanciesEl = document.getElementById("viewModalVacancies");
        const expEl = document.getElementById("viewModalExperience");
        const yearEl = document.getElementById("viewModalAcademicYear");
        const startDateEl = document.getElementById("viewModalStartDate");
        const deadlineEl = document.getElementById("viewModalDeadline");
        const paidStatusEl = document.getElementById("viewModalPaidStatus");
        const stipendEl = document.getElementById("viewModalStipend");
        const applicantsEl = document.getElementById("viewModalApplicantsCount");

        const skillsContainer = document.getElementById("viewModalSkillsList");
        const respGroup = document.getElementById("viewModalResponsibilitiesGroup");
        const respEl = document.getElementById("viewModalResponsibilities");
        const benefitsGroup = document.getElementById("viewModalBenefitsGroup");
        const benefitsEl = document.getElementById("viewModalBenefits");
        const docGroup = document.getElementById("viewModalDocumentGroup");
        const docLink = document.getElementById("viewModalDocumentLink");
        const editBtn = document.getElementById("viewModalEditBtn");

        let currentInternshipId = null;

        function openModal(data) {
            currentInternshipId = data.id;
            lastFocusedElement = document.activeElement;

            if (coverContainer && coverImg) {
                if (data.cover_image) {
                    coverImg.src = data.cover_image;
                    coverContainer.style.display = "block";
                } else {
                    coverContainer.style.display = "none";
                }
            }

            if (titleEl) titleEl.textContent = data.title || "Internship Details";
            if (companyEl) companyEl.textContent = data.company || "Company";
            if (industryEl) industryEl.textContent = data.industry || "General Industry";

            if (statusBadge) {
                const status = data.status || "Active";
                statusBadge.textContent = status;
                statusBadge.className = "status-pill status-" + status.toLowerCase();
            }

            if (descEl) descEl.textContent = data.description || "No description provided.";
            if (durationEl) durationEl.textContent = data.duration || "Not specified";
            if (typeEl) typeEl.textContent = data.internship_type || "Not specified";
            if (workModeEl) workModeEl.textContent = data.work_mode || "Not specified";
            if (locationEl) locationEl.textContent = data.location || "Not specified";
            if (vacanciesEl) vacanciesEl.textContent = data.vacancies ? (data.vacancies + " opening" + (data.vacancies > 1 ? "s" : "")) : "Not specified";
            if (expEl) expEl.textContent = data.experience_level || "Not specified";
            if (yearEl) yearEl.textContent = data.academic_year || "3rd & 4th Year (both)";
            if (startDateEl) startDateEl.textContent = data.start_date || "Not specified";
            if (deadlineEl) deadlineEl.textContent = data.deadline || "Not specified";
            if (paidStatusEl) paidStatusEl.textContent = data.paid_status || "Unpaid";
            if (stipendEl) stipendEl.textContent = (data.paid_status === "Paid" && data.stipend) ? ("LKR " + data.stipend) : "Not applicable";
            if (applicantsEl) applicantsEl.textContent = String(data.applicant_count || 0) + " candidate" + ((data.applicant_count || 0) === 1 ? "" : "s");

            // Skills chips
            if (skillsContainer) {
                skillsContainer.innerHTML = "";
                const tags = (data.tech_tags || "").split(",").map(t => t.trim()).filter(Boolean);
                if (tags.length > 0) {
                    tags.forEach(tag => {
                        const chip = document.createElement("span");
                        chip.className = "skill-chip";
                        chip.style.cssText = "padding: 6px 12px; background: #e0f2fe; color: #0369a1; border-radius: 50px; font-size: 12px; font-weight: 600;";
                        chip.textContent = tag;
                        skillsContainer.appendChild(chip);
                    });
                } else {
                    const empty = document.createElement("span");
                    empty.style.color = "#94a3b8";
                    empty.style.fontSize = "13px";
                    empty.textContent = "No specific skills specified.";
                    skillsContainer.appendChild(empty);
                }
            }

            if (respEl && respGroup) {
                if (data.responsibilities) {
                    respEl.textContent = data.responsibilities;
                    respGroup.style.display = "block";
                } else {
                    respGroup.style.display = "none";
                }
            }

            if (benefitsEl && benefitsGroup) {
                if (data.benefits) {
                    benefitsEl.textContent = data.benefits;
                    benefitsGroup.style.display = "block";
                } else {
                    benefitsGroup.style.display = "none";
                }
            }

            if (docGroup && docLink) {
                if (data.supporting_document) {
                    docLink.href = data.supporting_document;
                    docGroup.style.display = "block";
                } else {
                    docGroup.style.display = "none";
                }
            }

            modal.hidden = false;
            modal.setAttribute("aria-hidden", "false");
            document.body.classList.add("internship-modal-open");
        }

        function closeModal() {
            modal.hidden = true;
            modal.setAttribute("aria-hidden", "true");
            document.body.classList.remove("internship-modal-open");
            if (lastFocusedElement && typeof lastFocusedElement.focus === "function") {
                lastFocusedElement.focus();
            }
        }

        viewButtons.forEach(button => {
            button.addEventListener("click", function (event) {
                event.preventDefault();
                const raw = button.getAttribute("data-view-internship") || button.dataset.viewInternship;
                if (!raw) {
                    console.warn("No data-view-internship found on button");
                    return;
                }
                try {
                    const data = typeof raw === "string" ? JSON.parse(raw) : raw;
                    openModal(data);
                } catch (e) {
                    console.error("Error parsing internship data", e, raw);
                }
            });
        });

        modal.querySelectorAll("[data-view-modal-close]").forEach(btn => {
            btn.addEventListener("click", closeModal);
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape" && !modal.hidden) {
                closeModal();
            }
        });

        if (editBtn) {
            editBtn.addEventListener("click", function () {
                closeModal();
                if (currentInternshipId) {
                    const editButton = document.querySelector(`[data-internship-id="${currentInternshipId}"][data-edit-internship]`);
                    if (editButton) {
                        editButton.click();
                    }
                }
            });
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initializeViewInternshipModal, { once: true });
    } else {
        initializeViewInternshipModal();
    }
})();

(function () {
    const catalog = window.SKILL_CATALOG || null;

    function skillKey(value) {
        return String(value).trim().toLowerCase().replace(/[\s.\-_]+/g, "");
    }

    function editDistance(left, right) {
        const previous = Array.from({ length: right.length + 1 }, (_, index) => index);
        for (let i = 1; i <= left.length; i++) {
            let diagonal = previous[0];
            previous[0] = i;
            for (let j = 1; j <= right.length; j++) {
                const above = previous[j];
                previous[j] = Math.min(
                    previous[j] + 1,
                    previous[j - 1] + 1,
                    diagonal + (left[i - 1] === right[j - 1] ? 0 : 1)
                );
                diagonal = above;
            }
        }
        return previous[right.length];
    }

    function suggest(skill) {
        if (!catalog) return "";
        const key = skillKey(skill);
        if (!key) return "";
        let best = "";
        let bestDistance = Infinity;
        Object.entries(catalog).forEach(([catalogKey, canonical]) => {
            const distance = editDistance(key, catalogKey);
            if (distance < bestDistance) {
                bestDistance = distance;
                best = canonical;
            }
        });
        return bestDistance <= Math.max(1, Math.floor(key.length / 3)) ? best : "";
    }

    function validateSkills(value, required) {
        const skills = [];
        for (const rawSkill of value.split(",")) {
            const skill = rawSkill.trim().replace(/ +/g, " ");
            if (!skill) continue;
            skills.push(skill);
        }
        if (skills.length === 0) {
            return required ? "Please enter at least one skill, e.g. PHP, MySQL, JavaScript." : "";
        }
        if (skills.length > 15) return "Please list no more than 15 skills.";

        const canonicalSkills = [];
        const seen = new Set();
        for (const skill of skills) {
            if (!/\p{L}/u.test(skill)) {
                return `"${skill}" is not a valid skill. Skills must contain letters, not only numbers or symbols.`;
            }
            const key = skillKey(skill);
            if (catalog && !Object.prototype.hasOwnProperty.call(catalog, key)) {
                const hint = suggest(skill);
                return `"${skill}" is not a recognised skill or technology.${hint ? ` Did you mean "${hint}"?` : " Please enter a real skill such as PHP, MySQL or Communication."}`;
            }
            const canonical = catalog ? catalog[key] : skill;
            const canonicalKey = skillKey(canonical);
            if (seen.has(canonicalKey)) return `Skill "${canonical}" is listed more than once.`;
            seen.add(canonicalKey);
            canonicalSkills.push(canonical);
        }
        return "";
    }

    function bindTechTagsValidation() {
        ["tech_tags", "edit_tech_tags"].forEach(id => {
            const input = document.getElementById(id);
            if (!input || input.dataset.techTagsValidationBound === "true") return;
            input.dataset.techTagsValidationBound = "true";
            const form = input.form;
            const required = id === "tech_tags";
            const validate = () => {
                const message = validateSkills(input.value, required);
                input.setCustomValidity(message);
                return message === "";
            };

            input.addEventListener("input", validate);
            input.addEventListener("blur", function () {
                if (!validate() && input.value.trim() !== "") input.reportValidity();
            });
            if (form) {
                form.addEventListener("submit", function (event) {
                    if (!validate()) {
                        event.preventDefault();
                        input.reportValidity();
                    }
                }, true);
            }
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", bindTechTagsValidation, { once: true });
    }
    bindTechTagsValidation();
})();
