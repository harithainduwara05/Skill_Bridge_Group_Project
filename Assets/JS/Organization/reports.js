/*
| Organization - reports page script
| Used by: Functions/Organization/reports.php
| Values from PHP / the database come from ORG_DATA (set in a small <script> on the page).
*/

const RP = ORG_DATA.RP;

/* ================= BUILDER ================= */
(function () {
    const form = document.getElementById('rpForm');

    // new report type -> start fresh with that type's own filters and columns
    form.querySelectorAll('input[name="type"]').forEach(r => r.addEventListener('change', () => {
        const p = new URLSearchParams({ type: r.value, period: form.period.value });
        if (form.period.value === 'custom') { p.set('from', form.from.value); p.set('to', form.to.value); }
        location.href = 'reports.php?' + p.toString();
    }));

    // custom date range
    const period = document.getElementById('rpPeriod');
    period.addEventListener('change', () => {
        document.getElementById('rpCustom').hidden = period.value !== 'custom';
        if (period.value !== 'custom') form.requestSubmit();
    });

    // a filter changed -> update the report straight away
    form.querySelectorAll('select:not(#rpPeriod)').forEach(el =>
        el.addEventListener('change', () => form.requestSubmit()));   // requestSubmit = goes through the check below

    // "Customize the table and download": All / None for the table details
    document.querySelectorAll('[data-cols]').forEach(b => b.addEventListener('click', () => {
        document.querySelectorAll('#rpCols input').forEach(c => c.checked = b.dataset.cols === 'all');
        if (colsErr.textContent) checkBuilder();
    }));

    // ---- Check the builder before the report is made (messages under the fields, no popups) ----
    const dateErr = document.getElementById('rpDateErr');
    const colsErr = document.getElementById('rpColsErr');
    function localToday() {
        const d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }
    function checkBuilder() {
        let dateMsg = '';
        if (form.period.value === 'custom') {
            const from = form.from.value, to = form.to.value;
            if (!from || !to)          dateMsg = 'Pick both a "From" and a "To" date.';
            else if (from > to)        dateMsg = 'The "From" date must be on or before the "To" date.';
            else if (to > localToday()) dateMsg = 'The "To" date can’t be in the future.';
        }
        dateErr.textContent = dateMsg;
        colsErr.textContent = document.querySelector('#rpCols input:checked') ? '' : 'Pick at least one detail for the table.';
        if (colsErr.textContent) form.querySelector('.rp-more').open = true;
        return !dateMsg && !colsErr.textContent;
    }
    form.addEventListener('submit', e => {
        if (!checkBuilder()) {
            e.preventDefault();
            (dateErr.textContent ? dateErr : colsErr).scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
    // clear the message once it is fixed
    form.from.addEventListener('change', () => { if (dateErr.textContent) checkBuilder(); });
    form.to.addEventListener('change', () => { if (dateErr.textContent) checkBuilder(); });
    document.getElementById('rpCols').addEventListener('change', () => { if (colsErr.textContent) checkBuilder(); });
})();

/* =================================================================
   CHARTS – drawn with plain JavaScript on <canvas> (no chart library)
   line: trend over time | bar: vertical or horizontal | doughnut
================================================================= */
const RP_COLORS = ['#0f3a66', '#2563eb', '#0f766e', '#f59e0b', '#dc2626', '#7c3aed', '#64748b', '#0891b2'];
const RP_STATUS_COLORS = {
    'On Track': '#16a34a', 'Behind Schedule': '#dc2626', 'Completed': '#64748b', 'Accepted': '#16a34a', 'Rejected': '#dc2626',
    'Pending': '#f59e0b', 'Active': '#2563eb', 'Reviewing': '#f59e0b', 'Open': '#0891b2', 'Hold': '#dc2626',
    'Draft': '#94a3b8', 'Below 50%': '#dc2626', '50% – 79%': '#f59e0b', '80% and above': '#16a34a'
};
const RP_FONT = "Inter, system-ui, -apple-system, 'Segoe UI', sans-serif";
const RP_TEXT = '#4b5563', RP_GRID = '#eef1f5', RP_BAR = '#0f3a66', RP_LINE = '#2563eb';

// round number for the top of an axis: 7 -> 8, 23 -> 25, 140 -> 150
function rpNiceMax(v) {
    if (v <= 0) return 1;
    if (v <= 5) return Math.ceil(v);
    const p = Math.pow(10, Math.floor(Math.log10(v)));
    const n = v / p;
    return (n <= 1 ? 1 : n <= 2 ? 2 : n <= 2.5 ? 2.5 : n <= 5 ? 5 : 10) * p;
}

// cut a label so it fits in a width, with "…"
function rpFit(g, text, maxW) {
    text = String(text);
    if (g.measureText(text).width <= maxW) return text;
    while (text.length > 1 && g.measureText(text + '…').width > maxW) text = text.slice(0, -1);
    return text + '…';
}

function rpRoundRect(g, x, y, w, h, r) {
    r = Math.max(0, Math.min(r, w / 2, h / 2));
    g.beginPath();
    g.moveTo(x + r, y);
    g.lineTo(x + w - r, y); g.quadraticCurveTo(x + w, y, x + w, y + r);
    g.lineTo(x + w, y + h - r); g.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
    g.lineTo(x + r, y + h); g.quadraticCurveTo(x, y + h, x, y + h - r);
    g.lineTo(x, y + r); g.quadraticCurveTo(x, y, x + r, y);
    g.closePath();
}

// value shown on an axis / next to a bar
function rpNum(v, max) {
    return max === 5 ? (Math.round(v * 10) / 10).toString() : Math.round(v).toString();
}

function rpDrawChart(canvas, ch) {
    const box = canvas.parentElement;
    const W = box.clientWidth, H = box.clientHeight;
    if (!W || !H) return;

    // draw at 2x (or more) so it stays sharp on screen and in the PDF
    const dpr = Math.max(2, window.devicePixelRatio || 1);
    canvas.width = Math.round(W * dpr);
    canvas.height = Math.round(H * dpr);
    const g = canvas.getContext('2d');
    g.setTransform(dpr, 0, 0, dpr, 0, 0);
    g.clearRect(0, 0, W, H);
    g.font = '12px ' + RP_FONT;
    g.textBaseline = 'middle';

    const labels = ch.labels.map(String);
    const data = ch.series[0].data.map(v => Number(v) || 0);
    const colors = labels.map((l, i) => RP_STATUS_COLORS[l] || RP_COLORS[i % RP_COLORS.length]);

    if (ch.type === 'doughnut') return rpDoughnut(g, W, H, labels, data, colors);
    if (ch.horizontal)          return rpHBar(g, W, H, labels, data, ch.max);
    return rpVChart(g, W, H, labels, data, ch.max, ch.type === 'line');
}

// ---------- vertical bars / line ----------
function rpVChart(g, W, H, labels, data, fixedMax, isLine) {
    const max = fixedMax || rpNiceMax(Math.max(...data, 0));
    const ticks = fixedMax || max >= 5 ? 5 : Math.max(1, Math.ceil(max));   // small counts: 0, 1, 2 ... (no repeated numbers)
    const tickText = i => rpNum(max / ticks * i, fixedMax);
    const left = Math.max(...Array.from({ length: ticks + 1 }, (_, i) => g.measureText(tickText(i)).width)) + 12;
    const top = 16, right = 10, bottom = 26;
    const pw = W - left - right, ph = H - top - bottom;
    const n = Math.max(1, labels.length);
    const slot = pw / n;
    const yOf = v => top + ph - (Math.min(v, max) / max) * ph;
    const xOf = i => left + slot * i + slot / 2;

    // grid + y axis
    g.textAlign = 'right';
    for (let i = 0; i <= ticks; i++) {
        const y = top + ph - (ph / ticks) * i;
        g.strokeStyle = RP_GRID; g.lineWidth = 1;
        g.beginPath(); g.moveTo(left, Math.round(y) + 0.5); g.lineTo(W - right, Math.round(y) + 0.5); g.stroke();
        g.fillStyle = RP_TEXT; g.fillText(tickText(i), left - 8, y);
    }

    // x labels: skip some when they don't fit
    g.textAlign = 'center';
    const widest = Math.max(...labels.map(l => g.measureText(l).width), 1);
    const every = Math.max(1, Math.ceil((widest + 10) / slot));
    labels.forEach((l, i) => {
        if (i % every !== 0) return;
        g.fillStyle = RP_TEXT;
        g.fillText(rpFit(g, l, slot * every - 6), xOf(i), H - bottom / 2);
    });

    if (isLine) {
        // area + line + points
        g.beginPath();
        data.forEach((v, i) => i ? g.lineTo(xOf(i), yOf(v)) : g.moveTo(xOf(i), yOf(v)));
        g.lineTo(xOf(n - 1), top + ph); g.lineTo(xOf(0), top + ph); g.closePath();
        g.fillStyle = 'rgba(37,99,235,0.12)'; g.fill();

        g.beginPath();
        data.forEach((v, i) => i ? g.lineTo(xOf(i), yOf(v)) : g.moveTo(xOf(i), yOf(v)));
        g.strokeStyle = RP_LINE; g.lineWidth = 2; g.lineJoin = 'round'; g.stroke();

        if (n <= 40) {
            data.forEach((v, i) => {
                g.beginPath(); g.arc(xOf(i), yOf(v), 3, 0, Math.PI * 2);
                g.fillStyle = '#fff'; g.fill(); g.strokeStyle = RP_LINE; g.lineWidth = 2; g.stroke();
            });
        }
        return;
    }

    // bars + value on top
    const bw = Math.min(34, slot * 0.6);
    data.forEach((v, i) => {
        const y = yOf(v), h = top + ph - y;
        g.fillStyle = RP_BAR;
        rpRoundRect(g, xOf(i) - bw / 2, y, bw, h, 5); g.fill();
        if (n <= 12 && v > 0) {
            g.fillStyle = '#111827'; g.textAlign = 'center';
            g.fillText(rpNum(v, fixedMax), xOf(i), Math.max(top - 6, y - 9));
        }
    });
}

// ---------- horizontal bars (progress by team / student ...) ----------
function rpHBar(g, W, H, labels, data, fixedMax) {
    const max = fixedMax || rpNiceMax(Math.max(...data, 0));
    const ticks = fixedMax || max >= 5 ? 5 : Math.max(1, Math.ceil(max));   // small counts: 0, 1, 2 ... (no repeated numbers)
    const labelW = Math.min(W * 0.38, Math.max(...labels.map(l => g.measureText(l).width), 20) + 12);
    const left = labelW, right = 34, top = 6, bottom = 24;
    const pw = W - left - right, ph = H - top - bottom;
    const n = Math.max(1, labels.length);
    const slot = ph / n;
    const bh = Math.min(22, slot * 0.65);
    const xOf = v => left + (Math.min(v, max) / max) * pw;

    // grid + x axis
    g.textAlign = 'center';
    for (let i = 0; i <= ticks; i++) {
        const x = left + (pw / ticks) * i;
        g.strokeStyle = RP_GRID; g.lineWidth = 1;
        g.beginPath(); g.moveTo(Math.round(x) + 0.5, top); g.lineTo(Math.round(x) + 0.5, top + ph); g.stroke();
        g.fillStyle = RP_TEXT; g.fillText(rpNum(max / ticks * i, fixedMax), x, H - bottom / 2);
    }

    labels.forEach((l, i) => {
        const cy = top + slot * i + slot / 2;
        g.textAlign = 'right'; g.fillStyle = RP_TEXT;
        g.fillText(rpFit(g, l, labelW - 12), left - 8, cy);

        const w = Math.max(2, xOf(data[i]) - left);
        g.fillStyle = RP_BAR;
        rpRoundRect(g, left, cy - bh / 2, w, bh, 4); g.fill();

        g.textAlign = 'left'; g.fillStyle = '#111827';
        g.fillText(rpNum(data[i], fixedMax), left + w + 6, cy);
    });
}

// ---------- doughnut + legend ----------
function rpDoughnut(g, W, H, labels, data, colors) {
    const total = data.reduce((a, b) => a + b, 0) || 1;
    const legend = labels.map((l, i) => l + '  (' + data[i] + ')');
    const legendW = W > 360 ? Math.min(W * 0.5, Math.max(...legend.map(t => g.measureText(t).width)) + 34) : 0;
    const areaW = W - legendW;
    const r = Math.max(10, Math.min(areaW, H) / 2 - 8);
    const cx = areaW / 2, cy = H / 2;

    let a = -Math.PI / 2;
    data.forEach((v, i) => {
        const slice = (v / total) * Math.PI * 2;
        g.beginPath(); g.moveTo(cx, cy); g.arc(cx, cy, r, a, a + slice); g.closePath();
        g.fillStyle = colors[i]; g.fill();
        g.strokeStyle = '#fff'; g.lineWidth = 2; g.stroke();
        a += slice;
    });
    // hole in the middle + total
    g.beginPath(); g.arc(cx, cy, r * 0.62, 0, Math.PI * 2); g.fillStyle = '#fff'; g.fill();
    g.fillStyle = '#111827'; g.textAlign = 'center';
    g.font = 'bold 20px ' + RP_FONT; g.fillText(String(data.reduce((x, y) => x + y, 0)), cx, cy - 6);
    g.font = '11px ' + RP_FONT; g.fillStyle = RP_TEXT; g.fillText('total', cx, cy + 14);
    g.font = '12px ' + RP_FONT;

    // legend: colour + name + count (so colour is never the only clue)
    if (legendW) {
        const rowH = 22, startY = cy - (legend.length * rowH) / 2 + rowH / 2;
        legend.forEach((t, i) => {
            const y = startY + i * rowH, x = areaW + 8;
            g.fillStyle = colors[i]; rpRoundRect(g, x, y - 6, 12, 12, 3); g.fill();
            g.fillStyle = '#374151'; g.textAlign = 'left';
            g.fillText(rpFit(g, t, legendW - 30), x + 20, y);
        });
    }
}

// draw every chart on the page, and again when the size changes
function rpDrawAll() {
    RP.charts.forEach(ch => {
        const el = document.getElementById('rpChart_' + ch.id);
        if (el) rpDrawChart(el, ch);
    });
}
rpDrawAll();
let rpResizeTimer;
window.addEventListener('resize', () => { clearTimeout(rpResizeTimer); rpResizeTimer = setTimeout(rpDrawAll, 150); });
if (document.fonts && document.fonts.ready) document.fonts.ready.then(rpDrawAll);

/* =================================================================
   PDF DOWNLOAD – the browser's own print window ("Save as PDF")
   Only the report is printed (no sidebar, header or options).
   The "Include in the PDF" boxes that are ticked RIGHT NOW decide
   what goes into the PDF (summary cards, charts, table).
================================================================= */
document.getElementById('rpPdfBtn').addEventListener('click', function () {
    const inc = Array.from(document.querySelectorAll('input[name="inc[]"]:checked')).map(el => el.value);
    const incErr = document.getElementById('rpIncErr');
    incErr.textContent = inc.length ? '' : 'Pick at least one of Summary cards, Charts or Table to include in the PDF.';
    if (!inc.length) {
        document.querySelector('.rp-more').open = true;
        incErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    document.body.classList.toggle('rp-print-no-cards',  !inc.includes('cards'));
    document.body.classList.toggle('rp-print-no-charts', !inc.includes('charts'));
    document.body.classList.toggle('rp-print-no-table',  !inc.includes('table'));

    // the PDF file name the browser suggests = the page title
    const oldTitle = document.title;
    document.title = RP.file.replace(/\.pdf$/, '');
    const restore = () => {
        document.title = oldTitle;
        document.body.classList.remove('rp-print-no-cards', 'rp-print-no-charts', 'rp-print-no-table');
        window.removeEventListener('afterprint', restore);
    };
    window.addEventListener('afterprint', restore);
    window.print();
});