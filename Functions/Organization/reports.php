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

include "../../Includes/org_sidebar.php";
include "../../Includes/dash_header.php";
?>

<style>
    .rp-wrap { padding: 14px 28px 28px; }
    .rp-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 18px; }
    .rp-head h2 { margin: 0; font-size: 24px; font-weight: 800; color: #0f3a66; }
    .rp-head p { margin: 4px 0 0; font-size: 14px; color: #4b5563; }
    .rp-head-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .rp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 16px; border-radius: 8px; font-family: inherit;
              font-size: 13.5px; font-weight: 600; cursor: pointer; text-decoration: none; border: 1px solid #d1d5db; background: #fff; color: #1f2937; }
    .rp-btn .material-symbols-outlined { font-size: 18px; }
    .rp-btn:hover { background: #f3f4f6; }
    .rp-btn.solid { background: #0f2a4a; border-color: #0f2a4a; color: #fff; }
    .rp-btn.solid:hover { background: #0a1f38; }
    .rp-btn:focus-visible, .rp-type input:focus-visible + span, .rp-input:focus-visible { outline: 3px solid #93c5fd; outline-offset: 2px; }
    .rp-btn[disabled] { opacity: .6; cursor: progress; }

    /* stat cards on top, then the builder, then the report – one below the other */
    .rp-layout { display: flex; flex-direction: column; gap: 20px; }
    .rp-wrap > .rp-cards { margin-bottom: 20px; }
    .rp-card { background: #fff; }

    /* ---------- builder (left) ---------- */
    .rp-builder { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden; }
    .rp-builder-body { padding: 6px 20px 8px; }
    .rp-row { display: grid; grid-template-columns: 1fr 1.6fr 1.3fr 0.8fr; gap: 0 22px; border-top: 1px solid #f1f3f6; }
    .rp-row > .rp-sec { border-top: none; }
    .rp-row > .rp-sec + .rp-sec { border-left: 1px solid #f1f3f6; padding-left: 22px; }
    .rp-row > .rp-sec:last-child .rp-checks { grid-template-columns: 1fr; }
    .rp-filter-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 10px; }
    .rp-types { grid-template-columns: repeat(5, minmax(0, 1fr)); }
    .rp-sec { padding: 14px 0; border-top: 1px solid #f1f3f6; }
    .rp-sec:first-child { border-top: none; }
    .rp-sec-title { display: flex; justify-content: space-between; align-items: baseline; font-size: 14px; font-weight: 700; color: #0f3a66; margin: 0 0 10px; }
    .rp-sec-title small { font-size: 12px; font-weight: 500; color: #6b7280; }
    .rp-label { display: block; font-size: 12.5px; font-weight: 600; color: #374151; margin: 10px 0 5px; }
    .rp-input { width: 100%; box-sizing: border-box; padding: 9px 11px; border: 1px solid #e5e7eb; border-radius: 9px; background: #f9fafb;
                font-family: inherit; font-size: 13.5px; color: #111827; }
    .rp-input:focus { border-color: #93c5fd; background: #fff; outline: none; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
    .rp-two { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .rp-two .rp-label { margin-top: 0; }
    .rp-two[hidden] { display: none; }

    .rp-types { display: grid; gap: 6px; }
    .rp-type { display: block; cursor: pointer; }
    .rp-type input { position: absolute; opacity: 0; pointer-events: none; }
    .rp-type > span { display: flex; align-items: center; gap: 10px; padding: 9px 11px; border: 1px solid #e5e7eb; border-radius: 10px; }
    .rp-type > span:hover { background: #f9fafb; }
    .rp-type .material-symbols-outlined { font-size: 20px; color: #0f3a66; width: 34px; height: 34px; border-radius: 9px; background: #eef2f7;
                                          display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .rp-type strong { display: block; font-size: 13.5px; color: #111827; }
    .rp-type small { font-size: 12px; color: #6b7280; }
    .rp-type input:checked + span { border-color: #0f2a4a; background: #f3f6fb; box-shadow: inset 3px 0 0 #0f2a4a; }
    .rp-type input:checked + span .material-symbols-outlined { background: #0f2a4a; color: #fff; }

    .rp-seg { display: grid; grid-template-columns: repeat(4, 1fr); gap: 3px; background: #f3f4f6; padding: 3px; border-radius: 9px; }
    .rp-seg label { cursor: pointer; }
    .rp-seg input { position: absolute; opacity: 0; }
    .rp-seg span { display: block; text-align: center; padding: 6px 2px; border-radius: 7px; font-size: 12.5px; font-weight: 600; color: #4b5563; }
    .rp-seg input:checked + span { background: #fff; color: #0f2a4a; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
    .rp-seg input:focus-visible + span { outline: 2px solid #93c5fd; }

    .rp-checks { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 10px; }
    .rp-check { display: flex; align-items: center; gap: 7px; font-size: 13px; color: #374151; cursor: pointer; }
    .rp-check input { width: 15px; height: 15px; accent-color: #0f2a4a; margin: 0; }
    .rp-links { display: flex; gap: 10px; }
    .rp-link { background: none; border: none; padding: 0; font-family: inherit; font-size: 12px; font-weight: 600; color: #0f3a66; cursor: pointer; }
    .rp-link:hover { text-decoration: underline; }

    .rp-builder-foot { display: flex; justify-content: flex-end; gap: 8px; padding: 12px 20px; border-top: 1px solid #e5e7eb; background: #fbfcfd; }

    /* ---------- report preview (right) – looks like the PDF page ---------- */
    .rp-sheet { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 28px rgba(15,42,74,.07); }
    .rp-band { background: #0f2a4a; color: #fff; padding: 20px 24px 18px; display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .rp-band-brand { font-size: 12.5px; color: #9fb3cc; }
    .rp-band h3 { margin: 4px 0 2px; font-size: 26px; font-weight: 800; letter-spacing: -.01em; }
    .rp-band-org { font-size: 14px; color: #dbe5f1; }
    .rp-band-meta { text-align: right; font-size: 12.5px; color: #9fb3cc; line-height: 1.7; }
    .rp-band-meta strong { color: #fff; font-weight: 600; }
    .rp-chips { display: flex; flex-wrap: wrap; gap: 6px; padding: 12px 24px; background: #f5f7fb; border-bottom: 1px solid #e5e7eb; }
    .rp-chip { font-size: 12px; color: #1e3a5f; background: #fff; border: 1px solid #dbe3ee; padding: 4px 10px; border-radius: 999px; }
    .rp-chip b { font-weight: 600; color: #6b7280; }
    .rp-chip.sample { background: #fffbeb; border-color: #fde68a; color: #92400e; }

    .rp-body { padding: 20px 24px 24px; }
    .rp-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
    .rp-card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; display: flex; gap: 12px; align-items: flex-start; }
    .rp-card-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .rp-card-icon.navy { background: #0f2a4a; color: #fff; } .rp-card-icon.blue { background: #dbeafe; color: #2563eb; }
    .rp-card-icon.green { background: #dcfce7; color: #16a34a; } .rp-card-icon.sky { background: #e0f2fe; color: #0369a1; }
    .rp-card-icon.red { background: #fee2e2; color: #dc2626; }
    .rp-card-label { font-size: 12.5px; color: #4b5563; }
    .rp-card-value { font-size: 22px; font-weight: 800; color: #111827; line-height: 1.2; }
    .rp-card-sub { font-size: 12px; color: #6b7280; }

    .rp-charts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-bottom: 22px; }
    .rp-chart { border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px 16px; min-width: 0; }
    .rp-chart.full { grid-column: 1 / -1; }
    .rp-chart h4 { margin: 0 0 10px; font-size: 14px; font-weight: 700; color: #111827; }
    .rp-chart-box { position: relative; height: 240px; }
    .rp-chart.full .rp-chart-box { height: 220px; }
    .rp-chart-box canvas { display: block; width: 100%; height: 100%; }
    .rp-print-cards { display: none; }
    .rp-chart-empty { display: flex; align-items: center; justify-content: center; height: 100%; font-size: 13px; color: #9ca3af; }

    .rp-table-head { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 10px; }
    .rp-table-head h4 { margin: 0; font-size: 16px; font-weight: 800; color: #0f3a66; }
    .rp-table-head span { font-size: 12.5px; color: #6b7280; }
    .rp-table-wrap { overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 12px; }
    .rp-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .rp-table th { background: #f5f7fb; color: #374151; font-weight: 700; text-align: left; padding: 10px 12px; white-space: nowrap; border-bottom: 1px solid #e5e7eb; }
    .rp-table td { padding: 10px 12px; border-top: 1px solid #f1f3f6; color: #1f2937; vertical-align: middle; }
    .rp-table tr:first-child td { border-top: none; }
    .rp-table td.num { white-space: nowrap; }
    .rp-prog { display: flex; align-items: center; gap: 8px; min-width: 110px; }
    .rp-prog-bar { flex: 1; height: 6px; background: #e5e7eb; border-radius: 999px; overflow: hidden; }
    .rp-prog-bar span { display: block; height: 100%; background: #0f3a66; border-radius: 999px; }
    .rp-prog-bar.low span { background: #dc2626; } .rp-prog-bar.high span { background: #16a34a; }
    .rp-pill { font-size: 11.5px; font-weight: 700; padding: 3px 9px; border-radius: 999px; white-space: nowrap; }
    .rp-pill.ok { background: #dcfce7; color: #166534; } .rp-pill.late { background: #fee2e2; color: #b91c1c; }
    .rp-pill.wait { background: #fef3c7; color: #92400e; } .rp-pill.done { background: #e5e7eb; color: #4b5563; }
    .rp-late { color: #b91c1c; font-weight: 600; }
    .rp-empty { text-align: center; padding: 36px 16px; color: #6b7280; font-size: 14px; }
    .rp-empty .material-symbols-outlined { font-size: 36px; color: #9ca3af; display: block; margin-bottom: 6px; }

    @media (max-width: 1250px) { .rp-cards { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 1350px) {
        .rp-row { grid-template-columns: 1fr 1fr; }
        .rp-row > .rp-sec:nth-child(3) { border-left: none; padding-left: 0; }
        .rp-types { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 1000px) {
        .rp-charts { grid-template-columns: 1fr; }
    }
    @media (max-width: 760px) {
        .rp-row { grid-template-columns: 1fr; }
        .rp-row > .rp-sec + .rp-sec { border-left: none; padding-left: 0; border-top: 1px solid #f1f3f6; }
        .rp-types { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 600px) {
        .rp-wrap { padding: 12px 14px 24px; }
        .rp-cards { grid-template-columns: 1fr; }
        .rp-band-meta { text-align: left; }
        .rp-head-actions { width: 100%; } .rp-head-actions .rp-btn { flex: 1; }
    }
    @media (prefers-reduced-motion: reduce) { * { transition: none !important; animation: none !important; } }

    /* ---------- PDF: "Download PDF" opens the browser's print window -> Save as PDF ---------- */
    @page { size: A4 landscape; margin: 10mm; }
    @media print {
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { background: #fff !important; }
        .sidebar, .top-header, .rp-head, .rp-builder, footer.footer, .flash-toast, .rp-wrap > .rp-cards { display: none !important; }
        .main-wrapper { margin-left: 0 !important; }
        .content > *:not(.top-header) { max-width: none !important; }
        .rp-wrap { padding: 0 !important; }
        .rp-layout { display: block !important; }
        .rp-sheet { border: none !important; box-shadow: none !important; border-radius: 0 !important; overflow: visible !important; }
        .rp-print-cards { display: grid !important; grid-template-columns: repeat(4, 1fr) !important; }
        .rp-card, .rp-chart, .rp-table tr { break-inside: avoid; page-break-inside: avoid; }
        .rp-charts { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        .rp-chart-box { height: auto !important; }
        .rp-chart-box canvas { height: auto !important; }
        .rp-table-wrap { overflow: visible !important; }
        /* the whole table fits the page width: smaller text, headers may wrap */
        .rp-table { table-layout: auto; width: 100% !important; font-size: 10.5px !important; }
        .rp-table th { white-space: normal !important; padding: 6px 6px !important; font-size: 10.5px !important; }
        .rp-table td { padding: 6px 6px !important; }
        .rp-table td.num { white-space: normal !important; }
        .rp-prog { min-width: 0 !important; gap: 4px !important; }
        .rp-prog-bar { min-width: 24px; }
        .rp-pill { font-size: 9.5px !important; padding: 2px 6px !important; white-space: normal !important; }
        .rp-table tr, .rp-table td { break-inside: avoid; page-break-inside: avoid; }
        body.rp-print-no-cards .rp-print-cards,
        body.rp-print-no-charts .rp-charts,
        body.rp-print-no-table .rp-table-head,
        body.rp-print-no-table .rp-table-wrap,
        body.rp-print-no-table .rp-empty { display: none !important; }
    }
</style>

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