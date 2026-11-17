<?php
/*
| Organization backend - Reports: builds the report (cards, charts, table) from report_data.php
| Used by: Functions/Organization/reports.php
| (the page already loaded Config/db.php + Session/Session.php and checked the role)
*/

$user = current_user();
$organization_email = $user['email'];

require_once __DIR__ . "/report_data.php";

/*
|--------------------------------------------------------------------------
| REPORT BUILDER
|--------------------------------------------------------------------------
| The organization chooses:
|   - report type  : Projects / Teams / Individual students / Proposals / Feedback
|   - period       : last 7 days ... this year, all time, or a custom date range
|   - filters      : only the ones that matter for the report type (project, team, status or rating)
|   - "Customize the table and download" (closed by default): which details go into the table,
|     and what goes into the PDF (summary cards, charts, table). The table is newest first.
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
// (kept short: time period + at most two more)
$show = [
    'project' => $type !== 'projects',
    'team'    => $type === 'individual',
    'status'  => in_array($type, ['projects', 'teams', 'proposals'], true),
    'rating'  => $type === 'feedback',
];

function rpSel($a, $b) { return (string)$a === (string)$b ? 'selected' : ''; }
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES);

$pillClass = ['On Track' => 'ok', 'Completed' => 'done', 'Behind Schedule' => 'late', 'Accepted' => 'ok', 'Rejected' => 'late',
              'Pending' => 'wait', 'Active' => 'ok', 'Reviewing' => 'wait', 'Open' => 'wait', 'Hold' => 'late',
              'Draft' => 'done', 'In Progress' => 'wait', 'Not started' => 'done'];