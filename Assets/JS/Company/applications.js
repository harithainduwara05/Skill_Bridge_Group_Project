document.addEventListener("DOMContentLoaded", function () {
    const statusFilter = document.getElementById("statusFilter");
    const universityFilter = document.getElementById("universityFilter");
    const internshipFilter = document.getElementById("internshipFilter");
    const searchInput = document.getElementById("applicationSearch");
    const clearFiltersBtn = document.getElementById("clearFiltersBtn");
    const noMatchRow = document.getElementById("noMatchRow");
    const rows = Array.from(document.querySelectorAll("#applicationsTable tbody .application-row"));
    const visibleCount = document.getElementById("visibleApplicationCount");

    function filterApplications() {
        const status = statusFilter ? statusFilter.value : "all";
        const university = universityFilter ? universityFilter.value : "all";
        const internship = internshipFilter ? internshipFilter.value : "all";
        const query = searchInput ? searchInput.value.trim().toLowerCase() : "";
        let shown = 0;

        rows.forEach(function (row) {
            const visible =
                (status === "all" || row.dataset.status === status) &&
                (university === "all" || (row.dataset.university || "").replace(/\s+/g, " ").trim().toLocaleLowerCase() === university.replace(/\s+/g, " ").trim().toLocaleLowerCase()) &&
                (internship === "all" || row.dataset.internship === internship) &&
                (query === "" || (row.dataset.search || "").indexOf(query) !== -1);
            row.hidden = !visible;
            if (visible) shown++;
        });

        if (visibleCount) visibleCount.textContent = String(shown);
        if (noMatchRow) noMatchRow.hidden = !(rows.length > 0 && shown === 0);
    }

    [statusFilter, universityFilter, internshipFilter].forEach(function (el) {
        if (el) el.addEventListener("change", filterApplications);
    });
    if (searchInput) searchInput.addEventListener("input", filterApplications);
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener("click", function () {
            if (statusFilter) statusFilter.value = "all";
            if (universityFilter) universityFilter.value = "all";
            if (internshipFilter) internshipFilter.value = "all";
            if (searchInput) searchInput.value = "";
            filterApplications();
        });
    }

    const exportButton = document.getElementById("exportCsvBtn");
    if (exportButton) {
        exportButton.addEventListener("click", function () {
            const lines = [["Student", "Email", "Internship", "University", "Skills", "Applied Date", "Status", "Interview", "Disqualification Reason"]];
            rows.forEach(function (row) {
                if (row.hidden) return;
                const cells = row.querySelectorAll("td");
                const interview = row.dataset.status === "Accepted" && row.dataset.interviewDate
                    ? [row.dataset.interviewDate, row.dataset.interviewTime].filter(Boolean).join(" ")
                    : "";
                lines.push([
                    cells[0].querySelector("strong")?.textContent.trim() || "",
                    cells[0].querySelector("span")?.textContent.trim() || "",
                    row.dataset.internship || "",
                    cells[1].textContent.trim(),
                    cells[2].textContent.trim().replace(/\s+/g, " "),
                    cells[3].textContent.trim(),
                    row.dataset.status || "",
                    interview,
                    row.dataset.status === "Disqualified" ? (row.dataset.disqualificationReason || "") : ""
                ]);
            });

            if (lines.length === 1) {
                alert("There are no applications to export.");
                return;
            }

            const csv = lines.map(function (line) {
                return line.map(function (value) {
                    let text = String(value);
                    if (/^[=+\-@\t\r]/.test(text)) text = "'" + text; // stop spreadsheet formula injection
                    return '"' + text.replace(/"/g, '""') + '"';
                }).join(",");
            }).join("\r\n");
            const url = URL.createObjectURL(new Blob(["\ufeff" + csv], { type: "text/csv;charset=utf-8" }));
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
    const radioApplied = document.getElementById("radioDecisionApplied");
    const cardOptApplied = document.getElementById("cardOptApplied");

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
        const accepted = mode === "Accepted";
        const disqualified = mode === "Disqualified";
        const applied = mode === "Applied";

        if (radioAccepted) radioAccepted.checked = accepted;
        if (radioDisqualified) radioDisqualified.checked = disqualified;
        if (radioApplied) radioApplied.checked = applied;
        if (cardOptAccepted) cardOptAccepted.classList.toggle("active", accepted);
        if (cardOptDisqualified) cardOptDisqualified.classList.toggle("active", disqualified);
        if (cardOptApplied) cardOptApplied.classList.toggle("active", applied);

        if (acceptSection) acceptSection.hidden = !accepted;
        if (disqualifySection) disqualifySection.hidden = !disqualified;

        if (inputDate) inputDate.required = accepted;
        if (selectTime) selectTime.required = accepted;
        if (inputLink) inputLink.required = accepted;
        if (selectReason) selectReason.required = disqualified;
        validateInterviewLink();
        validateInterviewDate();
        validateNoteLengths();
        validateReasonNotes();
    }

    // Same rules as the server (applications.php validateApplicationDecision)
    function validateInterviewLink() {
        if (!inputLink || !selectType) return;
        let message = "";
        const value = inputLink.value.trim();
        const video = selectType.value.indexOf("Video") === 0;
        if (radioAccepted && radioAccepted.checked) {
            if (value.length < 3) {
                message = "Please enter a meeting link or location of at least 3 characters.";
            } else if (value.length > 255) {
                message = "The meeting link / location is too long (maximum 255 characters).";
            } else if (video && !/^(https?:\/\/)?[A-Za-z0-9][A-Za-z0-9.\-]*\.[A-Za-z]{2,}(\/\S*)?$/.test(value)) {
                message = "Please enter a valid meeting link, e.g. https://meet.google.com/abc-defg-hij.";
            } else if (!video && (value.length < 3 || !/\p{L}/u.test(value))) {
                message = "Please enter the interview location, e.g. Room 302, Head Office.";
            }
        }
        inputLink.setCustomValidity(message);
    }

    function validateInterviewDate() {
        if (!inputDate) return;
        const now = new Date();
        const today = [now.getFullYear(), String(now.getMonth() + 1).padStart(2, "0"), String(now.getDate()).padStart(2, "0")].join("-");
        const latest = new Date(now.getFullYear() + 1, now.getMonth(), now.getDate());
        const max = [latest.getFullYear(), String(latest.getMonth() + 1).padStart(2, "0"), String(latest.getDate()).padStart(2, "0")].join("-");
        inputDate.min = today;
        inputDate.max = max;
        inputDate.setCustomValidity(inputDate.value && (inputDate.value < today || inputDate.value > max)
            ? (inputDate.value < today ? "The interview date cannot be in the past." : "The interview date must be within the next year.") : "");
    }

    function validateNoteLengths() {
        if (inputNotes) inputNotes.setCustomValidity(inputNotes.value.length > 1000 ? "Interview notes must be under 1000 characters." : "");
        if (inputDisqualifyNotes) inputDisqualifyNotes.setCustomValidity(inputDisqualifyNotes.value.length > 500 ? "Additional feedback must be under 500 characters." : "");
    }

    function validateReasonNotes() {
        if (!selectReason || !inputDisqualifyNotes) return;
        const needsNote = radioDisqualified && radioDisqualified.checked && selectReason.value === "Other";
        inputDisqualifyNotes.setCustomValidity(
            needsNote && inputDisqualifyNotes.value.trim().length < 5
                ? 'Please add a short explanation when the reason is "Other".' : ""
        );
    }

    if (inputLink) inputLink.addEventListener("input", validateInterviewLink);
    if (inputDate) inputDate.addEventListener("change", validateInterviewDate);
    if (inputNotes) inputNotes.addEventListener("input", validateNoteLengths);
    if (inputDisqualifyNotes) inputDisqualifyNotes.addEventListener("input", validateNoteLengths);
    if (selectType) selectType.addEventListener("change", validateInterviewLink);
    if (selectReason) selectReason.addEventListener("change", validateReasonNotes);
    if (inputDisqualifyNotes) inputDisqualifyNotes.addEventListener("input", validateReasonNotes);

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
    if (radioApplied) {
        radioApplied.addEventListener("change", function () {
            if (this.checked) setDecisionMode("Applied");
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
                inputDate.value = [tomorrow.getFullYear(), String(tomorrow.getMonth() + 1).padStart(2, "0"), String(tomorrow.getDate()).padStart(2, "0")].join("-");
            }
        }
        if (selectTime) selectTime.value = Array.from(selectTime.options).some(function (o) { return o.value === interviewTime; }) ? interviewTime : "";
        if (selectType) {
            selectType.value = Array.from(selectType.options).some(function (o) { return o.value === interviewType; })
                ? interviewType : "Video Interview (Google Meet / Zoom)";
        }
        if (inputLink) inputLink.value = interviewLink;
        if (inputNotes) inputNotes.value = interviewNotes;

        // Pre-fill disqualify values
        let matchedOption = "";
        if (selectReason) {
            // The saved text is "Reason - extra notes"
            Array.from(selectReason.options).forEach(opt => {
                if (opt.value && disqReason.startsWith(opt.value)) matchedOption = opt.value;
            });
            selectReason.value = matchedOption || (disqReason ? "Other" : "");
        }
        if (inputDisqualifyNotes) {
            inputDisqualifyNotes.value = matchedOption
                ? disqReason.slice(matchedOption.length).replace(/^\s*-\s*/, "")
                : disqReason;
        }

        // "Undo decision" only makes sense once a decision exists
        if (cardOptApplied) cardOptApplied.hidden = currentStatus === "Applied";

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

    if (decisionForm) {
        decisionForm.addEventListener("submit", function () {
            const saveBtn = document.getElementById("saveDecisionModalBtn");
            if (saveBtn) saveBtn.disabled = true;
        });
    }

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
