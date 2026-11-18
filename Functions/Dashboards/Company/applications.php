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

$interviewTimeSlots = [
    '09:00 AM - 09:45 AM', '10:00 AM - 10:45 AM', '11:00 AM - 11:45 AM', '01:30 PM - 02:15 PM',
    '02:30 PM - 03:15 PM', '03:30 PM - 04:15 PM', '04:30 PM - 05:15 PM',
];
$interviewTypes = ['Video Interview (Google Meet / Zoom)', 'In-Person Interview'];
$disqualifyReasons = [
    'Technical skills insufficient', 'Qualifications / GPA mismatch', 'Schedule / Availability mismatch',
    'Position already filled', 'Interview performance', 'Other',
];

if (empty($_SESSION['applications_csrf'])) {
    $_SESSION['applications_csrf'] = bin2hex(random_bytes(16));
}
$csrfToken = $_SESSION['applications_csrf'];

/** Returns an error message, or '' when the decision details are valid. */
function validateApplicationDecision($decision, array $details, array $slots, array $types, array $reasons)
{
    if ($decision === 'Accepted') {
        $date = $details['interview_date'];
        $dateObj = $date === '' ? false : DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
            return 'Please choose a valid interview date.';
        }
        if ($date < date('Y-m-d')) return 'The interview date cannot be in the past.';
        if ($date > date('Y-m-d', strtotime('+1 year'))) return 'The interview date must be within the next year.';
        if (!in_array($details['interview_time'], $slots, true)) return 'Please choose a valid interview time slot.';
        if (!in_array($details['interview_type'], $types, true)) return 'Please choose a valid interview format.';

        $link = $details['interview_link'];
        if ($link === '') return 'Please enter the meeting link or the interview location.';
        if (mb_strlen($link) < 3) return 'Please enter a meeting link or location of at least 3 characters.';
        if (mb_strlen($link) > 255) return 'The meeting link / location is too long (maximum 255 characters).';
        if ($details['interview_type'] === $types[0]) {
            if (preg_match('/^(https?:\/\/)?[A-Za-z0-9][A-Za-z0-9.\-]*\.[A-Za-z]{2,}(\/\S*)?$/', $link) !== 1) {
                return 'Please enter a valid meeting link, e.g. https://meet.google.com/abc-defg-hij.';
            }
        } elseif (mb_strlen($link) < 3 || preg_match('/\p{L}/u', $link) !== 1) {
            return 'Please enter the interview location, e.g. Room 302, Head Office.';
        }
        if (mb_strlen($details['interview_notes']) > 1000) return 'Interview notes must be under 1000 characters.';
        return '';
    }

    if ($decision === 'Disqualified') {
        if (!in_array($details['disqualification_reason'], $reasons, true)) return 'Please select a reason for disqualification.';
        $notes = $details['disqualification_notes'];
        if ($details['disqualification_reason'] === 'Other' && mb_strlen($notes) < 5) {
            return 'Please add a short explanation when the reason is "Other".';
        }
        if (mb_strlen($notes) > 500) return 'Additional feedback must be under 500 characters.';
        return '';
    }

    return 'Please choose a decision.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_decision'])) {
    $applicationId = (int) ($_POST['application_id'] ?? 0);
    $decision = ucfirst(strtolower(trim($_POST['application_decision'] ?? '')));
    $details = [
        'interview_date' => trim($_POST['interview_date'] ?? ''),
        'interview_time' => trim($_POST['interview_time'] ?? ''),
        'interview_type' => trim($_POST['interview_type'] ?? ''),
        'interview_link' => trim($_POST['interview_link'] ?? ''),
        'interview_notes' => trim($_POST['interview_notes'] ?? ''),
        'disqualification_reason' => trim($_POST['disqualification_reason'] ?? ''),
        'disqualification_notes' => trim($_POST['disqualification_notes'] ?? ''),
    ];

    $flashType = 'error';
    $flashMessage = '';
    if (!hash_equals($csrfToken, (string) ($_POST['csrf_token'] ?? ''))) {
        $flashMessage = 'Your session expired. Please try again.';
    } elseif ($applicationId < 1) {
        $flashMessage = 'Application not found.';
    } elseif ($decision === 'Applied') {
        if ($companyManager->resetCompanyApplication($applicationId, $companyName)) {
            $flashType = 'success';
            $flashMessage = 'Decision undone. The application is back to Applied and any interview was removed.';
        } else {
            $flashMessage = 'Unable to undo this decision. Please refresh and try again.';
        }
    } else {
        $flashMessage = validateApplicationDecision($decision, $details, $interviewTimeSlots, $interviewTypes, $disqualifyReasons);
        if ($flashMessage === '') {
            if ($companyManager->updateCompanyApplicationDecision($applicationId, $companyName, $decision, $details)) {
                $flashType = 'success';
                $flashMessage = $decision === 'Accepted'
                    ? 'Application accepted and interview scheduled successfully.'
                    : 'Application status updated to Disqualified.';
            } else {
                $flashMessage = 'Unable to update this application. Please refresh and try again.';
            }
        }
    }

    $_SESSION['applications_flash'] = ['type' => $flashType, 'message' => $flashMessage];
    header('Location: applications.php');
    exit;
}

