document.addEventListener("DOMContentLoaded", function () {

    const roleFilter = document.getElementById("roleFilter");
    const statusFilter = document.getElementById("statusFilter");
    const skillFilter = document.getElementById("skillFilter");
    const universityFilter = document.getElementById("universityFilter");

    const rows = document.querySelectorAll(
        "#applicationsTable tbody tr"
    );


    /* ===============================
       FILTER APPLICATIONS
    =============================== */

    function filterApplications() {

        const role = roleFilter.value;
        const status = statusFilter.value;
        const skill = skillFilter.value;
        const university = universityFilter.value;

        rows.forEach(function (row) {

            const rowRole = row.dataset.role;
            const rowStatus = row.dataset.status;
            const rowSkill = row.dataset.skill || "";
            const rowUniversity = row.dataset.university;

            const roleMatch =
                role === "all" || rowRole === role;

            const statusMatch =
                status === "all" || rowStatus === status;

            const skillMatch =
                skill === "all" ||
                rowSkill.includes(skill);

            const universityMatch =
                university === "all" ||
                rowUniversity === university;

            if (
                roleMatch &&
                statusMatch &&
                skillMatch &&
                universityMatch
            ) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }


    roleFilter.addEventListener(
        "change",
        filterApplications
    );

    statusFilter.addEventListener(
        "change",
        filterApplications
    );

    skillFilter.addEventListener(
        "change",
        filterApplications
    );

    universityFilter.addEventListener(
        "change",
        filterApplications
    );


    /* ===============================
       SHORTLIST BUTTON
    =============================== */

    document.querySelectorAll(
        ".shortlist-btn"
    ).forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                this.classList.toggle("active");

                const icon =
                    this.querySelector(
                        ".material-symbols-outlined"
                    );

                if (this.classList.contains("active")) {

                    icon.style.fontVariationSettings =
                        "'FILL' 1";

                    this.title = "Shortlisted";

                } else {

                    icon.style.fontVariationSettings =
                        "'FILL' 0";

                    this.title = "Shortlist";
                }
            }
        );
    });


    /* ===============================
       PAGINATION VISUAL STATE
    =============================== */

    document.querySelectorAll(
        ".page-number"
    ).forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                document.querySelectorAll(
                    ".page-number"
                ).forEach(function (item) {
                    item.classList.remove("active");
                });

                this.classList.add("active");
            }
        );
    });


    /* ===============================
       EXPORT CSV
    =============================== */

    const exportButton =
        document.getElementById("exportCsvBtn");

    exportButton.addEventListener(
        "click",
        function () {

            let csv =
                "Student,University,Skills,Applied Date,Status\n";

            rows.forEach(function (row) {

                if (row.style.display === "none") {
                    return;
                }

                const cells =
                    row.querySelectorAll("td");

                const student =
                    cells[0]
                        .querySelector("strong")
                        .innerText.trim();

                const university =
                    cells[1]
                        .innerText
                        .trim()
                        .replace(/\n/g, " ");

                const skills =
                    cells[2]
                        .innerText
                        .trim()
                        .replace(/\n/g, " ");

                const date =
                    cells[3]
                        .innerText
                        .trim()
                        .replace(/\n/g, " ");

                const status =
                    cells[4]
                        .innerText
                        .trim();

                csv +=
                    `"${student}",` +
                    `"${university}",` +
                    `"${skills}",` +
                    `"${date}",` +
                    `"${status}"\n`;
            });

            const blob =
                new Blob(
                    [csv],
                    {
                        type:
                            "text/csv;charset=utf-8;"
                    }
                );

            const url =
                URL.createObjectURL(blob);

            const link =
                document.createElement("a");

            link.href = url;

            link.download =
                "skillbridge-applications.csv";

            document.body.appendChild(link);

            link.click();

            document.body.removeChild(link);

            URL.revokeObjectURL(url);
        }
    );

});