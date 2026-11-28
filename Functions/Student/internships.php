<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Config/db.php';
require_once __DIR__ . '/../../Session/Session.php';
require_once __DIR__ . '/../../Backend/Student/StudentInternshipRepository.php';
require_once __DIR__ . '/check_student_access.php';

require_role('student');
$user = current_user();
$email = (string) ($user['Email'] ?? $user['email'] ?? '');
if ($email === '') {
    http_response_code(403);
    exit('Student email not found in session.');
}

if (empty($_SESSION['student_internship_csrf'])) {
    $_SESSION['student_internship_csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['student_internship_csrf'];

$repo = new StudentInternshipRepository($conn);
$canApply = canApplyInternship($email);

// Handle Save / Bookmark toggle
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'toggle_save') {
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Invalid security token.');
    }
    $targetId = filter_var($_POST['internship_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $returnTo = (string) ($_POST['return_to'] ?? 'internships.php');
    $allowedReturn = ['internships.php', 'saved_internships.php', 'view_internship.php'];
    if (!in_array($returnTo, $allowedReturn, true)) {
        $returnTo = 'internships.php';
    }

    if ($targetId) {
        try {
            $savedNow = $repo->toggleSaved($email, (int) $targetId);
            $_SESSION['student_internship_flash'] = [
                'text'  => $savedNow ? 'Internship saved successfully.' : 'Internship removed successfully.',
                'error' => false,
            ];
        } catch (Throwable $e) {
            error_log('Toggle saved internship error: ' . $e->getMessage());
            $_SESSION['student_internship_flash'] = [
                'text'  => 'Unable to update saved internships.',
                'error' => true,
            ];
        }
    }

    if ($returnTo === 'view_internship.php' && $targetId) {
        header('Location: view_internship.php?id=' . (int) $targetId, true, 303);
    } elseif ($returnTo === 'saved_internships.php') {
        header('Location: saved_internships.php', true, 303);
    } else {
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        header('Location: internships.php' . ($qs !== '' ? '?' . $qs : ''), true, 303);
    }
    exit;
}

$filters = [
    'search'    => trim((string) ($_GET['search'] ?? '')),
    'industry'  => trim((string) ($_GET['industry'] ?? '')),
    'work_mode' => trim((string) ($_GET['work_mode'] ?? '')),
    'schedule'  => trim((string) ($_GET['schedule'] ?? '')),
];

$industries = [];
$internships = [];
$savedIds = [];
$appliedIds = [];
$errorMsg = null;

try {
    $industries = $repo->industries();
    $internships = $repo->listInternships($filters);
    $savedIds = $repo->savedIds($email);
    $appliedIds = $repo->appliedIds($email);
} catch (Throwable $e) {
    error_log('Student internships load error: ' . $e->getMessage());
    $errorMsg = 'Unable to load internships right now. Please try again.';
}

$flash = $_SESSION['student_internship_flash'] ?? null;
unset($_SESSION['student_internship_flash']);

function internEsc(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function companyInitials(string $name): string
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

$pageTitle = 'Find Internships | SkillBridge';
$cssVer = is_file(__DIR__ . '/../../Assets/CSS/Student/internships.css') ? (string) filemtime(__DIR__ . '/../../Assets/CSS/Student/internships.css') : '1';
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/dashboard.css">'
    . '<link rel="stylesheet" href="../../Assets/CSS/Student/internships.css?v=' . $cssVer . '">'
    . '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">';

include __DIR__ . '/../../Includes/student_sidebar.php';
include __DIR__ . '/../../Includes/dash_header.php';
?>

<main class="content internship-page">
    <section class="internship-header">
        <div>
            <h1>Find Internships</h1>
            <p>Kickstart your career with real internship opportunities from partner companies.</p>
        </div>
        <div class="internship-header-actions">
            <a href="applications.php" class="btn-secondary">
                <i class="fa-solid fa-file-lines"></i> My Applications
            </a>
            <a href="saved_internships.php" class="btn-secondary">
                <i class="fa-solid fa-bookmark"></i> Saved Internships (<?= count($savedIds) ?>)
            </a>
        </div>
    </section>

    <?php if ($flash): ?>
        <div class="internship-toast <?= !empty($flash['error']) ? 'error' : '' ?>" id="internshipToast" role="status">
            <span><?= internEsc((string) $flash['text']) ?></span>
            <button type="button" onclick="this.parentElement.remove()" aria-label="Close">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!$canApply): ?>
        <div class="internship-notice-banner">
            <i class="fa-solid fa-circle-info"></i> Internship applications are open to Year 3 and Year 4 students. You can still browse internships and bookmark them for later.
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div class="internship-error-banner">
            <?= internEsc($errorMsg) ?>
        </div>
    <?php endif; ?>

    <form class="internship-filter" method="GET" action="internships.php" id="internshipFilterForm">
        <div class="filter-box">
            <label for="searchFilter">Keyword / Company</label>
            <input type="text" id="searchFilter" name="search" value="<?= internEsc($filters['search']) ?>" placeholder="Role, company or city...">
        </div>

        <div class="filter-box">
            <label for="industryFilter">Industry</label>
            <select id="industryFilter" name="industry">
                <option value="">All Industries</option>
                <?php foreach ($industries as $ind): ?>
                    <option value="<?= internEsc($ind) ?>" <?= $filters['industry'] === $ind ? 'selected' : '' ?>>
                        <?= internEsc($ind) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-box">
            <label for="locationFilter">Work Mode</label>
            <select id="locationFilter" name="work_mode">
                <option value="">All Work Modes</option>
                <?php foreach (['Remote', 'On-site', 'Hybrid'] as $mode): ?>
                    <option value="<?= internEsc($mode) ?>" <?= strcasecmp($filters['work_mode'], $mode) === 0 ? 'selected' : '' ?>>
                        <?= internEsc($mode) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-box">
            <label for="scheduleFilter">Schedule</label>
            <select id="scheduleFilter" name="schedule">
                <option value="">All Schedules</option>
                <?php foreach (['Full-time', 'Part-time'] as $sched): ?>
                    <option value="<?= internEsc($sched) ?>" <?= strcasecmp($filters['schedule'], $sched) === 0 ? 'selected' : '' ?>>
                        <?= internEsc($sched) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-action">
            <button type="submit" class="filter-submit-btn">Filter</button>
            <a href="internships.php" class="clear-filter">
                <i class="fa-solid fa-filter-circle-xmark"></i> Clear
            </a>
        </div>
    </form>

    <section class="internship-grid">
        <?php if (empty($internships)): ?>
            <div class="internship-empty-state">
                No matching internships found in the database.
            </div>
        <?php else: ?>
            <?php foreach ($internships as $internship): ?>
                <?php
                $iid = (int) $internship['id'];
                $companyName = (string) ($internship['company_name'] ?? 'Company');
                $locationText = trim((string) ($internship['location'] ?? ''));
                $workModeText = trim((string) ($internship['work_mode'] ?? ''));
                $badgeLabel = $workModeText !== '' ? $workModeText : (string) ($internship['internship_type'] ?? 'Internship');
                $displayLoc = $locationText !== '' ? $locationText : ($workModeText !== '' ? $workModeText : 'Not specified');
                $isApplied = in_array($iid, $appliedIds, true);
                $isExpired = !empty($internship['is_expired']);
                $isSaved = in_array($iid, $savedIds, true);
                $actionQs = $_SERVER['QUERY_STRING'] ?? '';
                ?>
                <div class="internship-card">
                    <form method="POST" action="internships.php<?= $actionQs !== '' ? '?' . internEsc($actionQs) : '' ?>" class="internship-save-top-form">
                        <input type="hidden" name="csrf" value="<?= internEsc($csrf) ?>">
                        <input type="hidden" name="action" value="toggle_save">
                        <input type="hidden" name="return_to" value="internships.php">
                        <input type="hidden" name="internship_id" value="<?= $iid ?>">
                        <button type="submit"
                                class="internship-save-top-btn <?= $isSaved ? 'saved' : '' ?>"
                                title="<?= $isSaved ? 'Remove from Saved' : 'Save Internship' ?>"
                                aria-label="<?= $isSaved ? 'Remove from Saved' : 'Save Internship' ?>">
                            <i class="fa-<?= $isSaved ? 'solid' : 'regular' ?> fa-bookmark"></i>
                        </button>
                    </form>

                    <div class="card-top">
                        <div class="company-logo">
                            <span><?= internEsc(companyInitials($companyName)) ?></span>
                        </div>
                        <span class="status-badge"><?= internEsc($badgeLabel) ?></span>
                    </div>

                    <h2><?= internEsc((string) $internship['title']) ?></h2>
                    <p class="company-name"><?= internEsc($companyName) ?></p>

                    <p class="location">
                        <i class="fa-solid fa-location-dot"></i>
                        <?= internEsc($displayLoc) ?>
                    </p>

                    <div class="internship-tags">
                        <?php foreach (array_slice($internship['skills'] ?? [], 0, 4) as $skill): ?>
                            <span><?= internEsc($skill) ?></span>
                        <?php endforeach; ?>
                        <?php if (empty($internship['skills'])): ?>
                            <span><?= internEsc((string) $internship['industry_name']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="internship-info">
                        <div>
                            <small>Duration</small>
                            <strong><?= internEsc((string) $internship['duration_label']) ?></strong>
                        </div>
                        <div>
                            <small>Deadline</small>
                            <strong><?= internEsc((string) $internship['formatted_deadline']) ?></strong>
                        </div>
                    </div>

                    <div class="internship-actions">
                        <a href="view_internship.php?id=<?= $iid ?>" class="view-details">
                            <i class="fa-solid fa-eye"></i> View Details
                        </a>

                        <?php if ($isApplied): ?>
                            <span class="status-badge is-applied">Applied</span>
                        <?php elseif ($isExpired): ?>
                            <span class="status-badge is-closed">Closed</span>
                        <?php elseif ($canApply): ?>
                            <a href="submit_application.php?id=<?= $iid ?>" class="apply-now">
                                <i class="fa-solid fa-paper-plane"></i> Apply Now
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const toast = document.getElementById("internshipToast");
    if (toast) {
        setTimeout(() => toast.remove(), 3500);
    }
});
</script>

<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>