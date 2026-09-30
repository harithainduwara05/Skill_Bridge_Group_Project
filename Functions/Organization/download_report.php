<?php
/*
| CSV download of the report built on reports.php
| (same options: reports.php?type=teams&period=3m...  ->  download_report.php?type=teams&period=3m...)
|
| The CSV is one clean table: the column names on the first row, then one row
| per record. This opens correctly in Excel / Google Sheets and can be imported
| into other tools. (A CSV can't keep column widths or colours – for a formatted
| report use "Download Excel", download_excel.php.)
*/
include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');
$user = current_user();
$organization_email = $user['email'];

include __DIR__ . "/team_schema.php";
include __DIR__ . "/report_data.php";
tmEnsureSchema($conn);

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