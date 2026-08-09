// ── Flash Toast Auto-dismiss ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('flashToast');
    if (toast) {
        setTimeout(() => {
            toast.style.transition = 'opacity .4s ease, transform .4s ease';
            toast.style.opacity    = '0';
            toast.style.transform  = 'translateX(120%)';
            setTimeout(() => toast.remove(), 400);
        }, 4000); // 4 seconds කට පස්සේ ඉබේ disappear වෙනවා
    }
});

// ── Add Modal ─────────────────────────────────────────────────────────────────
const addModal = document.getElementById('addModal');
document.getElementById('btnAddUniversity').addEventListener('click',  () => addModal.classList.add('open'));
document.getElementById('closeAddModal').addEventListener('click',     () => addModal.classList.remove('open'));
document.getElementById('cancelAddModal').addEventListener('click',    () => addModal.classList.remove('open'));
addModal.addEventListener('click', e => { if (e.target === addModal) addModal.classList.remove('open'); });

// ── View Modal ────────────────────────────────────────────────────────────────
const viewModal   = document.getElementById('viewModal');
const viewContent = document.getElementById('viewModalContent');

function openViewModal(name, domain, location, students, status, city) {
    const badgeCls = status === 'Active' ? 'active' : status === 'Pending' ? 'pending' : 'inactive-badge';
    viewContent.innerHTML =
        detailRow('University',   escHtml(name)) +
        detailRow('Email Domain', '<span class="univ-domain-badge">@' + escHtml(domain) + '</span>') +
        detailRow('City',         escHtml(city)) +
        detailRow('Location',     escHtml(location)) +
        detailRow('Students',     escHtml(students)) +
        detailRow('Status',       '<span class="badge-status ' + badgeCls + '">' + escHtml(status) + '</span>');
    viewModal.classList.add('open');
}

function detailRow(label, val) {
    return '<div class="univ-detail-row">' +
           '<span class="univ-detail-label">' + label + '</span>' +
           '<span class="univ-detail-value">'  + val   + '</span>' +
           '</div>';
}

document.getElementById('closeViewModal').addEventListener('click', () => viewModal.classList.remove('open'));
viewModal.addEventListener('click', e => { if (e.target === viewModal) viewModal.classList.remove('open'); });

function escHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str || '—'));
    return d.innerHTML;
}

// ── Client-side live search ───────────────────────────────────────────────────
document.getElementById('univSearchInput').addEventListener('input', function () {
    const q    = this.value.toLowerCase();
    const rows = document.querySelectorAll('#univTable tbody tr');
    rows.forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

// ── Export CSV ────────────────────────────────────────────────────────────────
document.getElementById('exportBtn').addEventListener('click', function () {
    const table = document.getElementById('univTable');
    const rows  = [];
    const heads = [...table.querySelectorAll('thead th')].map(th => '"' + th.textContent.trim() + '"');
    rows.push(heads.join(','));
    table.querySelectorAll('tbody tr').forEach(row => {
        const cells = [...row.querySelectorAll('td')];
        if (cells.length < 2) return;
        rows.push(cells.map(td => '"' + td.textContent.trim().replace(/\s+/g, ' ').replace(/"/g, '""') + '"').join(','));
    });
    const blob = new Blob([rows.join('\n')], { type: 'text/csv' });
    const url  = URL.createObjectURL(blob);
    const a    = Object.assign(document.createElement('a'), { href: url, download: 'universities.csv' });
    a.click();
    URL.revokeObjectURL(url);
});
