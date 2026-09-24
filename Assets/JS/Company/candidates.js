document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       ELEMENTS
    ===================================================== */

    const shortlistBtn = document.getElementById("shortlistBtn");
    const shortlistText = document.getElementById("shortlistText");

    const downloadCvBtn = document.getElementById("downloadCvBtn");
    const resumeDownloadBtn = document.getElementById("resumeDownloadBtn");
    const resumeZoomBtn = document.getElementById("resumeZoomBtn");

    const resumePreview = document.getElementById("resumePreview");

    const toast = document.getElementById("candidateToast");
    const toastMessage = document.getElementById("toastMessage");
    const toastIcon = document.getElementById("toastIcon");

    const projectButtons = document.querySelectorAll(".project-open-btn");

    const certificateButtons = document.querySelectorAll(
        ".certificate-item button"
    );

    const viewAllBtn = document.querySelector(".view-all-btn");

    let shortlisted = false;
    let resumeZoomed = false;
    let toastTimer = null;


    /* =====================================================
       TOAST
    ===================================================== */

    function showToast(message, icon = "check_circle") {

        if (!toast || !toastMessage || !toastIcon) {
            return;
        }

        toastMessage.textContent = message;
        toastIcon.textContent = icon;

        toast.classList.add("show");

        if (toastTimer) {
            clearTimeout(toastTimer);
        }

        toastTimer = setTimeout(function () {
            toast.classList.remove("show");
        }, 2600);
    }


    /* =====================================================
       SHORTLIST
       UI demo only.
       Connect to backend later when shortlist endpoint exists.
    ===================================================== */

    if (shortlistBtn) {

        shortlistBtn.addEventListener("click", function () {

            shortlisted = !shortlisted;

            if (shortlisted) {

                shortlistBtn.classList.add("is-shortlisted");

                if (shortlistText) {
                    shortlistText.textContent = "Shortlisted";
                }

                const icon = shortlistBtn.querySelector(
                    ".material-symbols-outlined"
                );

                if (icon) {
                    icon.textContent = "check_circle";
                }

                showToast(
                    "Liam Henderson added to shortlist."
                );

            } else {

                shortlistBtn.classList.remove("is-shortlisted");

                if (shortlistText) {
                    shortlistText.textContent = "Shortlist";
                }

                const icon = shortlistBtn.querySelector(
                    ".material-symbols-outlined"
                );

                if (icon) {
                    icon.textContent = "star";
                }

                showToast(
                    "Liam Henderson removed from shortlist.",
                    "info"
                );
            }

        });

    }


    /* =====================================================
       DOWNLOAD CV
       Placeholder until actual CV file/backend is connected.
    ===================================================== */

    function handleCvDownload() {

        showToast(
            "CV download will use the candidate's uploaded CV.",
            "description"
        );

    }

    if (downloadCvBtn) {
        downloadCvBtn.addEventListener(
            "click",
            handleCvDownload
        );
    }

    if (resumeDownloadBtn) {
        resumeDownloadBtn.addEventListener(
            "click",
            handleCvDownload
        );
    }


    /* =====================================================
       RESUME ZOOM
    ===================================================== */

    if (resumeZoomBtn && resumePreview) {

        resumeZoomBtn.addEventListener("click", function () {

            resumeZoomed = !resumeZoomed;

            const paper = resumePreview.querySelector(
                ".resume-paper"
            );

            const icon = resumeZoomBtn.querySelector(
                ".material-symbols-outlined"
            );

            if (!paper) {
                return;
            }

            if (resumeZoomed) {

                paper.style.transform = "scale(1.04)";
                paper.style.transformOrigin = "top center";

                if (icon) {
                    icon.textContent = "zoom_out";
                }

            } else {

                paper.style.transform = "scale(1)";

                if (icon) {
                    icon.textContent = "zoom_in";
                }

            }

        });

    }


    /* =====================================================
       PROJECT BUTTONS
    ===================================================== */

    projectButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            const card = button.closest(".portfolio-card");

            const title = card
                ? card.querySelector("h3")
                : null;

            const projectName = title
                ? title.textContent.trim()
                : "Project";

            showToast(
                projectName + " project selected.",
                "open_in_new"
            );

        });

    });


    /* =====================================================
       CERTIFICATES
    ===================================================== */

    certificateButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            const item = button.closest(
                ".certificate-item"
            );

            const certificateName = item
                ? item.querySelector(
                    ".certificate-info strong"
                )
                : null;

            const name = certificateName
                ? certificateName.textContent.trim()
                : "Certificate";

            showToast(
                name + " selected.",
                "verified"
            );

        });

    });


    /* =====================================================
       VIEW ALL PROJECTS
    ===================================================== */

    if (viewAllBtn) {

        viewAllBtn.addEventListener("click", function () {

            showToast(
                "All portfolio projects selected.",
                "folder_open"
            );

        });

    }

});