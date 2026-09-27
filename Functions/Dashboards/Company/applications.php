<?php
include '../../../Config/db.php';
require_once '../../../Session/Session.php';
require_once '../../../Backend/CompanyBackend.php';
require_role('company');

$user = current_user();
$companyEmail = $user['email'] ?? $user['Email'] ?? '';
$companyManager = new CompanyManager($conn);
$company = $companyManager->getCompany($companyEmail);
if (!$company) {
    die('Company profile not found.');
}
$companyName = $company['Name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_decision'])) {
    $applicationId = (int) ($_POST['application_id'] ?? 0);
    $decision = trim($_POST['application_decision'] ?? '');
    $updated = $applicationId > 0
        && $companyManager->updateCompanyApplicationDecision($applicationId, $companyName, $decision);
    $notice = $updated
        ? (strtolower($decision) === 'accepted' ? 'accepted' : 'disqualified')
        : 'decision_failed';
    header('Location: applications.php?notice=' . $notice);
    exit;
}

$applications = [];
$sql = "SELECT ia.application_id, ia.Email AS email, ia.status, ia.applied_date,
               s.Name AS student_name, s.University, i.title AS internship_title
        FROM internship_applications ia
        INNER JOIN internships i ON i.id = ia.internship_id
        INNER JOIN student s ON s.Email = ia.Email
        WHERE TRIM(i.company) = TRIM(?)
        ORDER BY ia.applied_date DESC, ia.application_id DESC";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('s', $companyName);
    if ($stmt->execute()) {
        $applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

$skillsByEmail = [];
$skillResult = $conn->query("SELECT Email, GROUP_CONCAT(DISTINCT skill_name ORDER BY skill_name SEPARATOR ', ') AS skills FROM skills GROUP BY Email");
if ($skillResult) {
    while ($skillRow = $skillResult->fetch_assoc()) {
        $skillsByEmail[$skillRow['Email']] = $skillRow['skills'] ?? '';
    }
}

$counts = ['Applied' => 0, 'Accepted' => 0, 'Disqualified' => 0];
foreach ($applications as &$application) {
    $rawStatus = strtolower(trim($application['status'] ?? ''));
    $application['display_status'] = in_array($rawStatus, ['rejected', 'disqualified'], true)
        ? 'Disqualified'
        : (in_array($rawStatus, ['accepted', 'shortlisted', 'interviewing'], true) ? 'Accepted' : 'Applied');
    $counts[$application['display_status']]++;
    $application['skills'] = $skillsByEmail[$application['email']] ?? '';
}
unset($application);

$universities = [];
$universityResult = $conn->query("SELECT DISTINCT TRIM(University) AS University FROM universityemails WHERE Status = 'Active' AND TRIM(COALESCE(University, '')) <> '' ORDER BY University ASC");
if ($universityResult) {
    while ($universityRow = $universityResult->fetch_assoc()) {
        $universities[] = $universityRow['University'];
    }
}

$noticeMessages = [
    'accepted' => 'Application accepted.',
    'disqualified' => 'Application disqualified.',
];
$successMessage = $noticeMessages[$_GET['notice'] ?? ''] ?? '';
$errorMessage = ($_GET['notice'] ?? '') === 'decision_failed'
    ? 'Unable to update this application. Please refresh and try again.'
    : '';
$page_title = 'Applications';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications | SkillBridge</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">
    <link rel="stylesheet" href="../../../Assets/CSS/Company/applications.css?v=<?= filemtime(__DIR__ . '/../../../Assets/CSS/Company/applications.css') ?>">
</head>
<body>

<?php include '../../../Includes/company_sidebar.php'; ?>
<?php include '../../../Includes/dash_header.php'; ?>

<main class="content applications-page">
    <div class="applications-heading">
        <div>
            <h1>Internship Applications</h1>
            <p>Review applicants and make application decisions for your internships.</p>
        </div>
        <div class="applications-heading-actions">
            <button type="button" class="export-btn" id="exportCsvBtn">
                <span class="material-symbols-outlined" aria-hidden="true">download</span>
                Export CSV
            </button>
        </div>
    </div>

    <?php if ($successMessage !== ''): ?>
        <div class="applications-notice success" role="status"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage !== ''): ?>
        <div class="applications-notice error" role="alert"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <section class="application-summary-grid" aria-label="Application totals">
        <div class="application-summary-card">
            <div class="summary-icon applicants"><span class="material-symbols-outlined">group</span></div>
            <div><span class="summary-label">TOTAL APPLICANTS</span><strong><?= count($applications) ?></strong></div>
        </div>
        <div class="application-summary-card">
            <div class="summary-icon review"><span class="material-symbols-outlined">hourglass_empty</span></div>
            <div><span class="summary-label">APPLIED</span><strong class="orange-number"><?= $counts['Applied'] ?></strong></div>
        </div>
        <div class="application-summary-card">
            <div class="summary-icon accepted"><span class="material-symbols-outlined">check_circle</span></div>
            <div><span class="summary-label">ACCEPTED</span><strong class="green-number"><?= $counts['Accepted'] ?></strong></div>
        </div>
        <div class="application-summary-card">
            <div class="summary-icon disqualified"><span class="material-symbols-outlined">cancel</span></div>
            <div><span class="summary-label">DISQUALIFIED</span><strong class="red-number"><?= $counts['Disqualified'] ?></strong></div>
        </div>
    </section>

    <section class="applications-filter-card">
        <div class="filter-group">
            <label for="statusFilter">Application Status</label>
            <select id="statusFilter">
                <option value="all">All Statuses</option>
                <option value="Applied">Applied</option>
                <option value="Accepted">Accepted</option>
                <option value="Disqualified">Disqualified</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="universityFilter">University</label>
            <select id="universityFilter">
                <option value="all">All Universities</option>
                <?php foreach ($universities as $university): ?>
                    <option value="<?= htmlspecialchars($university, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($university) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </section>

    <section class="applications-table-card">
        <div class="applications-table-wrapper">
            <table id="applicationsTable">
                <thead>
                    <tr><th>STUDENT NAME</th><th>UNIVERSITY</th><th>SKILLS</th><th>APPLIED DATE</th><th>STATUS</th><th>ACTIONS</th></tr>
                </thead>
                <tbody>
                    <?php if (!$applications): ?>
                        <tr><td colspan="6" class="applications-empty">No applications found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($applications as $application): ?>
                        <?php
                            $name = trim($application['student_name'] ?? 'Student');
                            $nameParts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
                            $initials = strtoupper(substr($nameParts[0] ?? 'S', 0, 1) . substr($nameParts[count($nameParts) - 1] ?? '', 0, 1));
                            $status = $application['display_status'];
                            $date = !empty($application['applied_date']) ? date('M j, Y', strtotime($application['applied_date'])) : '—';
                            $skills = array_slice(array_filter(array_map('trim', explode(',', $application['skills']))), 0, 3);
                        ?>
                        <tr class="application-row" data-status="<?= htmlspecialchars($status) ?>" data-university="<?= htmlspecialchars($application['University'] ?? '') ?>">
                            <td><div class="student-cell"><div class="student-avatar avatar-blue"><?= htmlspecialchars($initials) ?></div><div><strong><?= htmlspecialchars($name) ?></strong><span><?= htmlspecialchars($application['email']) ?></span></div></div></td>
                            <td><?= htmlspecialchars($application['University'] ?? '—') ?></td>
                            <td><div class="skills-list">
                                <?php foreach ($skills as $skill): ?><span><?= htmlspecialchars($skill) ?></span><?php endforeach; ?>
                                <?php if (!$skills): ?><span>—</span><?php endif; ?>
                            </div></td>
                            <td><?= htmlspecialchars($date) ?></td>
                            <td><span class="status <?= strtolower($status) ?>"><?= htmlspecialchars($status) ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <a class="action-btn view-btn" href="candidates.php?application_id=<?= (int) $application['application_id'] ?>" title="View Candidate Details" aria-label="View Candidate Details"><span class="material-symbols-outlined">visibility</span></a>
                                    <?php if ($status === 'Applied'): ?>
                                        <form method="POST" class="decision-form">
                                            <input type="hidden" name="application_id" value="<?= (int) $application['application_id'] ?>">
                                            <button type="submit" name="application_decision" value="Accepted" class="action-btn accept-btn" title="Accept" aria-label="Accept"><span class="material-symbols-outlined">check_circle</span></button>
                                            <button type="submit" name="application_decision" value="Disqualified" class="action-btn disqualify-btn" title="Disqualify" aria-label="Disqualify"><span class="material-symbols-outlined">cancel</span></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="applications-pagination"><p>Showing <span id="visibleApplicationCount"><?= count($applications) ?></span> of <?= count($applications) ?> applications</p></div>
    </section>

    <?php include '../../../Includes/company_dashboard_footer.php'; ?>
</main>
<script src="../../../Assets/JS/Company/applications.js"></script>
<?php include '../../../Includes/dash_footer.php'; ?>
</body>
</html>
