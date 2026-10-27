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