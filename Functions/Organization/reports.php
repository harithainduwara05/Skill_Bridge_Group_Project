<?php

include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');
$user = current_user();
$organization_email = $user['email'];

include __DIR__ . "/team_schema.php";
include __DIR__ . "/report_data.php";
tmEnsureSchema($conn);

/*
|--------------------------------------------------------------------------
| REPORT BUILDER
|--------------------------------------------------------------------------
| The organization chooses:
|   - report type  : Projects / Teams / Individual students / Proposals / Feedback
|   - period       : last 7 days ... this year, all time, or a custom date range
|   - trend by     : day / week / month
|   - filters      : team, student, project, category, status, progress, rating
|   - columns      : which details go into the table
|   - sort order   and what goes into the PDF (summary cards, charts, table)
| The page shows a live preview; the same report downloads as PDF or CSV.
*/
$params = rpParams($_GET);
$report = rpBuild(rpDataset($conn, $organization_email), $params);
$type   = $params['type'];

$orgName = rpQuery($conn, "SELECT Name FROM organization WHERE Email = ?", "s", [$organization_email])[0]['Name']
           ?? ($user['username'] ?? 'Organization');

// same options for the CSV link
$csvQuery = http_build_query(array_merge($_GET, ['type' => $type]));

// which filters make sense for which report
$show = [
    'team'    => in_array($type, ['teams', 'individual', 'feedback'], true),
    'student' => in_array($type, ['individual', 'proposals'], true),
    'status'  => $type !== 'feedback',
    'band'    => in_array($type, ['projects', 'teams', 'individual'], true),
    'rating'  => $type === 'feedback',
];

function rpSel($a, $b) { return (string)$a === (string)$b ? 'selected' : ''; }
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES);

$pillClass = ['On Track' => 'ok', 'Completed' => 'done', 'Behind Schedule' => 'late', 'Accepted' => 'ok', 'Rejected' => 'late',
              'Pending' => 'wait', 'Active' => 'ok', 'Reviewing' => 'wait', 'Open' => 'wait', 'Hold' => 'late',
              'Draft' => 'done', 'In Progress' => 'wait', 'Not started' => 'done'];

 $extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Organization/reports.css">';             
include "../../Includes/org_sidebar.php";
include "../../Includes/dash_header.php";
?>

