document.addEventListener("DOMContentLoaded", function () {

    /* =========================================================
       ELEMENTS
    ========================================================= */

    const scheduleModal =
        document.getElementById("scheduleInterviewModal");

    const viewModal =
        document.getElementById("viewInterviewModal");

    const editModal =
        document.getElementById("editInterviewModal");

    const scheduleForm =
        document.getElementById("scheduleInterviewForm");

    const editForm =
        document.getElementById("editInterviewForm");

    const viewButtons =
        document.querySelectorAll(".view-interview-btn");

    const editButtons =
        document.querySelectorAll(".edit-interview-btn");

    const closeButtons =
        document.querySelectorAll("[data-close-modal]");

    let currentEditRow = null;


    /* =========================================================
       MODAL HELPERS
    ========================================================= */

    function openModal(modal) {

        if (!modal) {
            return;
        }

        modal.classList.add("active");

        document.body.style.overflow = "hidden";
    }


    function closeModal(modal) {

        if (!modal) {
            return;
        }

        modal.classList.remove("active");

        const anyModalOpen =
            document.querySelector(
                ".interview-modal-overlay.active"
            );

        if (!anyModalOpen) {
            document.body.style.overflow = "";
        }
    }


    function closeAllModals() {

        document
            .querySelectorAll(".interview-modal-overlay")
            .forEach(function (modal) {

                modal.classList.remove("active");

            });

        document.body.style.overflow = "";
    }


    /* =========================================================
       SCHEDULE INTERVIEW
    ========================================================= */

    document.querySelectorAll(".schedule-row-interview-btn").forEach(function (button) {

        button.addEventListener("click", function () {

            const row = button.closest(".interview-row");

            if (!row) {
                return;
            }

            document.getElementById("scheduleCandidate").value =
                row.dataset.student || "";

            document.getElementById("scheduleInternship").value =
                row.dataset.internship || "";

            openModal(scheduleModal);

        });

    });


    /* =========================================================
       CLOSE BUTTONS
    ========================================================= */

    closeButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                const modal =
                    button.closest(
                        ".interview-modal-overlay"
                    );

                closeModal(modal);

            }
        );

    });


    /* =========================================================
       CLICK OUTSIDE MODAL
    ========================================================= */

    document
        .querySelectorAll(".interview-modal-overlay")
        .forEach(function (overlay) {

            overlay.addEventListener(
                "click",
                function (event) {

                    if (event.target === overlay) {

                        closeModal(overlay);

                    }

                }
            );

        });


    /* =========================================================
       ESC KEY
    ========================================================= */

    document.addEventListener(
        "keydown",
        function (event) {

            if (event.key === "Escape") {

                closeAllModals();

            }

        }
    );


    /* =========================================================
       VIEW INTERVIEW
    ========================================================= */

    viewButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                const row =
                    button.closest(".interview-row");

                if (!row) {
                    return;
                }

                const student =
                    row.dataset.student || "-";

                const internship =
                    row.dataset.internship || "-";

                const team =
                    row.dataset.team || "-";

                const date =
                    row.dataset.date || "-";

                const time =
                    row.dataset.time || "-";

                const status =
                    row.dataset.status || "-";


                document.getElementById(
                    "viewStudentName"
                ).textContent = student;

                document.getElementById(
                    "viewInternship"
                ).textContent = internship;

                document.getElementById(
                    "viewTeam"
                ).textContent = team;

                document.getElementById(
                    "viewDate"
                ).textContent = date;

                document.getElementById(
                    "viewTime"
                ).textContent = time;

                document.getElementById(
                    "viewStatus"
                ).textContent = status;


                openModal(viewModal);

            }
        );

    });


    /* =========================================================
       EDIT INTERVIEW
    ========================================================= */

    editButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                const row =
                    button.closest(".interview-row");

                if (!row) {
                    return;
                }

                currentEditRow = row;


                document.getElementById(
                    "editCandidate"
                ).value =
                    row.dataset.student || "";


                document.getElementById(
                    "editInternship"
                ).value =
                    row.dataset.internship || "";


                document.getElementById(
                    "editDate"
                ).value =
                    row.dataset.date || "";


                document.getElementById(
                    "editTime"
                ).value =
                    row.dataset.time || "";


                document.getElementById(
                    "editStatus"
                ).value =
                    row.dataset.status || "Applied";


                openModal(editModal);

            }
        );

    });


    /* =========================================================
       EDIT FORM - FRONTEND DEMO
    ========================================================= */

    if (editForm) {

        editForm.addEventListener(
            "submit",
            function (event) {

                event.preventDefault();

                if (!currentEditRow) {
                    return;
                }


                const internship =
                    document
                        .getElementById("editInternship")
                        .value
                        .trim();


                const date =
                    document
                        .getElementById("editDate")
                        .value
                        .trim();


                const time =
                    document
                        .getElementById("editTime")
                        .value
                        .trim();


                const status =
                    document
                        .getElementById("editStatus")
                        .value;


                currentEditRow.dataset.internship =
                    internship;

                currentEditRow.dataset.date =
                    date;

                currentEditRow.dataset.time =
                    time;

                currentEditRow.dataset.status =
                    status;


                /*
                 * Update visible internship title.
                 */

                const internshipTitle =
                    currentEditRow.querySelector(
                        ".internship-info strong"
                    );

                if (internshipTitle) {

                    internshipTitle.textContent =
                        internship;

                }


                /*
                 * Update visible date.
                 */

                const dateTitle =
                    currentEditRow.querySelector(
                        ".date-info strong"
                    );

                if (dateTitle) {

                    dateTitle.textContent =
                        date;

                }


                /*
                 * Update visible time.
                 */

                const timeElement =
                    currentEditRow.querySelector(
                        ".date-info small"
                    );

                if (timeElement) {

                    timeElement.innerHTML =
                        '<span class="material-symbols-outlined">' +
                        'schedule' +
                        '</span>' +
                        time;

                }


                /*
                 * Update status.
                 */

                const statusElement =
                    currentEditRow.querySelector(
                        ".interview-status"
                    );

                if (statusElement) {

                    statusElement.textContent =
                        status;

                    statusElement.classList.remove(
                        "applied",
                        "interviewing",
                        "hired"
                    );

                    statusElement.classList.add(
                        status.toLowerCase()
                    );

                }


                closeModal(editModal);

                currentEditRow = null;

            }
        );

    }


    /* =========================================================
       SCHEDULE FORM - FRONTEND DEMO
    ========================================================= */

    if (scheduleForm) {

        scheduleForm.addEventListener(
            "submit",
            function (event) {

                event.preventDefault();

                /*
                 * UI-only for now.
                 *
                 * No fake database/backend operation is
                 * performed here.
                 */

                closeModal(scheduleModal);

                scheduleForm.reset();

            }
        );

    }


    /* =========================================================
       FILTER BUTTON
    ========================================================= */

    const filterButton =
        document.getElementById(
            "filterInterviewsBtn"
        );

    if (filterButton) {

        filterButton.addEventListener(
            "click",
            function () {

                /*
                 * UI placeholder.
                 * Does not navigate to a missing page.
                 */

                filterButton.classList.toggle(
                    "active"
                );

            }
        );

    }


    /* =========================================================
       DOWNLOAD BUTTON
    ========================================================= */

    const downloadButton =
        document.getElementById(
            "downloadInterviewsBtn"
        );

    if (downloadButton) {

        downloadButton.addEventListener(
            "click",
            function () {

                /*
                 * Keep user on this page.
                 * Backend export can be connected later.
                 */

            }
        );

    }


    /* =========================================================
       PAGINATION
    ========================================================= */

    const previousButton =
        document.getElementById(
            "previousInterviewPage"
        );

    const nextButton =
        document.getElementById(
            "nextInterviewPage"
        );


    if (previousButton) {

        previousButton.addEventListener(
            "click",
            function () {

                /*
                 * Frontend placeholder.
                 * No invalid navigation.
                 */

            }
        );

    }


    if (nextButton) {

        nextButton.addEventListener(
            "click",
            function () {

                /*
                 * Frontend placeholder.
                 * No invalid navigation.
                 */

            }
        );

    }

});
