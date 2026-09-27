document.addEventListener("DOMContentLoaded", function () {
    const statusFilter = document.getElementById("statusFilter");
    const universityFilter = document.getElementById("universityFilter");
    const rows = Array.from(document.querySelectorAll("#applicationsTable tbody .application-row"));
    const visibleCount = document.getElementById("visibleApplicationCount");

    function filterApplications() {
        const status = statusFilter ? statusFilter.value : "all";
        const university = universityFilter ? universityFilter.value : "all";
        let shown = 0;

        rows.forEach(function (row) {
            const statusMatch = status === "all" || row.dataset.status === status;
            const universityMatch = university === "all" || row.dataset.university === university;
            const visible = statusMatch && universityMatch;
            row.hidden = !visible;
            if (visible) shown++;
        });

        if (visibleCount) visibleCount.textContent = String(shown);
    }

    if (statusFilter) statusFilter.addEventListener("change", filterApplications);
    if (universityFilter) universityFilter.addEventListener("change", filterApplications);

    const exportButton = document.getElementById("exportCsvBtn");
    if (exportButton) {
        exportButton.addEventListener("click", function () {
            const lines = [["Student", "University", "Skills", "Applied Date", "Status"]];
            rows.forEach(function (row) {
                if (row.hidden) return;
                const cells = row.querySelectorAll("td");
                lines.push([
                    cells[0].querySelector("strong")?.textContent.trim() || "",
                    cells[1].textContent.trim(),
                    cells[2].textContent.trim().replace(/\s+/g, " "),
                    cells[3].textContent.trim(),
                    cells[4].textContent.trim()
                ]);
            });

            const csv = lines.map(function (line) {
                return line.map(function (value) {
                    return '"' + String(value).replace(/"/g, '""') + '"';
                }).join(",");
            }).join("\r\n");
            const url = URL.createObjectURL(new Blob([csv], { type: "text/csv;charset=utf-8" }));
            const link = document.createElement("a");
            link.href = url;
            link.download = "skillbridge-applications.csv";
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        });
    }

    // =========================================================
    // DECISION & INTERVIEW SCHEDULING MODAL
    // =========================================================
    const decisionModal = document.getElementById("decisionModal");
    const decisionForm = document.getElementById("decisionModalForm");
    const closeBtn = document.getElementById("closeDecisionModalBtn");
    const cancelBtn = document.getElementById("cancelDecisionModalBtn");

    const modalAppId = document.getElementById("modalApplicationId");
    const candidateTitle = document.getElementById("modalCandidateTitle");
    const internshipSubtitle = document.getElementById("modalInternshipSubtitle");

    const radioAccepted = document.getElementById("radioDecisionAccepted");
    const radioDisqualified = document.getElementById("radioDecisionDisqualified");
    const cardOptAccepted = document.getElementById("cardOptAccepted");
    const cardOptDisqualified = document.getElementById("cardOptDisqualified");

    const acceptSection = document.getElementById("acceptInterviewSection");
    const disqualifySection = document.getElementById("disqualifySection");

    const inputDate = document.getElementById("modalInterviewDate");
    const selectTime = document.getElementById("modalInterviewTime");
    const selectType = document.getElementById("modalInterviewType");
    const inputLink = document.getElementById("modalInterviewLink");
    const inputNotes = document.getElementById("modalInterviewNotes");

    const selectReason = document.getElementById("modalDisqualificationReason");
    const inputDisqualifyNotes = document.getElementById("modalDisqualificationNotes");

    function setDecisionMode(mode) {
        if (mode === "Accepted") {
            radioAccepted.checked = true;
            if (cardOptAccepted) cardOptAccepted.classList.add("active");
            if (cardOptDisqualified) cardOptDisqualified.classList.remove("active");

            if (acceptSection) acceptSection.hidden = false;
            if (disqualifySection) disqualifySection.hidden = true;

            if (inputDate) inputDate.required = true;
            if (selectTime) selectTime.required = true;
            if (selectReason) selectReason.required = false;
        } else {
            radioDisqualified.checked = true;
            if (cardOptAccepted) cardOptAccepted.classList.remove("active");
            if (cardOptDisqualified) cardOptDisqualified.classList.add("active");

            if (acceptSection) acceptSection.hidden = true;
            if (disqualifySection) disqualifySection.hidden = false;

            if (inputDate) inputDate.required = false;
            if (selectTime) selectTime.required = false;
            if (selectReason) selectReason.required = true;
        }
    }

    if (radioAccepted) {
        radioAccepted.addEventListener("change", function () {
            if (this.checked) setDecisionMode("Accepted");
        });
    }
    if (radioDisqualified) {
        radioDisqualified.addEventListener("change", function () {
            if (this.checked) setDecisionMode("Disqualified");
        });
    }

    function openDecisionModal(triggerBtn) {
        if (!decisionModal) return;
        const row = triggerBtn.closest("tr.application-row");
        if (!row) return;

        const appId = row.dataset.id || "0";
        const candidate = row.dataset.candidate || "Student";
        const internship = row.dataset.internship || "";
        const currentStatus = row.dataset.status || "Applied";
        const interviewDate = row.dataset.interviewDate || "";
        const interviewTime = row.dataset.interviewTime || "";
        const interviewType = row.dataset.interviewType || "Video Interview (Google Meet / Zoom)";
        const interviewLink = row.dataset.interviewLink || "";
        const interviewNotes = row.dataset.interviewNotes || "";
        const disqReason = row.dataset.disqualificationReason || "";

        const mode = triggerBtn.dataset.mode || "switch";

        if (modalAppId) modalAppId.value = appId;
        if (candidateTitle) candidateTitle.textContent = candidate;
        if (internshipSubtitle) {
            internshipSubtitle.textContent = internship ? "Position: " + internship : "Manage candidate recruitment decision";
        }

        // Pre-fill interview values
        if (inputDate) {
            if (interviewDate) {
                inputDate.value = interviewDate;
            } else {
                // Default to tomorrow
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                inputDate.value = tomorrow.toISOString().split("T")[0];
            }
        }
        if (selectTime) selectTime.value = interviewTime || "10:00 AM - 10:45 AM";
        if (selectType) selectType.value = interviewType || "Video Interview (Google Meet / Zoom)";
        if (inputLink) inputLink.value = interviewLink;
        if (inputNotes) inputNotes.value = interviewNotes;

        // Pre-fill disqualify values
        if (selectReason) {
            // Check if disqReason matches one of the options
            let matchedOption = "";
            Array.from(selectReason.options).forEach(opt => {
                if (opt.value && disqReason.startsWith(opt.value)) matchedOption = opt.value;
            });
            selectReason.value = matchedOption || (disqReason ? "Other" : "");
        }
        if (inputDisqualifyNotes) {
            inputDisqualifyNotes.value = disqReason;
        }

        // Determine which mode to show
        if (mode === "accept") {
            setDecisionMode("Accepted");
        } else if (mode === "disqualify") {
            setDecisionMode("Disqualified");
        } else {
            // Default to current status so user sees existing details and can switch freely
            if (currentStatus === "Disqualified") {
                setDecisionMode("Disqualified");
            } else {
                setDecisionMode("Accepted");
            }
        }

        decisionModal.hidden = false;
        document.body.style.overflow = "hidden";
    }

    function closeDecisionModal() {
        if (!decisionModal) return;
        decisionModal.hidden = true;
        document.body.style.overflow = "";
    }

    document.querySelectorAll("[data-trigger-modal]").forEach(function (btn) {
        btn.addEventListener("click", function () {
            openDecisionModal(this);
        });
    });

    if (closeBtn) closeBtn.addEventListener("click", closeDecisionModal);
    if (cancelBtn) cancelBtn.addEventListener("click", closeDecisionModal);

    if (decisionModal) {
        decisionModal.addEventListener("click", function (e) {
            if (e.target === decisionModal) closeDecisionModal();
        });
    }

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && decisionModal && !decisionModal.hidden) {
            closeDecisionModal();
        }
    });
});
