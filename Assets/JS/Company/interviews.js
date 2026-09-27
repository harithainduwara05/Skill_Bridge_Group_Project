document.addEventListener("DOMContentLoaded", function () {
    const rows = Array.from(document.querySelectorAll(".interview-row"));
    const scheduleModal = document.getElementById("scheduleInterviewModal");
    const viewModal = document.getElementById("viewInterviewModal");
    const editModal = document.getElementById("editInterviewModal");
    const disqualifyModal = document.getElementById("disqualifyInterviewModal");
    const disqualifyForm = document.getElementById("disqualifyInterviewForm");
    const reasonSelect = document.getElementById("disqualificationReason");
    const otherGroup = document.getElementById("otherDisqualificationGroup");
    const otherNote = document.getElementById("otherDisqualificationNote");
    let currentEditRow = null;
    let pendingDisqualificationRow = null;
    let pendingEditValues = null;

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

    function refreshHiredCount() {
        const hiredCount = document.getElementById("hiredInterviewCount");
        if (hiredCount) hiredCount.textContent = String(rows.filter(row => row.dataset.status === "Hired").length);
    }

    function refreshRowActions(row) {
        const actions = row.querySelector(".interview-actions");
        if (!actions) return;
        actions.querySelectorAll(".interview-outcome-btn").forEach(button => button.remove());
        const isInterviewing = row.dataset.status === "Interviewing";
        actions.querySelectorAll(".schedule-row-interview-btn, .edit-interview-btn").forEach(button => {
            button.hidden = !isInterviewing;
        });
        if (isInterviewing) {
            const hiredButton = document.createElement("button");
            hiredButton.type = "button";
            hiredButton.className = "interview-action-btn interview-outcome-btn mark-hired-btn";
            hiredButton.title = "Mark as Hired";
            hiredButton.setAttribute("aria-label", "Mark as Hired");
            hiredButton.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true">how_to_reg</span>';
            actions.appendChild(hiredButton);

            const disqualifyButton = document.createElement("button");
            disqualifyButton.type = "button";
            disqualifyButton.className = "interview-action-btn interview-outcome-btn disqualify-interview-btn";
            disqualifyButton.title = "Disqualify";
            disqualifyButton.setAttribute("aria-label", "Disqualify");
            disqualifyButton.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true">person_off</span>';
            actions.appendChild(disqualifyButton);
        }
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
        refreshHiredCount();
    }

    function startDisqualification(row, editValues = null) {
        pendingDisqualificationRow = row;
        pendingEditValues = editValues;
        if (disqualifyForm) disqualifyForm.reset();
        if (otherGroup) otherGroup.hidden = true;
        if (otherNote) otherNote.required = false;
        closeModal(editModal);
        openModal(disqualifyModal);
    }

    function applyEditValues(row, values) {
        if (!row || !values) return;
        row.dataset.internship = values.internship;
        row.dataset.date = values.date;
        row.dataset.time = values.time;
        const internshipTitle = row.querySelector(".internship-info strong");
        const dateTitle = row.querySelector(".date-info strong");
        const timeElement = row.querySelector(".date-info small");
        if (internshipTitle) internshipTitle.textContent = values.internship;
        if (dateTitle) dateTitle.textContent = values.date;
        if (timeElement) {
            const icon = document.createElement("span");
            icon.className = "material-symbols-outlined";
            icon.textContent = "schedule";
            timeElement.replaceChildren(icon, document.createTextNode(" " + values.time));
        }
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

    rows.forEach(row => {
        if (!["Interviewing", "Hired", "Disqualified"].includes(row.dataset.status)) {
            setInterviewStatus(row, "Interviewing");
        } else {
            refreshRowActions(row);
        }
        const viewButton = row.querySelector(".view-interview-btn");
        if (viewButton) viewButton.addEventListener("click", () => showView(row));

        const scheduleButton = row.querySelector(".schedule-row-interview-btn");
        if (scheduleButton) scheduleButton.addEventListener("click", () => {
            const candidate = document.getElementById("scheduleCandidate");
            const internship = document.getElementById("scheduleInternship");
            if (candidate) candidate.value = row.dataset.student || "";
            if (internship) internship.value = row.dataset.internship || "";
            openModal(scheduleModal);
        });

        const editButton = row.querySelector(".edit-interview-btn");
        if (editButton) editButton.addEventListener("click", () => {
            currentEditRow = row;
            const fields = {
                editCandidate: row.dataset.student,
                editInternship: row.dataset.internship,
                editDate: row.dataset.date,
                editTime: row.dataset.time,
                editStatus: row.dataset.status
            };
            Object.entries(fields).forEach(([id, value]) => {
                const field = document.getElementById(id);
                if (field) field.value = value || "";
            });
            openModal(editModal);
        });
    });
    refreshHiredCount();

    document.addEventListener("click", function (event) {
        const hiredButton = event.target.closest(".mark-hired-btn");
        if (hiredButton) {
            setInterviewStatus(hiredButton.closest(".interview-row"), "Hired");
            return;
        }
        const disqualifyButton = event.target.closest(".disqualify-interview-btn");
        if (disqualifyButton) startDisqualification(disqualifyButton.closest(".interview-row"));
    });

    if (reasonSelect) reasonSelect.addEventListener("change", function () {
        const isOther = reasonSelect.value === "Other";
        if (otherGroup) otherGroup.hidden = !isOther;
        if (otherNote) {
            otherNote.required = isOther;
            if (!isOther) otherNote.value = "";
        }
    });

    if (disqualifyForm) disqualifyForm.addEventListener("submit", function (event) {
        event.preventDefault();
        if (!pendingDisqualificationRow || !reasonSelect || !reasonSelect.value) return;
        let reason = reasonSelect.value;
        if (reason === "Other") {
            const note = otherNote ? otherNote.value.trim() : "";
            if (!note) {
                if (otherNote) otherNote.focus();
                return;
            }
            reason += ": " + note;
        }
        applyEditValues(pendingDisqualificationRow, pendingEditValues);
        setInterviewStatus(pendingDisqualificationRow, "Disqualified", reason);
        closeModal(disqualifyModal);
        disqualifyForm.reset();
        pendingDisqualificationRow = null;
        pendingEditValues = null;
    });

    const editForm = document.getElementById("editInterviewForm");
    if (editForm) editForm.addEventListener("submit", function (event) {
        event.preventDefault();
        if (!currentEditRow) return;
        const values = {
            internship: document.getElementById("editInternship").value.trim(),
            date: document.getElementById("editDate").value.trim(),
            time: document.getElementById("editTime").value.trim()
        };
        const status = document.getElementById("editStatus").value;
        if (status === "Disqualified") {
            startDisqualification(currentEditRow, values);
            currentEditRow = null;
            return;
        }
        applyEditValues(currentEditRow, values);
        setInterviewStatus(currentEditRow, status);
        closeModal(editModal);
        currentEditRow = null;
    });

    document.querySelectorAll("[data-close-modal]").forEach(button => button.addEventListener("click", function () {
        const modal = button.closest(".interview-modal-overlay");
        closeModal(modal);
        if (modal === disqualifyModal) {
            pendingDisqualificationRow = null;
            pendingEditValues = null;
            if (disqualifyForm) disqualifyForm.reset();
            if (otherGroup) otherGroup.hidden = true;
            if (otherNote) otherNote.required = false;
        }
    }));

    document.querySelectorAll(".interview-modal-overlay").forEach(overlay => overlay.addEventListener("click", function (event) {
        if (event.target !== overlay) return;
        closeModal(overlay);
        if (overlay === disqualifyModal) {
            pendingDisqualificationRow = null;
            pendingEditValues = null;
            if (disqualifyForm) disqualifyForm.reset();
            if (otherGroup) otherGroup.hidden = true;
            if (otherNote) otherNote.required = false;
        }
    }));
    document.addEventListener("keydown", event => {
        if (event.key !== "Escape") return;
        document.querySelectorAll(".interview-modal-overlay.active").forEach(closeModal);
        pendingDisqualificationRow = null;
        pendingEditValues = null;
    });

    const scheduleForm = document.getElementById("scheduleInterviewForm");
    if (scheduleForm) scheduleForm.addEventListener("submit", function (event) {
        event.preventDefault();
        closeModal(scheduleModal);
        scheduleForm.reset();
    });

    const filterButton = document.getElementById("filterInterviewsBtn");
    if (filterButton) filterButton.addEventListener("click", () => filterButton.classList.toggle("active"));
});
