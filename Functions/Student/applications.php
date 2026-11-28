<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Config/db.php';
require_once __DIR__ . '/../../Session/Session.php';
require_once __DIR__ . '/../../Backend/Student/StudentInternshipRepository.php';

require_role('student');
$user = current_user();
$email = trim((string) ($user['Email'] ?? $user['email'] ?? ''));
if ($email === '') {
    http_response_code(403);
    exit('Student email not found in session.');
}

$repo = new StudentInternshipRepository($conn);
$filterStatus = strtolower(trim((string) ($_GET['status'] ?? 'all')));
$allowedFilters = ['all', 'pending', 'shortlisted', 'under_review', 'accepted', 'rejected'];
if (!in_array($filterStatus, $allowedFilters, true)) {
    $filterStatus = 'all';
}

$stats = [
    'pending'      => 0,
    'shortlisted'  => 0,
    'under_review' => 0,
    'accepted'     => 0,
    'rejected'     => 0,
];

$applications = [];
$errorMsg = null;

try {
    $hasInterviewsTable = false;
    $tCheck = $conn->prepare(
        "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'internship_interviews'"
    );
    if ($tCheck) {
        $tCheck->execute();
        $hasInterviewsTable = (int) $tCheck->get_result()->fetch_row()[0] > 0;
        $tCheck->close();
    }

    $hasCompanyCol = false;
    $cCheck = $conn->prepare(
        "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'internships' AND COLUMN_NAME = 'company'"
    );
    if ($cCheck) {
        $cCheck->execute();
        $hasCompanyCol = (int) $cCheck->get_result()->fetch_row()[0] > 0;
        $cCheck->close();
    }

    $companyExpr = $hasCompanyCol
        ? "COALESCE(NULLIF(TRIM(i.company), ''), c.Name, i.company_email)"
        : "COALESCE(c.Name, i.company_email)";

    $interviewSelect = $hasInterviewsTable
        ? ", ii.interview_date, ii.interview_time, ii.interview_type, ii.interview_link, ii.interview_notes"
        : ", NULL AS interview_date, NULL AS interview_time, NULL AS interview_type, NULL AS interview_link, NULL AS interview_notes";

    $interviewJoin = $hasInterviewsTable
        ? "LEFT JOIN internship_interviews ii ON ii.application_id = ia.application_id"
        : "";

    $sql = "SELECT
                ia.application_id,
                ia.Email,
                ia.internship_id,
                ia.application_title,
                ia.cover_note,
                ia.relevant_experience,
                ia.selection_rationale,
                ia.resume_file,
                ia.status,
                ia.applied_date,
                i.title AS position,
                i.location AS internship_location,
                i.work_mode,
                i.internship_type,
                {$companyExpr} AS company,
                c.location AS company_location
                {$interviewSelect}
            FROM internship_applications ia
            INNER JOIN internships i ON i.id = ia.internship_id
            LEFT JOIN company c ON LOWER(TRIM(c.Email)) = LOWER(TRIM(i.company_email))
            {$interviewJoin}
            WHERE LOWER(TRIM(ia.Email)) = LOWER(TRIM(?))
            ORDER BY ia.applied_date DESC, ia.application_id DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rawStatus = trim((string) ($row['status'] ?? 'Pending'));
            $stLower = strtolower($rawStatus);

            if (in_array($stLower, ['accepted', 'approved', 'hired', 'selected'], true)) {
                $statusKey = 'accepted';
                $statusLabel = 'Accepted';
                $stats['accepted']++;
            } elseif (in_array($stLower, ['rejected', 'disqualified'], true)) {
                $statusKey = 'rejected';
                $statusLabel = 'Rejected';
                $stats['rejected']++;
            } elseif ($stLower === 'shortlisted') {
                $statusKey = 'shortlisted';
                $statusLabel = 'Shortlisted';
                $stats['shortlisted']++;
            } elseif (in_array($stLower, ['under review', 'under_review', 'review'], true)) {
                $statusKey = 'under_review';
                $statusLabel = 'Under Review';
                $stats['under_review']++;
            } else {
                $statusKey = 'pending';
                $statusLabel = 'Pending';
                $stats['pending']++;
            }

            $row['_status_key'] = $statusKey;
            $row['_status_label'] = $statusLabel;

            if ($filterStatus === 'all' || $filterStatus === $statusKey) {
                $applications[] = $row;
            }
        }
        $stmt->close();
    }
} catch (Throwable $e) {
    error_log('Failed to load internship applications: ' . $e->getMessage());
    $errorMsg = 'Unable to load your internship applications right now.';
}

