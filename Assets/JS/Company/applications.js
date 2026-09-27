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
});
