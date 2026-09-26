<?php
/*
| CSV download of the report built on reports.php
| (same options: reports.php?type=teams&period=3m...  ->  download_report.php?type=teams&period=3m...)
*/
include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role('organization');
$user = current_user();
$organization_email = $user['email'];

include __DIR__ . "/team_schema.php";
include __DIR__ . "/report_data.php";
tmEnsureSchema($conn);

$params = rpParams($_GET);
$report = rpBuild(rpDataset($conn, $organization_email), $params);

$orgName = rpQuery($conn, "SELECT Name FROM organization WHERE Email = ?", "s", [$organization_email])[0]['Name']
           ?? ($user['username'] ?? 'Organization');

$filename = 'skillbridge_' . $report['type'] . '_report_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));   // BOM so Excel opens UTF-8 correctly

// ---- report details ----
fputcsv($out, ['SkillBridge – ' . $report['type_label'] . ' Report']);
fputcsv($out, ['Organization', $orgName]);
fputcsv($out, ['Generated on', date('M d, Y h:i A')]);
foreach ($report['filters'] as $k => $v) fputcsv($out, [$k, $v]);
if ($report['has_sample']) fputcsv($out, ['Note', 'Includes sample data']);
fputcsv($out, []);

// ---- summary (stat cards) ----
if (in_array('cards', $params['inc'], true)) {
    fputcsv($out, ['Summary']);
    foreach ($report['cards'] as $c) fputcsv($out, [$c['label'], $c['value'], $c['sub']]);
    fputcsv($out, []);
}

// ---- table ----
fputcsv($out, array_values($report['columns']));
foreach ($report['rows'] as $r) {
    $line = [];
    foreach ($report['columns'] as $key => $label) $line[] = rpCell($key, $r[$key] ?? '');
    fputcsv($out, $line);
}
fputcsv($out, []);
fputcsv($out, ['Total rows', $report['total_rows']]);

fclose($out);