$applications = [];

$companyTableCheck = $conn->query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'internships' AND COLUMN_NAME = 'company_email' LIMIT 1");
$hasNormalizedCompanySchema = $companyTableCheck && $companyTableCheck->num_rows > 0;

if ($hasNormalizedCompanySchema) {
    $sql = "SELECT ia.application_id, ia.Email AS email, ia.status, ia.applied_date,
                   ii.interview_date,
                   ii.interview_time,
                   ii.interview_type,
                   ii.interview_link,
                   ii.interview_notes,
                   ia.disqualification_reason,
                   s.Name AS student_name, s.University, i.title AS internship_title
            FROM internship_applications ia
            INNER JOIN internships i ON i.id = ia.internship_id
            LEFT JOIN company c ON c.Email = i.company_email
            LEFT JOIN internship_interviews ii ON ii.application_id = ia.application_id
            INNER JOIN student s ON s.Email = ia.Email
            WHERE TRIM(COALESCE(c.Name, '')) = TRIM(?)
            ORDER BY ia.applied_date DESC, ia.application_id DESC";
} else {
    $sql = "SELECT ia.application_id, ia.Email AS email, ia.status, ia.applied_date,
                   ia.interview_date, ia.interview_time, ia.interview_type, ia.interview_link, ia.interview_notes, ia.disqualification_reason,
                   s.Name AS student_name, s.University, i.title AS internship_title
            FROM internship_applications ia
            INNER JOIN internships i ON i.id = ia.internship_id
            INNER JOIN student s ON s.Email = ia.Email
            WHERE TRIM(i.company) = TRIM(?)
            ORDER BY ia.applied_date DESC, ia.application_id DESC";
}
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

$universityMap = [];
$universityResult = $conn->query("SELECT University FROM universityemails WHERE TRIM(COALESCE(University, '')) <> ''");
if ($universityResult) {
    while ($universityRow = $universityResult->fetch_assoc()) {
        $value = preg_replace('/\s+/u', ' ', trim((string) $universityRow['University']));
        if ($value !== '') $universityMap[mb_strtolower($value, 'UTF-8')] = $value;
    }
}
foreach ($applications as $application) {
    $value = preg_replace('/\s+/u', ' ', trim((string) ($application['University'] ?? '')));
    if ($value !== '') $universityMap[mb_strtolower($value, 'UTF-8')] = $value;
}
$universities = array_values($universityMap);
natcasesort($universities);
$universities = array_values($universities);

$successMessage = '';
$errorMessage = '';
if (!empty($_SESSION['applications_flash'])) {
    $flash = $_SESSION['applications_flash'];
    unset($_SESSION['applications_flash']);
    if (($flash['type'] ?? '') === 'success') {
        $successMessage = (string) ($flash['message'] ?? '');
    } else {
        $errorMessage = (string) ($flash['message'] ?? '');
    }
}

