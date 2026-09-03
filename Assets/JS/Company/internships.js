document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("internshipSearch");
    const statusFilter = document.getElementById("statusFilter");
    const rows = document.querySelectorAll(".internship-row");


    function filterInternships() {

        const searchValue = searchInput
            ? searchInput.value.toLowerCase().trim()
            : "";

        const statusValue = statusFilter
            ? statusFilter.value
            : "all";


        rows.forEach(function (row) {

            const text = row.innerText.toLowerCase();
            const rowStatus = row.dataset.status;

            const matchesSearch = text.includes(searchValue);

            const matchesStatus =
                statusValue === "all" ||
                rowStatus === statusValue;


            if (matchesSearch && matchesStatus) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }

        });

    }


    if (searchInput) {
        searchInput.addEventListener("input", filterInternships);
    }


    if (statusFilter) {
        statusFilter.addEventListener("change", filterInternships);
    }


    const deleteForms = document.querySelectorAll(".delete-form");


    deleteForms.forEach(function (form) {

        form.addEventListener("submit", function (event) {

            const confirmed = confirm(
                "Are you sure you want to delete this internship?"
            );

            if (!confirmed) {
                event.preventDefault();
            }

        });

    });

});