$appliedSuccess = isset($_GET['applied']) && $_GET['applied'] === '1';

function trackEsc(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function trackInitials(string $name): string
{
    $words = preg_split('/\s+/', trim($name)) ?: [];
    $initials = '';
    foreach ($words as $w) {
        if ($w !== '') {
            $initials .= strtoupper(mb_substr($w, 0, 1));
        }
    }
    return $initials !== '' ? mb_substr($initials, 0, 2) : 'IN';
}

$pageTitle = 'Application Tracking | SkillBridge';
$cssVer = is_file(__DIR__ . '/../../Assets/CSS/Student/applications.css') ? (string) filemtime(__DIR__ . '/../../Assets/CSS/Student/applications.css') : '1';
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/dashboard.css">'
    . '<link rel="stylesheet" href="../../Assets/CSS/Student/applications.css?v=' . $cssVer . '">'
    . '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">';

include __DIR__ . '/../../Includes/student_sidebar.php';
include __DIR__ . '/../../Includes/dash_header.php';
?>

<main class="content applications-page">
    <section class="applications-header">
        <div>
            <h1>Internship Applications</h1>
            <p>Track every internship application you submitted, inspect your responses and CV, and check status updates.</p>
        </div>
        <div class="header-actions">
            <a href="internships.php" class="new-application">
                <i class="fa-solid fa-plus"></i> Browse Internships
            </a>
        </div>
    </section>

    <?php if ($appliedSuccess): ?>
        <div class="app-toast" id="appSubmittedToast" role="status">
            <span>Internship application submitted successfully.</span>
            <button type="button" onclick="this.parentElement.remove()" aria-label="Close">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div class="app-alert-error" role="alert"><?= trackEsc($errorMsg) ?></div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <section class="application-stats">
        <div class="stat-card">
            <div class="stat-icon submitted"><i class="fa-solid fa-clock"></i></div>
            <div class="stat-content">
                <span>PENDING</span>
                <strong><?= str_pad((string) $stats['pending'], 2, '0', STR_PAD_LEFT) ?></strong>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon interview"><i class="fa-solid fa-star"></i></div>
            <div class="stat-content">
                <span>SHORTLISTED / REVIEW</span>
                <strong><?= str_pad((string) ($stats['shortlisted'] + $stats['under_review']), 2, '0', STR_PAD_LEFT) ?></strong>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon offer"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-content">
                <span>ACCEPTED</span>
                <strong><?= str_pad((string) $stats['accepted'], 2, '0', STR_PAD_LEFT) ?></strong>
            </div>
        </div>
    </section>

    <!-- Applications Table -->
    <section class="applications-table-card">
        <div class="table-header">
            <h2>Submitted Internship Applications (<?= count($applications) ?>)</h2>
            <form method="GET" action="applications.php" id="statusFilterForm" class="status-filter">
                <label for="statusFilter">Filter by Status:</label>
                <select name="status" id="statusFilter" onchange="document.getElementById('statusFilterForm').submit();">
                    <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="shortlisted" <?= $filterStatus === 'shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                    <option value="under_review" <?= $filterStatus === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                    <option value="accepted" <?= $filterStatus === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                    <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </form>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>COMPANY</th>
                        <th>INTERNSHIP POSITION</th>
                        <th>APPLIED DATE</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="5" class="empty-table-row">
                                No internship applications found. <a href="internships.php">Explore internships</a> to apply.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <?php
                            $statusKey = (string) ($app['_status_key'] ?? 'pending');
                            $statusLabel = (string) ($app['_status_label'] ?? 'Pending');

                            $companyName = (string) ($app['company'] ?? 'Company');
                            $locLabel = (string) ($app['internship_location'] ?: ($app['company_location'] ?: ($app['work_mode'] ?: 'Not specified')));
                            $typeLabel = (string) ($app['internship_type'] ?: ($app['work_mode'] ?: 'Internship'));
                            $formattedDate = !empty($app['applied_date']) && strtotime((string) $app['applied_date']) !== false
                                ? date('M d, Y', strtotime((string) $app['applied_date']))
                                : '-';

                            $appTitleText = trim((string) ($app['application_title'] ?? ''));
                            $displayPositionTitle = $appTitleText !== '' ? $appTitleText : $typeLabel;

                            $resumeRel = trim((string) ($app['resume_file'] ?? ''));
                            $resumeUrl = '';
                            if ($resumeRel !== '') {
                                $cleanResume = ltrim(str_replace('\\', '/', $resumeRel), '/');
                                if (str_starts_with($cleanResume, 'http://') || str_starts_with($cleanResume, 'https://')) {
                                    $resumeUrl = $cleanResume;
                                } else {
                                    $resumeUrl = '../../' . $cleanResume;
                                }
                            }

                            $detailPayload = [
                                'position'            => (string) ($app['position'] ?? ''),
                                'company'             => $companyName,
                                'location'            => $locLabel,
                                'type'                => $typeLabel,
                                'applied_date'        => $formattedDate,
                                'status'              => $statusLabel,
                                'status_key'          => $statusKey,
                                'application_title'   => (string) ($app['application_title'] ?? ''),
                                'cover_note'          => (string) ($app['cover_note'] ?? ''),
                                'relevant_experience' => (string) ($app['relevant_experience'] ?? ''),
                                'selection_rationale' => (string) ($app['selection_rationale'] ?? ''),
                                'resume_name'         => $resumeRel !== '' ? basename($resumeRel) : '',
                                'resume_url'          => $resumeUrl,
                                'internship_url'      => 'view_internship.php?id=' . (int) $app['internship_id'],
                                'interview_date'      => (string) ($app['interview_date'] ?? ''),
                                'interview_time'      => (string) ($app['interview_time'] ?? ''),
                                'interview_type'      => (string) ($app['interview_type'] ?? ''),
                                'interview_link'      => (string) ($app['interview_link'] ?? ''),
                                'interview_notes'     => (string) ($app['interview_notes'] ?? ''),
                            ];
                            ?>
                            <tr>
                                <td>
                                    <div class="company-cell">
                                        <div class="company-avatar"><?= trackEsc(trackInitials($companyName)) ?></div>
                                        <div>
                                            <strong><?= trackEsc($companyName) ?></strong>
                                            <small><?= trackEsc($locLabel) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= trackEsc((string) ($app['position'] ?? '')) ?></strong>
                                    <small><?= trackEsc($displayPositionTitle) ?></small>
                                </td>
                                <td><?= trackEsc($formattedDate) ?></td>
                                <td>
                                    <span class="status <?= trackEsc($statusKey) ?>"><?= trackEsc($statusLabel) ?></span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <button type="button"
                                                class="action-btn view-app-btn"
                                                title="View Submitted Application"
                                                aria-label="View Submitted Application"
                                                data-application='<?= htmlspecialchars(json_encode($detailPayload, JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>'>
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                        <a href="view_internship.php?id=<?= (int) $app['internship_id'] ?>"
                                           class="action-link-btn"
                                           title="View Internship Details"
                                           aria-label="View Internship Details">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<!-- Submitted Application Details Modal -->
<div class="app-modal-overlay" id="appDetailModal" hidden>
    <div class="app-modal-card" role="dialog" aria-modal="true" aria-labelledby="appModalPosition">
        <div class="app-modal-header">
            <div>
                <span class="status pending" id="appModalStatus">Pending</span>
                <h2 id="appModalPosition">Internship Position</h2>
                <p id="appModalMeta">Company &bull; Applied Date</p>
            </div>
            <button type="button" class="app-modal-close" id="closeAppModalBtn" aria-label="Close modal">&times;</button>
        </div>

        <div class="app-modal-body">
            <div class="app-detail-block">
                <label>Application Title</label>
                <p id="appModalTitle">-</p>
            </div>

            <div class="app-detail-block">
                <label>Cover Note / Introduction</label>
                <p id="appModalCover">-</p>
            </div>

            <div class="app-detail-block">
                <label>Relevant Experience</label>
                <p id="appModalExperience">-</p>
            </div>

            <div class="app-detail-block">
                <label>Why Should We Select You?</label>
                <p id="appModalRationale">-</p>
            </div>

            <!-- Always visible section showing submitted CV / document status -->
            <div class="app-detail-block" id="appModalResumeBox">
                <label>Submitted CV / Document</label>
                <div class="app-resume-row" id="appModalResumeAttached">
                    <div class="app-resume-meta">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span id="appModalResumeName">resume.pdf</span>
                    </div>
                    <div class="app-resume-actions">
                        <a id="appModalResumeViewLink" href="#" target="_blank" rel="noopener" class="app-view-btn">
                            <i class="fa-regular fa-eye"></i> View CV
                        </a>
                        <a id="appModalResumeDownloadLink" href="#" target="_blank" rel="noopener" download class="app-download-btn">
                            <i class="fa-solid fa-download"></i> Download
                        </a>
                    </div>
                </div>
                <div class="app-resume-empty" id="appModalResumeEmpty" hidden>
                    No CV / document was attached to this application.
                </div>
            </div>

            <div class="app-interview-box" id="appModalInterviewBox" hidden>
                <h3><i class="fa-solid fa-calendar-check"></i> Interview Details</h3>
                <p id="appModalInterviewSchedule"></p>
                <p id="appModalInterviewLink"></p>
                <p id="appModalInterviewNotes"></p>
            </div>
        </div>

        <div class="app-modal-footer">
            <a id="appModalInternshipLink" href="internships.php" class="app-modal-secondary">View Internship</a>
            <button type="button" class="app-modal-primary" id="doneAppModalBtn">Close</button>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const toast = document.getElementById("appSubmittedToast");
    if (toast) {
        setTimeout(() => toast.remove(), 3500);
    }

    const modal = document.getElementById("appDetailModal");
    const closeBtn = document.getElementById("closeAppModalBtn");
    const doneBtn = document.getElementById("doneAppModalBtn");

    function closeModal() {
        if (modal) modal.hidden = true;
    }

    document.querySelectorAll(".view-app-btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
            let data = {};
            try {
                data = JSON.parse(this.getAttribute("data-application") || "{}");
            } catch (e) {
                data = {};
            }

            document.getElementById("appModalPosition").textContent = data.position || "Internship Application";
            document.getElementById("appModalMeta").textContent =
                (data.company || "Company") +
                (data.location ? " • " + data.location : "") +
                (data.applied_date ? " • Applied " + data.applied_date : "");

            const badge = document.getElementById("appModalStatus");
            const sk = (data.status_key || "pending").toLowerCase();
            badge.textContent = data.status || "Pending";
            badge.className = "status " + sk;

            document.getElementById("appModalTitle").textContent = data.application_title || "Not provided";
            document.getElementById("appModalCover").textContent = data.cover_note || "Not provided";
            document.getElementById("appModalExperience").textContent = data.relevant_experience || "Not provided";
            document.getElementById("appModalRationale").textContent = data.selection_rationale || "Not provided";

            const resumeAttached = document.getElementById("appModalResumeAttached");
            const resumeEmpty = document.getElementById("appModalResumeEmpty");
            const resumeViewLink = document.getElementById("appModalResumeViewLink");
            const resumeDownloadLink = document.getElementById("appModalResumeDownloadLink");
            const resumeName = document.getElementById("appModalResumeName");

            if (data.resume_url) {
                resumeName.textContent = data.resume_name || "Submitted CV";
                resumeViewLink.href = data.resume_url;
                resumeDownloadLink.href = data.resume_url;
                resumeAttached.hidden = false;
                resumeEmpty.hidden = true;
            } else {
                resumeAttached.hidden = true;
                resumeEmpty.hidden = false;
            }

            const intBox = document.getElementById("appModalInterviewBox");
            if (data.interview_date || data.interview_link || data.interview_notes) {
                document.getElementById("appModalInterviewSchedule").textContent =
                    "Schedule: " + (data.interview_date || "TBA") +
                    (data.interview_time ? " (" + data.interview_time + ")" : "") +
                    (data.interview_type ? " — " + data.interview_type : "");

                const linkEl = document.getElementById("appModalInterviewLink");
                if (data.interview_link) {
                    linkEl.innerHTML = 'Meeting / Link: <a href="' + encodeURI(data.interview_link) + '" target="_blank" rel="noopener">' + data.interview_link + '</a>';
                    linkEl.hidden = false;
                } else {
                    linkEl.hidden = true;
                }

                const notesEl = document.getElementById("appModalInterviewNotes");
                if (data.interview_notes) {
                    notesEl.textContent = "Notes: " + data.interview_notes;
                    notesEl.hidden = false;
                } else {
                    notesEl.hidden = true;
                }

                intBox.hidden = false;
            } else {
                intBox.hidden = true;
            }

            const internLink = document.getElementById("appModalInternshipLink");
            if (internLink && data.internship_url) {
                internLink.href = data.internship_url;
            }

            if (modal) modal.hidden = false;
        });
    });

    if (closeBtn) closeBtn.addEventListener("click", closeModal);
    if (doneBtn) doneBtn.addEventListener("click", closeModal);
    if (modal) {
        modal.addEventListener("click", function (e) {
            if (e.target === modal) closeModal();
        });
    }
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && modal && !modal.hidden) closeModal();
    });
});
</script>

<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>