$internshipTitles = array_values(array_unique(array_filter(array_map(function ($row) {
    return trim($row['internship_title'] ?? '');
}, $applications))));
sort($internshipTitles);
$page_title = 'Applications';
$extra_css = '<link rel="stylesheet" href="../../../Assets/CSS/Company/applications.css?v=' . filemtime(__DIR__ . '/../../../Assets/CSS/Company/applications.css') . '">';

include '../../../Includes/company_sidebar.php';
include '../../../Includes/dash_header.php';
?>

<main class="content applications-page">
    <div class="applications-heading">
        <div>
            <p class="page-label">APPLICATIONS</p>
            <h1>Internship Applications</h1>
            <p class="page-description">Review applicants and make application decisions for your internships.</p>
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
            <label for="applicationSearch">Search</label>
            <input type="search" id="applicationSearch" placeholder="Student name, email or skill" autocomplete="off">
        </div>
        <div class="filter-group">
            <label for="internshipFilter">Internship</label>
            <select id="internshipFilter">
                <option value="all">All Internships</option>
                <?php foreach ($internshipTitles as $internshipOption): ?>
                    <option value="<?= htmlspecialchars($internshipOption, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($internshipOption) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
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
        <div class="filter-group">
            <label>&nbsp;</label>
            <button type="button" class="export-btn" id="clearFiltersBtn">Clear filters</button>
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
                    <tr id="noMatchRow" hidden><td colspan="6" class="applications-empty">No applications match your filters.</td></tr>
                    <?php foreach ($applications as $application): ?>
                        <?php
                            $name = trim($application['student_name'] ?? 'Student');
                            $nameParts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
                            $initials = strtoupper(substr($nameParts[0] ?? 'S', 0, 1) . substr($nameParts[count($nameParts) - 1] ?? '', 0, 1));
                            $status = $application['display_status'];
                            $date = !empty($application['applied_date']) ? date('M j, Y', strtotime($application['applied_date'])) : '—';
                            $skills = array_slice(array_filter(array_map('trim', explode(',', $application['skills']))), 0, 3);
                            $appId = (int) $application['application_id'];
                            $internshipTitle = trim($application['internship_title'] ?? '');
                            $interviewDate = trim($application['interview_date'] ?? '');
                            $interviewTime = trim($application['interview_time'] ?? '');
                            $interviewType = trim($application['interview_type'] ?? '');
                            $interviewLink = trim($application['interview_link'] ?? '');
                            $interviewNotes = trim($application['interview_notes'] ?? '');
                            $disqualifyReason = trim($application['disqualification_reason'] ?? '');
                        ?>
                        <tr class="application-row"
                            data-id="<?= $appId ?>"
                            data-candidate="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                            data-internship="<?= htmlspecialchars($internshipTitle, ENT_QUOTES, 'UTF-8') ?>"
                            data-status="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>"
                            data-search="<?= htmlspecialchars(strtolower($name . ' ' . ($application['email'] ?? '') . ' ' . ($application['skills'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"
                            data-university="<?= htmlspecialchars(preg_replace('/\s+/u', ' ', trim((string) ($application['University'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>"
                            data-interview-date="<?= htmlspecialchars($interviewDate, ENT_QUOTES, 'UTF-8') ?>"
                            data-interview-time="<?= htmlspecialchars($interviewTime, ENT_QUOTES, 'UTF-8') ?>"
                            data-interview-type="<?= htmlspecialchars($interviewType, ENT_QUOTES, 'UTF-8') ?>"
                            data-interview-link="<?= htmlspecialchars($interviewLink, ENT_QUOTES, 'UTF-8') ?>"
                            data-interview-notes="<?= htmlspecialchars($interviewNotes, ENT_QUOTES, 'UTF-8') ?>"
                            data-disqualification-reason="<?= htmlspecialchars($disqualifyReason, ENT_QUOTES, 'UTF-8') ?>">
                            <td><div class="student-cell"><div class="student-avatar avatar-blue"><?= htmlspecialchars($initials) ?></div><div><strong><?= htmlspecialchars($name) ?></strong><span><?= htmlspecialchars($application['email']) ?></span></div></div></td>
                            <td><?= htmlspecialchars($application['University'] ?? '—') ?></td>
                            <td><div class="skills-list">
                                <?php foreach ($skills as $skill): ?><span><?= htmlspecialchars($skill) ?></span><?php endforeach; ?>
                                <?php if (!$skills): ?><span>—</span><?php endif; ?>
                            </div></td>
                            <td><?= htmlspecialchars($date) ?></td>
                            <td>
                                <div class="status-cell-container">
                                    <span class="status <?= strtolower($status) ?>"><?= htmlspecialchars($status) ?></span>
                                    <?php if ($status === 'Accepted' && $interviewDate !== ''): ?>
                                        <div class="interview-slot-tag" title="Interview Scheduled: <?= htmlspecialchars($interviewDate) ?> <?= htmlspecialchars($interviewTime) ?>">
                                            <span class="material-symbols-outlined">event</span>
                                            <span><?= date('M j', strtotime($interviewDate)) ?><?= $interviewTime !== '' ? ' &bull; ' . htmlspecialchars($interviewTime) : '' ?></span>
                                        </div>
                                    <?php elseif ($status === 'Disqualified' && $disqualifyReason !== ''): ?>
                                        <div class="disqualify-reason-tag" title="Reason: <?= htmlspecialchars($disqualifyReason) ?>">
                                            <span class="material-symbols-outlined">info</span>
                                            <span><?= htmlspecialchars(mb_strimwidth($disqualifyReason, 0, 24, '...')) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a class="action-btn view-btn" href="candidates.php?application_id=<?= $appId ?>" title="View Candidate Details" aria-label="View Candidate Details">
                                        <span class="material-symbols-outlined">visibility</span>
                                    </a>
                                    <button type="button" class="action-btn status-change-btn" data-trigger-modal="<?= $appId ?>" data-mode="switch" title="Manage Status &amp; Interview" aria-label="Manage Status and Interview">
                                        <span class="material-symbols-outlined">published_with_changes</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="applications-pagination"><p>Showing <span id="visibleApplicationCount"><?= count($applications) ?></span> of <?= count($applications) ?> applications</p></div>
    </section>

    <!-- =========================================================
         DECISION & INTERVIEW SCHEDULING MODAL
    ========================================================== -->
    <div class="application-modal-overlay" id="decisionModal" hidden>
        <div class="application-modal-panel">
            <div class="application-modal-header">
                <div>
                    <span class="modal-badge-label">STATUS &amp; INTERVIEW MANAGEMENT</span>
                    <h2 id="modalCandidateTitle">Update Application Status</h2>
                    <p id="modalInternshipSubtitle">Manage candidate status, interview slot, or disqualification reason.</p>
                </div>
                <button type="button" class="modal-close-icon-btn" id="closeDecisionModalBtn" aria-label="Close modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form method="POST" id="decisionModalForm">
                <input type="hidden" name="application_id" id="modalApplicationId" value="0">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="modal-body-content">
                    <!-- STATUS TOGGLE CARDS -->
                    <div class="status-toggle-container">
                        <label class="status-toggle-card" id="cardOptAccepted">
                            <input type="radio" name="application_decision" value="Accepted" id="radioDecisionAccepted" required>
                            <div class="card-inner">
                                <div class="card-icon-box accept-box">
                                    <span class="material-symbols-outlined">event_available</span>
                                </div>
                                <div class="card-texts">
                                    <strong>Accept &amp; Interview</strong>
                                    <span>Schedule interview time slot</span>
                                </div>
                            </div>
                        </label>

                        <label class="status-toggle-card" id="cardOptDisqualified">
                            <input type="radio" name="application_decision" value="Disqualified" id="radioDecisionDisqualified" required>
                            <div class="card-inner">
                                <div class="card-icon-box disqualify-box">
                                    <span class="material-symbols-outlined">person_off</span>
                                </div>
                                <div class="card-texts">
                                    <strong>Disqualify</strong>
                                    <span>Provide rejection reason</span>
                                </div>
                            </div>
                        </label>
                        <label class="status-toggle-card" id="cardOptApplied" hidden>
                            <input type="radio" name="application_decision" value="Applied" id="radioDecisionApplied" required>
                            <div class="card-inner">
                                <div class="card-icon-box">
                                    <span class="material-symbols-outlined">undo</span>
                                </div>
                                <div class="card-texts">
                                    <strong>Undo decision</strong>
                                    <span>Back to Applied, interview removed</span>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- ACCEPT: INTERVIEW DETAILS -->
                    <div class="decision-conditional-section" id="acceptInterviewSection">
                        <div class="section-heading-mini">
                            <span class="material-symbols-outlined">calendar_month</span>
                            <h4>Interview Booking Information</h4>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group-custom">
                                <label for="modalInterviewDate">Interview Date <span class="req">*</span></label>
                                <input type="date" id="modalInterviewDate" name="interview_date" min="<?= date('Y-m-d') ?>">
                            </div>

                            <div class="form-group-custom">
                                <label for="modalInterviewTime">Time Slot <span class="req">*</span></label>
                                <select id="modalInterviewTime" name="interview_time">
                                    <option value="">Select Time Slot</option>
                                    <?php foreach ($interviewTimeSlots as $slot): ?>
                                        <option value="<?= htmlspecialchars($slot) ?>"><?= htmlspecialchars($slot) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group-custom">
                                <label for="modalInterviewType">Interview Format</label>
                                <select id="modalInterviewType" name="interview_type">
                                    <option value="Video Interview (Google Meet / Zoom)">Video Interview (Google Meet / Zoom)</option>
                                    <option value="In-Person Interview">In-Person Interview</option>
                                </select>
                            </div>

                            <div class="form-group-custom">
                                <label for="modalInterviewLink">Meeting Link / Location</label>
                                <input type="text" id="modalInterviewLink" name="interview_link" placeholder="e.g. meet.google.com/xyz or Room 302">
                            </div>
                        </div>

                        <div class="form-group-custom">
                            <label for="modalInterviewNotes">Instructions / Notes for Candidate (Optional)</label>
                            <textarea id="modalInterviewNotes" name="interview_notes" rows="2" placeholder="e.g. Please bring your portfolio and be prepared to discuss your project work..."></textarea>
                        </div>
                    </div>

                    <!-- DISQUALIFY: REASON DETAILS -->
                    <div class="decision-conditional-section" id="disqualifySection" hidden>
                        <div class="section-heading-mini">
                            <span class="material-symbols-outlined">report_problem</span>
                            <h4>Reason for Disqualification</h4>
                        </div>

                        <div class="form-group-custom">
                            <label for="modalDisqualificationReason">Primary Reason <span class="req">*</span></label>
                            <select id="modalDisqualificationReason" name="disqualification_reason">
                                <option value="">Select a reason</option>
                                <?php foreach ($disqualifyReasons as $reasonOption): ?>
                                    <option value="<?= htmlspecialchars($reasonOption) ?>"><?= htmlspecialchars($reasonOption) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group-custom">
                            <label for="modalDisqualificationNotes">Additional Feedback / Note (Optional)</label>
                            <textarea id="modalDisqualificationNotes" name="disqualification_notes" rows="3" placeholder="Provide additional feedback or internal reason..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="application-modal-footer">
                    <button type="button" class="btn-modal-cancel" id="cancelDecisionModalBtn">Cancel</button>
                    <button type="submit" class="btn-modal-save" id="saveDecisionModalBtn">
                        <span class="material-symbols-outlined">check_circle</span>
                        Save Decision
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../../../Includes/company_dashboard_footer.php'; ?>
</main>
<script src="../../../Assets/JS/Company/applications.js"></script>
<?php include '../../../Includes/dash_footer.php'; ?>
