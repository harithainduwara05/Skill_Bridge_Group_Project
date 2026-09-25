document.addEventListener("DOMContentLoaded", function () {

    /* =========================================
       ELEMENTS
    ========================================= */

    const periodButtons = document.querySelectorAll(".period-btn");
    const exportPdfBtn = document.getElementById("exportPdfBtn");
    const exportCsvBtn = document.getElementById("exportCsvBtn");
    const viewFullDetailsBtn = document.getElementById("viewFullDetailsBtn");
    const whitepaperBtn = document.getElementById("whitepaperBtn");


    /* =========================================
       PERIOD FILTER
       Last 30 Days / Quarterly / Yearly
    ========================================= */

    periodButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            // Remove active class from all buttons
            periodButtons.forEach(function (btn) {
                btn.classList.remove("active");
            });

            // Add active class to clicked button
            button.classList.add("active");

            const selectedPeriod = button.dataset.period;

            console.log("Selected report period:", selectedPeriod);

            /*
             * Frontend UI only.
             * Later backend data can be loaded here
             * according to selectedPeriod.
             */

        });

    });


    /* =========================================
       EXPORT PDF
    ========================================= */

    if (exportPdfBtn) {

        exportPdfBtn.addEventListener("click", function () {

            /*
             * Opens browser print dialog.
             * User can choose "Save as PDF".
             * No fake PHP page -> no 404.
             */

            window.print();

        });

    }


    /* =========================================
       EXPORT CSV
    ========================================= */

    if (exportCsvBtn) {

        exportCsvBtn.addEventListener("click", function () {

            const reportData = [

                [
                    "Department",
                    "Postings",
                    "Applicants",
                    "Hire Goal",
                    "Status",
                    "Velocity"
                ],

                [
                    "Software Engineering",
                    "12",
                    "1240",
                    "15 / 20",
                    "On Track",
                    "+18%"
                ],

                [
                    "Design & Creative",
                    "4",
                    "420",
                    "2 / 5",
                    "Interviewing",
                    "Stable"
                ],

                [
                    "Data Science",
                    "2",
                    "115",
                    "3 / 3",
                    "Completed",
                    "+5%"
                ]

            ];


            /* Convert data to CSV */

            const csvContent = reportData
                .map(function (row) {

                    return row
                        .map(function (cell) {

                            return '"' +
                                String(cell).replace(/"/g, '""') +
                                '"';

                        })
                        .join(",");

                })
                .join("\n");


            /* Create CSV file */

            const blob = new Blob(
                [csvContent],
                {
                    type: "text/csv;charset=utf-8;"
                }
            );


            const url = URL.createObjectURL(blob);

            const downloadLink = document.createElement("a");

            downloadLink.href = url;

            downloadLink.download =
                "skillbridge-hiring-report.csv";


            document.body.appendChild(downloadLink);

            downloadLink.click();

            document.body.removeChild(downloadLink);

            URL.revokeObjectURL(url);

        });

    }


    /* =========================================
       VIEW FULL DETAILS
    ========================================= */

    if (viewFullDetailsBtn) {

        viewFullDetailsBtn.addEventListener("click", function () {

            /*
             * We do NOT navigate to another page.
             * Therefore there will be no 404 error.
             */

            const hiringSection =
                document.querySelector(".hiring-card");

            if (hiringSection) {

                hiringSection.scrollIntoView({
                    behavior: "smooth",
                    block: "center"
                });

            }

        });

    }


    /* =========================================
       DOWNLOAD WHITEPAPER
       Frontend placeholder
    ========================================= */

    if (whitepaperBtn) {

        whitepaperBtn.addEventListener("click", function () {

            /*
             * Whitepaper file/backend does not
             * exist yet, so don't use fake URL.
             */

            const originalHTML =
                whitepaperBtn.innerHTML;

            whitepaperBtn.textContent =
                "Coming Soon";

            whitepaperBtn.disabled = true;


            setTimeout(function () {

                whitepaperBtn.innerHTML =
                    originalHTML;

                whitepaperBtn.disabled = false;

            }, 1800);

        });

    }

});