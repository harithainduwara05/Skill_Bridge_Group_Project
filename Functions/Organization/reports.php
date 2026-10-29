<?php

include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');

// Backend (PHP + SQL) of this page
require_once __DIR__ . "/../../Backend/Organization/reports.php";

 $extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Organization/reports.css?v=' . filemtime(__DIR__ . '/../../Assets/CSS/Organization/reports.css') . '">';             
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

                <!-- only the filters that matter for this report (see $show in Backend/Organization/reports.php) -->
                <div class="rp-sec">
                    <div class="rp-sec-title">Filters</div>
                    <div class="rp-filters">
                        <div class="rp-f"><label class="rp-label" for="rpPeriod">Time period</label>
                        <select name="period" id="rpPeriod" class="rp-input">
                            <?php foreach (RP_PERIODS as $k => $l): ?>
                                <option value="<?= $k ?>" <?= rpSel($params['period'], $k) ?>><?= $h($l) ?></option>
                            <?php endforeach; ?>
                        </select></div>

                        <div class="rp-f rp-custom" id="rpCustom" <?= $params['period'] === 'custom' ? '' : 'hidden' ?>>
                            <div><label class="rp-label" for="rpFrom">From</label>
                                <input type="date" name="from" id="rpFrom" class="rp-input" value="<?= $h($params['from'] ?: $report['from']) ?>" max="<?= date('Y-m-d') ?>"></div>
                            <div><label class="rp-label" for="rpTo">To</label>
                                <input type="date" name="to" id="rpTo" class="rp-input" value="<?= $h($params['to'] ?: $report['to']) ?>" max="<?= date('Y-m-d') ?>"></div>
                        </div>

                        <?php if ($show['project']): ?>
                            <div class="rp-f"><label class="rp-label" for="rpProject">Project</label>
                            <select name="project" id="rpProject" class="rp-input">
                                <option value="">All projects</option>
                                <?php foreach ($report['options']['project'] as $o): ?><option <?= rpSel($params['project'], $o) ?>><?= $h($o) ?></option><?php endforeach; ?>
                            </select></div>
                        <?php endif; ?>

                        <?php if ($show['team']): ?>
                            <div class="rp-f"><label class="rp-label" for="rpTeam">Team</label>
                            <select name="team" id="rpTeam" class="rp-input">
                                <option value="">All teams</option>
                                <?php foreach ($report['options']['team'] as $o): ?><option <?= rpSel($params['team'], $o) ?>><?= $h($o) ?></option><?php endforeach; ?>
                            </select></div>
                        <?php endif; ?>

                        <?php if ($show['status']): ?>
                            <div class="rp-f"><label class="rp-label" for="rpStatus">Status</label>
                            <select name="status" id="rpStatus" class="rp-input">
                                <option value="">Any status</option>
                                <?php foreach ($report['options']['status'] as $k => $l): ?><option value="<?= $h($k) ?>" <?= rpSel($params['status'], $k) ?>><?= $h($l) ?></option><?php endforeach; ?>
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
                    <div class="rp-error" id="rpDateErr"></div>
                </div>

                <!-- extra choices for the table and the download (closed by default, so the page stays simple) -->
                <details class="rp-more" <?= count($report['columns']) < count(RP_COLUMNS[$type]) || count($params['inc']) < 3 ? 'open' : '' ?>>
                    <summary><span class="material-symbols-outlined">tune</span>Customize the table and download</summary>
                    <div class="rp-more-body">
                        <div class="rp-sec">
                            <div class="rp-sec-title">Details in the table
                                <span class="rp-links"><button type="button" class="rp-link" data-cols="all">All</button><button type="button" class="rp-link" data-cols="none">None</button></span>
                            </div>
                            <div class="rp-checks" id="rpCols">
                                <?php foreach (RP_COLUMNS[$type] as $k => $l): ?>
                                    <label class="rp-check"><input type="checkbox" name="cols[]" value="<?= $k ?>" <?= isset($report['columns'][$k]) ? 'checked' : '' ?>><?= $h($l) ?></label>
                                <?php endforeach; ?>
                            </div>
                            <div class="rp-error" id="rpColsErr"></div>
                        </div>
                        <div class="rp-sec">
                            <div class="rp-sec-title">Include in the PDF</div>
                            <div class="rp-checks">
                                <?php foreach (['cards' => 'Summary cards', 'charts' => 'Charts', 'table' => 'Table'] as $k => $l): ?>
                                    <label class="rp-check"><input type="checkbox" name="inc[]" value="<?= $k ?>" <?= in_array($k, $params['inc'], true) ? 'checked' : '' ?>><?= $l ?></label>
                                <?php endforeach; ?>
                            </div>
                            <div class="rp-error" id="rpIncErr"></div>
                        </div>
                    </div>
                </details>
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
    // Values from PHP (database) used by Assets/JS/Organization/reports.js
    const ORG_DATA = {
        RP: <?= json_encode([
    'type' => $type, 'title' => $report['type_label'] . ' Report', 'org' => $orgName,
    'period' => $report['period_text'], 'generated' => date('M d, Y h:i A'),
    'filters' => $report['filters'], 'sample' => $report['has_sample'],
    'cards' => $report['cards'], 'charts' => $report['charts'],
    'file' => 'skillbridge_' . $type . '_report_' . date('Y-m-d') . '.pdf',
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    };
</script>
<script src="../../Assets/JS/Organization/reports.js?v=<?= filemtime(__DIR__ . '/../../Assets/JS/Organization/reports.js') ?>"></script>


<?php include "../../Includes/dash_footer.php"; ?>