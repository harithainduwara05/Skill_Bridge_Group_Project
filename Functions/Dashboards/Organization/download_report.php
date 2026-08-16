<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role('organization');
$user = current_user();
$organization_email = $user['email'];

// Fetch all projects for this organization, with proposal counts
$stmt = $conn->prepare("SELECT p.*,
                                (SELECT COUNT(*) FROM student_projects sp WHERE sp.project_id = p.id) AS proposal_count
                         FROM projects p
                         WHERE p.organization_email = ?
                         ORDER BY p.posted_at DESC");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ---- Output as a downloadable CSV file ----
$filename = "skillbridge_report_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Add BOM so Excel opens UTF-8 correctly
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Header row
fputcsv($output, [
    'Project Title', 'Category', 'Difficulty', 'Duration', 'Students Required',
    'Preferred Year', 'Visibility', 'Deadline', 'Status', 'Proposals Received', 'Date Posted'
]);

foreach ($projects as $p) {
    fputcsv($output, [
        $p['title'],
        $p['category'],
        $p['difficulty'],
        $p['duration'],
        $p['members'],
        $p['preferred_year'],
        $p['visibility'],
        $p['deadline'],
        ucfirst($p['status']),
        $p['proposal_count'],
        date('Y-m-d', strtotime($p['posted_at'])),
    ]);
}

fclose($output);
exit;