<main class="content">
<div class="rp-wrap">

    <div class="rp-head">
        <div>
            <h2>Reports &amp; Analytics</h2>
            <p>Choose what the report should cover, check the preview, then download it.</p>
        </div>
        <div class="rp-head-actions">
            <a class="rp-btn" href="download_report.php?<?= $h($csvQuery) ?>">
                <span class="material-symbols-outlined">table_view</span>Download CSV
            </a>
            <button type="button" class="rp-btn solid" id="rpPdfBtn" title="Opens the print window – choose “Save as PDF”">
                <span class="material-symbols-outlined">picture_as_pdf</span>Download PDF
            </button>
        </div>
    </div>

    <!-- ===================== SUMMARY (full width, top of the page) ===================== -->
        <div class="rp-cards">
            <?php foreach ($report['cards'] as $c): ?>
                <div class="rp-card">
                    <div class="rp-card-icon <?= $c['tone'] ?>"><span class="material-symbols-outlined"><?= $c['icon'] ?></span></div>
                    <div>
                        <div class="rp-card-label"><?= $h($c['label']) ?></div>
                        <div class="rp-card-value"><?= $h($c['value']) ?></div>
                        <div class="rp-card-sub"><?= $h($c['sub']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>


    <div class="rp-layout">

        <!-- ===================== REPORT BUILDER ===================== -->
        <form class="rp-builder" method="get" action="reports.php" id="rpForm">
            <div class="rp-builder-body">

                <div class="rp-sec">
                    <div class="rp-sec-title">Report type</div>
                    <div class="rp-types">
                        <?php foreach (RP_TYPES as $key => $t): ?>
                            <label class="rp-type">
                                <input type="radio" name="type" value="<?= $key ?>" <?= $type === $key ? 'checked' : '' ?>>
                                <span>
                                    <span class="material-symbols-outlined"><?= $t['icon'] ?></span>
                                    <span><strong><?= $h($t['label']) ?></strong><small><?= $h($t['hint']) ?></small></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="rp-row">
                <div class="rp-sec">
                    <div class="rp-sec-title">Time period</div>
                    <select name="period" id="rpPeriod" class="rp-input" aria-label="Time period">
                        <?php foreach (RP_PERIODS as $k => $l): ?>
                            <option value="<?= $k ?>" <?= rpSel($params['period'], $k) ?>><?= $h($l) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="rp-two" id="rpCustom" <?= $params['period'] === 'custom' ? '' : 'hidden' ?> style="margin-top:8px;">
                        <div><label class="rp-label" for="rpFrom">From</label>
                            <input type="date" name="from" id="rpFrom" class="rp-input" value="<?= $h($params['from'] ?: $report['from']) ?>" max="<?= date('Y-m-d') ?>"></div>
                        <div><label class="rp-label" for="rpTo">To</label>
                            <input type="date" name="to" id="rpTo" class="rp-input" value="<?= $h($params['to'] ?: $report['to']) ?>" max="<?= date('Y-m-d') ?>"></div>
                    </div>

                    <span class="rp-label">Show the trend by</span>
                    <div class="rp-seg">
                        <?php foreach (['auto' => 'Auto', 'day' => 'Day', 'week' => 'Week', 'month' => 'Month'] as $k => $l): ?>
                            <label><input type="radio" name="group" value="<?= $k ?>" <?= $params['group'] === $k ? 'checked' : '' ?>><span><?= $l ?></span></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="rp-sec">
                    <div class="rp-sec-title">Filters</div>
                    <div class="rp-filter-grid">

                    <?php if ($show['team']): ?>
                        <div class="rp-f"><label class="rp-label" for="rpTeam">Team</label>
                        <select name="team" id="rpTeam" class="rp-input">
                            <option value="">All teams</option>
                            <?php foreach ($report['options']['team'] as $o): ?><option <?= rpSel($params['team'], $o) ?>><?= $h($o) ?></option><?php endforeach; ?>
                        </select></div>
                    <?php endif; ?>

                    <?php if ($show['student']): ?>
                        <div class="rp-f"><label class="rp-label" for="rpStudent">Student</label>
                        <select name="student" id="rpStudent" class="rp-input">
                            <option value="">All students</option>
                            <?php foreach ($report['options']['student'] as $o): ?><option <?= rpSel($params['student'], $o) ?>><?= $h($o) ?></option><?php endforeach; ?>
                        </select></div>
                    <?php endif; ?>

                    <div class="rp-f"><label class="rp-label" for="rpProject">Project</label>
                    <select name="project" id="rpProject" class="rp-input">
                        <option value="">All projects</option>
                        <?php foreach ($report['options']['project'] as $o): ?><option <?= rpSel($params['project'], $o) ?>><?= $h($o) ?></option><?php endforeach; ?>
                    </select></div>

                    <div class="rp-f"><label class="rp-label" for="rpCategory">Category</label>
                    <select name="category" id="rpCategory" class="rp-input">
                        <option value="">All categories</option>
                        <?php foreach ($report['options']['category'] as $o): ?><option <?= rpSel($params['category'], $o) ?>><?= $h($o) ?></option><?php endforeach; ?>
                    </select></div>

                    <?php if ($show['status']): ?>
                        <div class="rp-f"><label class="rp-label" for="rpStatus"><?= $type === 'individual' ? 'Work status' : 'Status' ?></label>
                        <select name="status" id="rpStatus" class="rp-input">
                            <option value="">Any status</option>
                            <?php foreach ($report['options']['status'] as $k => $l): ?><option value="<?= $h($k) ?>" <?= rpSel($params['status'], $k) ?>><?= $h($l) ?></option><?php endforeach; ?>
                        </select></div>
                    <?php endif; ?>

                    <?php if ($show['band']): ?>
                        <div class="rp-f"><label class="rp-label" for="rpBand">Progress</label>
                        <select name="band" id="rpBand" class="rp-input">
                            <option value="">Any progress</option>
                            <option value="low" <?= rpSel($params['band'], 'low') ?>>Below 50% (need support)</option>
                            <option value="mid" <?= rpSel($params['band'], 'mid') ?>>50% – 79%</option>
                            <option value="high" <?= rpSel($params['band'], 'high') ?>>80% and above</option>
                        </select></div>
                    <?php endif; ?>

                    <?php if ($show['rating']): ?>
                        <div class="rp-f"><label class="rp-label" for="rpRating">Rating</label>
                        <select name="rating" id="rpRating" class="rp-input">
                            <option value="">Any rating</option>
                            <option value="5" <?= rpSel($params['rating'], 5) ?>>5 stars only</option>
                            <option value="4" <?= rpSel($params['rating'], 4) ?>>4 stars and above</option>
                            <option value="3" <?= rpSel($params['rating'], 3) ?>>3 stars and above</option>
                        </select></div>
                    <?php endif; ?>
                    </div>
                </div>

                <div class="rp-sec">
                    <div class="rp-sec-title">Details in the table
                        <span class="rp-links"><button type="button" class="rp-link" data-cols="all">All</button><button type="button" class="rp-link" data-cols="none">None</button></span>
                    </div>
                    <div class="rp-checks" id="rpCols">
                        <?php foreach (RP_COLUMNS[$type] as $k => $l): ?>
                            <label class="rp-check"><input type="checkbox" name="cols[]" value="<?= $k ?>" <?= isset($report['columns'][$k]) ? 'checked' : '' ?>><?= $h($l) ?></label>
                        <?php endforeach; ?>
                    </div>

                    <div class="rp-two" style="margin-top:12px;">
                        <div><label class="rp-label" for="rpSort">Sort by</label>
                            <select name="sort" id="rpSort" class="rp-input">
                                <?php foreach (RP_COLUMNS[$type] as $k => $l): ?><option value="<?= $k ?>" <?= rpSel($params['sort'], $k) ?>><?= $h($l) ?></option><?php endforeach; ?>
                            </select></div>
                        <div><label class="rp-label" for="rpDir">Order</label>
                            <select name="dir" id="rpDir" class="rp-input">
                                <option value="desc" <?= rpSel($params['dir'], 'desc') ?>>Newest / highest</option>
                                <option value="asc" <?= rpSel($params['dir'], 'asc') ?>>Oldest / lowest</option>
                            </select></div>
                    </div>
                </div>

                <div class="rp-sec">
                    <div class="rp-sec-title">Include in the download</div>
                    <div class="rp-checks">
                        <?php foreach (['cards' => 'Summary cards', 'charts' => 'Charts', 'table' => 'Table'] as $k => $l): ?>
                            <label class="rp-check"><input type="checkbox" name="inc[]" value="<?= $k ?>" <?= in_array($k, $params['inc'], true) ? 'checked' : '' ?>><?= $l ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                </div><!-- /rp-row -->
            </div>

            <div class="rp-builder-foot">
                <a class="rp-btn" href="reports.php?type=<?= $type ?>">Reset</a>
                <button type="submit" class="rp-btn solid"><span class="material-symbols-outlined">refresh</span>Update report</button>
            </div>
        </form>

        <!-- ===================== REPORT PREVIEW ===================== -->
        <section class="rp-sheet" aria-label="Report preview">
            <div class="rp-band">
                <div>
                    <div class="rp-band-brand">SkillBridge report</div>
                    <h3><?= $h($report['type_label']) ?> report</h3>
                    <div class="rp-band-org"><?= $h($orgName) ?></div>
                </div>
                <div class="rp-band-meta">
                    <div>Period <strong><?= $h($report['period_text']) ?></strong></div>
                    <div>Generated <strong><?= date('M d, Y') ?></strong></div>
                    <div><strong><?= (int)$report['total_rows'] ?></strong> record<?= $report['total_rows'] === 1 ? '' : 's' ?></div>
                </div>
            </div>

            <div class="rp-chips">
                <?php foreach ($report['filters'] as $k => $v): ?>
                    <span class="rp-chip"><b><?= $h($k) ?>:</b> <?= $h($v) ?></span>
                <?php endforeach; ?>
                <?php if ($report['has_sample']): ?><span class="rp-chip sample">Includes sample data</span><?php endif; ?>
            </div>

            <div class="rp-body">
                <!-- summary cards again, inside the report: shown only in the PDF / print -->
                <div class="rp-cards rp-print-cards">
                    <?php foreach ($report['cards'] as $c): ?>
                        <div class="rp-card">
                            <div class="rp-card-icon <?= $c['tone'] ?>"><span class="material-symbols-outlined"><?= $c['icon'] ?></span></div>
                            <div>
                                <div class="rp-card-label"><?= $h($c['label']) ?></div>
                                <div class="rp-card-value"><?= $h($c['value']) ?></div>
                                <div class="rp-card-sub"><?= $h($c['sub']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="rp-charts">
                    <?php foreach ($report['charts'] as $i => $ch): ?>
                        <div class="rp-chart <?= $i === 0 || count($report['charts']) % 2 === 0 && $i === count($report['charts']) - 1 ? 'full' : '' ?>">
                            <h4><?= $h($ch['title']) ?></h4>
                            <div class="rp-chart-box">
                                <?php if (array_sum($ch['series'][0]['data']) > 0): ?>
                                    <canvas id="rpChart_<?= $h($ch['id']) ?>" role="img" aria-label="<?= $h($ch['title']) ?>"></canvas>
                                <?php else: ?>
                                    <div class="rp-chart-empty">No data for this period</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="rp-table-head">
                    <h4>Details</h4>
                    <span><?= (int)$report['total_rows'] ?> row<?= $report['total_rows'] === 1 ? '' : 's' ?> · sorted by <?= $h(RP_COLUMNS[$type][$params['sort']]) ?></span>
                </div>
                <?php if ($report['rows']): ?>
                    <div class="rp-table-wrap">
                        <table class="rp-table">
                            <thead><tr><?php foreach ($report['columns'] as $l): ?><th><?= $h($l) ?></th><?php endforeach; ?></tr></thead>
                            <tbody>
                            <?php foreach ($report['rows'] as $r): ?>
                                <tr>
                                    <?php foreach ($report['columns'] as $k => $l): $v = $r[$k] ?? ''; ?>
                                        <?php if ($k === 'progress'): ?>
                                            <td><div class="rp-prog"><div class="rp-prog-bar <?= $v < 50 ? 'low' : ($v >= 80 ? 'high' : '') ?>"><span style="width:<?= (int)$v ?>%"></span></div><strong><?= (int)$v ?>%</strong></div></td>
                                        <?php elseif (in_array($k, ['status', 'work'], true)): ?>
                                            <td><span class="rp-pill <?= $pillClass[$v] ?? 'done' ?>"><?= $h($v) ?></span></td>
                                        <?php elseif ($k === 'days_left'): ?>
                                            <td class="num <?= is_numeric($v) && $v < 0 ? 'rp-late' : '' ?>"><?= $h(rpCell($k, $v)) ?></td>
                                        <?php else: ?>
                                            <td class="<?= is_numeric($v) ? 'num' : '' ?>"><?= $h(rpCell($k, $v)) ?></td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="rp-empty">
                        <span class="material-symbols-outlined">filter_alt_off</span>
                        Nothing matches these options. Try a longer time period or remove a filter.
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
</main>

<script>
const RP = <?= json_encode([
    'type' => $type, 'title' => $report['type_label'] . ' Report', 'org' => $orgName,
    'period' => $report['period_text'], 'generated' => date('M d, Y h:i A'),
    'filters' => $report['filters'], 'sample' => $report['has_sample'],
    'cards' => $report['cards'], 'charts' => $report['charts'],
    'file' => 'skillbridge_' . $type . '_report_' . date('Y-m-d') . '.pdf',
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;

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
        if (period.value !== 'custom') form.submit();
    });

    // simple choices update the report straight away
    form.querySelectorAll('select:not(#rpPeriod):not(#rpSort):not(#rpDir), input[name="group"]').forEach(el =>
        el.addEventListener('change', () => form.submit()));

    document.querySelectorAll('[data-cols]').forEach(b => b.addEventListener('click', () => {
        document.querySelectorAll('#rpCols input').forEach(c => c.checked = b.dataset.cols === 'all');
    }));

    form.addEventListener('submit', e => {
        if (form.period.value === 'custom' && form.from.value && form.to.value && form.from.value > form.to.value) {
            e.preventDefault();
            alert('The "From" date must be before the "To" date.');
        }
        if (!document.querySelector('#rpCols input:checked')) {
            e.preventDefault();
            alert('Pick at least one detail for the table.');
        }
    });
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
   The "Include in the download" boxes that are ticked RIGHT NOW decide
   what goes into the PDF (summary cards, charts, table).
================================================================= */
document.getElementById('rpPdfBtn').addEventListener('click', function () {
    const inc = Array.from(document.querySelectorAll('input[name="inc[]"]:checked')).map(el => el.value);
    if (!inc.length) {
        alert('Pick at least one of Summary cards, Charts or Table to include in the download.');
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
</script>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="/Skill_Bridge_Group_Project/help_center.php">Help Center</a>
        <a href="/Skill_Bridge_Group_Project/help_center.php#knowledgeBaseSection">Privacy Policy</a>
        <a href="/Skill_Bridge_Group_Project/help_center.php#knowledgeBaseSection">Terms of Service</a>
    </div>
</footer>

<?php include "../../Includes/dash_footer.php"; ?>