document.addEventListener('DOMContentLoaded', () => {
    const globalSearchInput = document.getElementById('globalSearchInput');
    const filterRole = document.getElementById('filterRole');
    const filterStatus = document.getElementById('filterStatus');
    const filterSkills = document.getElementById('filterSkills');
    const filterUniversity = document.getElementById('filterUniversity');
    const tbody = document.getElementById('applicationsTbody');
    const rows = tbody.querySelectorAll('tr');

    // Filter applications based on inputs
    function filterApplications() {
        const query = globalSearchInput.value.toLowerCase().trim();
        const selectedStatus = filterStatus.value.toLowerCase();
        const selectedSkill = filterSkills.value.toLowerCase();
        const selectedUniversity = filterUniversity.value.toLowerCase();

        rows.forEach(row => {
            const studentName = row.querySelector('.student-name').textContent.toLowerCase();
            const studentEmail = row.querySelector('.student-email').textContent.toLowerCase();
            const university = row.querySelector('.university-name').textContent.toLowerCase();
            const status = row.querySelector('.status-badge').textContent.toLowerCase();
            const skills = Array.from(row.querySelectorAll('.skill-pill')).map(s => s.textContent.toLowerCase());

            const matchesSearch = !query || studentName.includes(query) || studentEmail.includes(query) || university.includes(query);
            const matchesStatus = !selectedStatus || status.includes(selectedStatus);
            const matchesUniversity = !selectedUniversity || university.includes(selectedUniversity);
            const matchesSkill = !selectedSkill || skills.some(s => s.includes(selectedSkill));

            if (matchesSearch && matchesStatus && matchesUniversity && matchesSkill) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Attach Event Listeners to Search and Dropdowns
    if (globalSearchInput) globalSearchInput.addEventListener('input', filterApplications);
    if (filterRole) filterRole.addEventListener('change', filterApplications);
    if (filterStatus) filterStatus.addEventListener('change', filterApplications);
    if (filterSkills) filterSkills.addEventListener('change', filterApplications);
    if (filterUniversity) filterUniversity.addEventListener('change', filterApplications);

    // Star candidate toggle
    tbody.addEventListener('click', (e) => {
        const starBtn = e.target.closest('.btn-star');
        if (starBtn) {
            starBtn.classList.toggle('starred');
            const icon = starBtn.querySelector('i');
            if (starBtn.classList.contains('starred')) {
                icon.className = 'fa-solid fa-star';
            } else {
                icon.className = 'fa-regular fa-star';
            }
        }

        // Action: Reject application confirmation
        const rejectBtn = e.target.closest('.btn-reject');
        if (rejectBtn) {
            const row = rejectBtn.closest('tr');
            const studentName = row.querySelector('.student-name').textContent;
            if (confirm(`Are you sure you want to mark ${studentName}'s application as rejected?`)) {
                const badge = row.querySelector('.status-badge');
                badge.textContent = 'Rejected';
                badge.className = 'status-badge badge-rejected';
            }
        }
    });

    // Export CSV Button Mock
    const btnExportCsv = document.getElementById('btnExportCsv');
    if (btnExportCsv) {
        btnExportCsv.addEventListener('click', () => {
            alert('Exporting applications data as CSV...');
        });
    }

    // Bulk Action Button Mock
    const btnBulkAction = document.getElementById('btnBulkAction');
    if (btnBulkAction) {
        btnBulkAction.addEventListener('click', () => {
            alert('Bulk Action menu triggered.');
        });
    }
});
