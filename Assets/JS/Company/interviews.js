document.addEventListener("DOMContentLoaded", function () {
    const rows = Array.from(document.querySelectorAll(".interview-row"));
    const scheduleModal = document.getElementById("scheduleInterviewModal");
    const viewModal = document.getElementById("viewInterviewModal");
    const decisionModal = document.getElementById("interviewDecisionModal");
    const decisionForm = document.getElementById("interviewDecisionForm");

    const radioHire = document.getElementById("choiceHired");
    const radioDisqualify = document.getElementById("choiceDisqualified");
    const cardHire = document.getElementById("cardHireChoice");
    const cardDisqualify = document.getElementById("cardDisqualifyChoice");
    const hireSection = document.getElementById("hireFieldsSection");
    const disqualifySection = document.getElementById("disqualifyFieldsSection");
    const categorySelect = document.getElementById("disqualificationCategory");
    const feedbackText = document.getElementById("disqualificationFeedback");
    const submitBtn = document.getElementById("btnSubmitDecision");
    const submitText = document.getElementById("decisionSubmitText");
    const modalCandidateTitle = document.getElementById("decisionModalCandidate");
    const modalInternshipSubtitle = document.getElementById("decisionModalInternship");

    // Search and filter controls
    const searchInput = document.getElementById("interviewSearchInput");
    const clearSearchBtn = document.getElementById("clearSearchBtn");
    const statusFilter = document.getElementById("interviewStatusFilter");
    const roleFilter = document.getElementById("interviewRoleFilter");
    const visibleCountSpan = document.getElementById("visibleInterviewCount");
    const totalCountSpan = document.getElementById("totalInterviewCount");
    const noResultsRow = document.getElementById("noInterviewResultsRow");
    const resetFiltersBtn = document.getElementById("resetInterviewFiltersBtn");

    let activeDecisionRow = null;

    function openModal(modal) {
        if (!modal) return;
        modal.classList.add("active");
        document.body.style.overflow = "hidden";
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove("active");
        if (!document.querySelector(".interview-modal-overlay.active")) document.body.style.overflow = "";
    }

    function refreshSummaryCounts() {
        const hiredCount = document.getElementById("hiredInterviewCount");
        const interviewingCount = document.getElementById("totalInterviewingCount");
        const rejectedCount = document.getElementById("rejectedInterviewCount");

        if (hiredCount) {
            hiredCount.textContent = String(rows.filter(row => row.dataset.status === "Hired").length);
        }
        if (interviewingCount) {
            interviewingCount.textContent = String(rows.filter(row => row.dataset.status === "Interviewing").length);
        }
        if (rejectedCount) {
            rejectedCount.textContent = String(rows.filter(row => row.dataset.status === "Disqualified").length);
        }
    }

    function setDecisionMode(mode) {
        if (mode === "Hired") {
            if (radioHire) radioHire.checked = true;
            if (cardHire) {
                cardHire.style.borderColor = "#16a34a";
                cardHire.style.background = "#f0fdf4";
            }
            if (cardDisqualify) {
                cardDisqualify.style.borderColor = "#e2e8f0";
                cardDisqualify.style.background = "#ffffff";
            }
            if (hireSection) {
                hireSection.hidden = false;
                hireSection.style.display = "flex";
            }
            if (disqualifySection) {
                disqualifySection.hidden = true;
                disqualifySection.style.display = "none";
            }
            if (categorySelect) categorySelect.required = false;
            if (feedbackText) feedbackText.required = false;
            if (submitBtn) {
                submitBtn.style.background = "#16854a";
                submitBtn.style.borderColor = "#16854a";
            }
            if (submitText) submitText.textContent = "Confirm Hire";
        } else {
            if (radioDisqualify) radioDisqualify.checked = true;
            if (cardHire) {
                cardHire.style.borderColor = "#e2e8f0";
                cardHire.style.background = "#ffffff";
            }
            if (cardDisqualify) {
                cardDisqualify.style.borderColor = "#dc2626";
                cardDisqualify.style.background = "#fff5f5";
            }
            if (hireSection) {
                hireSection.hidden = true;
                hireSection.style.display = "none";
            }
            if (disqualifySection) {
                disqualifySection.hidden = false;
                disqualifySection.style.display = "flex";
            }
            if (categorySelect) categorySelect.required = true;
            if (feedbackText) feedbackText.required = true;
            if (submitBtn) {
                submitBtn.style.background = "#dc2626";
                submitBtn.style.borderColor = "#dc2626";
            }
            if (submitText) submitText.textContent = "Confirm Disqualification";
        }
    }

    function openInterviewDecisionModal(row) {
        activeDecisionRow = row;
        if (modalCandidateTitle) modalCandidateTitle.textContent = row.dataset.student || "Candidate";
        if (modalInternshipSubtitle) {
            modalInternshipSubtitle.textContent = row.dataset.internship
                ? "Position: " + row.dataset.internship
                : "Record interview outcome decision";
        }
        if (decisionForm) decisionForm.reset();
        setDecisionMode("Hired");
        openModal(decisionModal);
    }

    function refreshRowActions(row) {
        const actions = row.querySelector(".interview-actions");
        if (!actions) return;

        // Remove any dynamically added outcome buttons
        actions.querySelectorAll(".interview-outcome-btn").forEach(button => button.remove());

        // Decision button is ONLY visible when status is 'Interviewing'
        const isInterviewing = row.dataset.status === "Interviewing";
        if (isInterviewing) {
            const decisionButton = document.createElement("button");
            decisionButton.type = "button";
            decisionButton.className = "interview-action-btn interview-outcome-btn interview-decision-btn";
            decisionButton.title = "Record Interview Decision (Hire / Disqualify)";
            decisionButton.setAttribute("aria-label", "Interview Decision");
            decisionButton.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true">fact_check</span>';
            decisionButton.addEventListener("click", function () {
                openInterviewDecisionModal(row);
            });
            actions.appendChild(decisionButton);
        }
        // When Hired or Disqualified, ONLY the eye icon (view-interview-btn) is shown
    }

    function setInterviewStatus(row, status, reason = "") {
        if (!row || !["Interviewing", "Hired", "Disqualified"].includes(status)) return;
        row.dataset.status = status;
        if (reason) row.dataset.disqualificationReason = reason;
        else delete row.dataset.disqualificationReason;

        const badge = row.querySelector(".interview-status");
        if (badge) {
            badge.textContent = status;
            badge.classList.remove("interviewing", "hired", "disqualified");
            badge.classList.add(status.toLowerCase());
        }
        refreshRowActions(row);
        refreshSummaryCounts();
        applyTableFilters();
    }

    function showView(row) {
        const fields = {
            viewStudentName: row.dataset.student,
            viewInternship: row.dataset.internship,
            viewTeam: row.dataset.team,
            viewDate: row.dataset.date,
            viewTime: row.dataset.time,
            viewStatus: row.dataset.status
        };
        Object.entries(fields).forEach(([id, value]) => {
            const field = document.getElementById(id);
            if (field) field.textContent = value || "-";
        });
        const reasonItem = document.getElementById("viewDisqualificationReasonItem");
        const reasonField = document.getElementById("viewDisqualificationReason");
        if (reasonItem && reasonField) {
            reasonItem.hidden = row.dataset.status !== "Disqualified";
            reasonField.textContent = row.dataset.disqualificationReason || "Reason not recorded";
        }
        openModal(viewModal);
    }

    // Filter and search application function
    function applyTableFilters() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : "";
        const selectedStatus = statusFilter ? statusFilter.value : "all";
        const selectedRole = roleFilter ? roleFilter.value : "all";

        if (clearSearchBtn) {
            clearSearchBtn.hidden = !query;
        }

        let visibleCount = 0;

        rows.forEach(row => {
            const student = (row.dataset.student || "").toLowerCase();
            const course = (row.dataset.course || "").toLowerCase();
            const internship = (row.dataset.internship || "").toLowerCase();
            const team = (row.dataset.team || "").toLowerCase();
            const status = (row.dataset.status || "").toLowerCase();

            const textMatch = !query || student.includes(query) || course.includes(query) || internship.includes(query) || team.includes(query);
            const statusMatch = selectedStatus === "all" || status === selectedStatus.toLowerCase();
            const roleMatch = selectedRole === "all" || (row.dataset.internship || "") === selectedRole;

            if (textMatch && statusMatch && roleMatch) {
                row.hidden = false;
                visibleCount++;
            } else {
                row.hidden = true;
            }
        });

        if (visibleCountSpan) visibleCountSpan.textContent = String(visibleCount);
        if (totalCountSpan) totalCountSpan.textContent = String(rows.length);

        if (noResultsRow) {
            noResultsRow.hidden = visibleCount > 0;
        }
    }

    // Initialize all table rows
    rows.forEach(row => {
        if (!["Interviewing", "Hired", "Disqualified"].includes(row.dataset.status)) {
            setInterviewStatus(row, "Interviewing");
        } else {
            refreshRowActions(row);
        }
        const viewButton = row.querySelector(".view-interview-btn");
        if (viewButton) {
            viewButton.addEventListener("click", () => showView(row));
        }
    });
    refreshSummaryCounts();
    applyTableFilters();

    // Search and filter listeners
    if (searchInput) {
        searchInput.addEventListener("input", applyTableFilters);
    }
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener("click", function () {
            if (searchInput) {
                searchInput.value = "";
                searchInput.focus();
            }
            applyTableFilters();
        });
    }
    if (statusFilter) {
        statusFilter.addEventListener("change", applyTableFilters);
    }
    if (roleFilter) {
        roleFilter.addEventListener("change", applyTableFilters);
    }
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener("click", function () {
            if (searchInput) searchInput.value = "";
            if (statusFilter) statusFilter.value = "all";
            if (roleFilter) roleFilter.value = "all";
            applyTableFilters();
        });
    }

    // Toggle choice listeners
    if (radioHire) {
        radioHire.addEventListener("change", function () {
            if (this.checked) setDecisionMode("Hired");
        });
    }
    if (radioDisqualify) {
        radioDisqualify.addEventListener("change", function () {
            if (this.checked) setDecisionMode("Disqualified");
        });
    }
    if (cardHire) {
        cardHire.addEventListener("click", function () {
            setDecisionMode("Hired");
        });
    }
    if (cardDisqualify) {
        cardDisqualify.addEventListener("click", function () {
            setDecisionMode("Disqualified");
        });
    }

    // Submit decision form
    if (decisionForm) {
        decisionForm.addEventListener("submit", function (event) {
            event.preventDefault();
            if (!activeDecisionRow) return;

            const selectedChoice = document.querySelector('input[name="decision_choice"]:checked')?.value || "Hired";

            if (selectedChoice === "Hired") {
                setInterviewStatus(activeDecisionRow, "Hired");
            } else {
                const category = categorySelect ? categorySelect.value.trim() : "";
                const feedback = feedbackText ? feedbackText.value.trim() : "";

                if (!category) {
                    if (categorySelect) categorySelect.focus();
                    return;
                }
                if (!feedback) {
                    if (feedbackText) feedbackText.focus();
                    return;
                }

                const fullReason = category + ": " + feedback;
                setInterviewStatus(activeDecisionRow, "Disqualified", fullReason);
            }

            closeModal(decisionModal);
            decisionForm.reset();
            activeDecisionRow = null;
        });
    }

    // Modal close listeners
    document.querySelectorAll("[data-close-modal]").forEach(button => {
        button.addEventListener("click", function () {
            const modal = button.closest(".interview-modal-overlay");
            closeModal(modal);
            if (modal === decisionModal) {
                activeDecisionRow = null;
                if (decisionForm) decisionForm.reset();
            }
        });
    });

    document.querySelectorAll(".interview-modal-overlay").forEach(overlay => {
        overlay.addEventListener("click", function (event) {
            if (event.target !== overlay) return;
            closeModal(overlay);
            if (overlay === decisionModal) {
                activeDecisionRow = null;
                if (decisionForm) decisionForm.reset();
            }
        });
    });

    document.addEventListener("keydown", event => {
        if (event.key !== "Escape") return;
        document.querySelectorAll(".interview-modal-overlay.active").forEach(closeModal);
        activeDecisionRow = null;
    });

    const scheduleForm = document.getElementById("scheduleInterviewForm");
    if (scheduleForm) {
        scheduleForm.addEventListener("submit", function (event) {
            event.preventDefault();
            closeModal(scheduleModal);
            scheduleForm.reset();
        });
    }

    const filterButton = document.getElementById("filterInterviewsBtn");
    if (filterButton) {
        filterButton.addEventListener("click", () => {
            if (searchInput) searchInput.focus();
        });
    }
});
