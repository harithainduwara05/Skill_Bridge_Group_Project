<?php
/*
| Organization backend - Download Report: sends the report as a CSV file
| Used by: Functions/Organization/download_report.php
| (the page already loaded Config/db.php + Session/Session.php and checked the role)
*/

$user = current_user();
$organization_email = $user['email'];

require_once __DIR__ . "/report_data.php";

$params = rpParams($_GET);
$report = rpBuild(rpDataset($conn, $organization_email), $params);

// same values as the table, but dates as YYYY-MM-DD so every spreadsheet reads them as dates
function csvCell(string $key, $v): string
{
    if (in_array($key, ['posted', 'deadline', 'created', 'joined', 'submitted', 'date'], true)) {
        return ($v && strtotime((string)$v)) ? date('Y-m-d', strtotime((string)$v)) : '';
    }
    if ($key === 'rating') return $v === null || $v === '' ? '' : (string)(int)$v;
    return rpCell($key, $v);
}

$filename = 'skillbridge_' . $report['type'] . '_report_'
          . $report['from'] . '_to_' . $report['to'] . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));   // BOM so Excel opens UTF-8 correctly

$head = array_values($report['columns']);
foreach ($head as $i => $label) if ($label === 'Rating') $head[$i] = 'Rating (out of 5)';
fputcsv($out, $head);

foreach ($report['rows'] as $r) {
    $line = [];
    foreach ($report['columns'] as $key => $label) $line[] = csvCell($key, $r[$key] ?? null);
    fputcsv($out, $line);
}

fclose($out);