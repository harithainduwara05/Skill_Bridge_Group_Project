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

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    http_response_code(400);
    exit('Invalid internship ID.');
}

$repo = new StudentInternshipRepository($conn);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'toggle_save') {
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Invalid security token.');
    }
    try {
        $savedNow = $repo->toggleSaved($email, (int) $id);
        $_SESSION['student_internship_flash'] = [
            'text'  => $savedNow ? 'Internship saved successfully.' : 'Internship removed successfully.',
            'error' => false,
        ];
    } catch (Throwable $e) {
        error_log('Toggle saved internship error: ' . $e->getMessage());
        $_SESSION['student_internship_flash'] = [
            'text'  => $e->getMessage(),
            'error' => true,
        ];
    }
    header('Location: view_internship.php?id=' . (int) $id, true, 303);
    exit;
}

$internship = $repo->findInternship((int) $id);
if (!$internship) {
    http_response_code(404);
    exit('Internship not found or unavailable.');
}

$isSaved = in_array((int) $id, $repo->savedIds($email), true);
$isApplied = $repo->hasApplied($email, (int) $id);
$canApply = canApplyInternship($email);
$isExpired = !empty($internship['is_expired']);

$flash = $_SESSION['student_internship_flash'] ?? null;
unset($_SESSION['student_internship_flash']);

$companyName = (string) ($internship['company_name'] ?? 'Company');
$words = preg_split('/\s+/', trim($companyName)) ?: [];
$initials = strtoupper(mb_substr($words[0] ?? 'C', 0, 1) . mb_substr($words[1] ?? '', 0, 1));

$responsibilities = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($internship['responsibilities'] ?? '')) ?: [])));
$benefits = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($internship['benefits'] ?? '')) ?: [])));

function viewInternEsc(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$pageTitle = 'View Internship | SkillBridge';
$cssVer = is_file(__DIR__ . '/../../Assets/CSS/Student/view_internship.css') ? (string) filemtime(__DIR__ . '/../../Assets/CSS/Student/view_internship.css') : '1';
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/dashboard.css">'
    . '<link rel="stylesheet" href="../../Assets/CSS/Student/view_internship.css?v=' . $cssVer . '">'
    . '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">';

include __DIR__ . '/../../Includes/student_sidebar.php';
include __DIR__ . '/../../Includes/dash_header.php';
?>

<main class="content view-internship-page">
    <?php if ($flash): ?>
        <div class="internship-toast <?= !empty($flash['error']) ? 'error' : '' ?>" id="viewInternshipToast" role="status">
            <span><?= viewInternEsc((string) $flash['text']) ?></span>
            <button type="button" onclick="this.parentElement.remove()" aria-label="Close">&times;</button>
        </div>
    <?php endif; ?>

    <div class="breadcrumb">
        <a href="internships.php">Internships</a>
        <span>&gt;</span>
        <strong>View Internship</strong>
    </div>

    <section class="internship-header-card">
        <div class="internship-info">
            <div class="internship-tags">
                <span><?= viewInternEsc((string) $internship['industry_name']) ?></span>
                <?php if (!empty($internship['work_mode'])): ?>
                    <span><?= viewInternEsc((string) $internship['work_mode']) ?></span>
                <?php endif; ?>
                <?php if ($isExpired): ?>
                    <span class="open-tag closed-tag">Applications Closed</span>
                <?php else: ?>
                    <span class="open-tag">Open Internship</span>
                <?php endif; ?>
            </div>

            <h1><?= viewInternEsc((string) $internship['title']) ?></h1>
            <p><?= nl2br(viewInternEsc((string) ($internship['description'] ?? 'No description provided.'))) ?></p>
        </div>

        <div class="internship-buttons">
            <?php if ($isApplied): ?>
                <a href="applications.php" class="apply-btn is-applied">
                    <i class="fa-solid fa-check"></i> View Application
                </a>
            <?php elseif (!$isExpired && $canApply): ?>
                <a href="submit_application.php?id=<?= (int) $id ?>" class="apply-btn">
                    <i class="fa-solid fa-paper-plane"></i> Apply Now
                </a>
            <?php endif; ?>

            <form method="POST" action="view_internship.php?id=<?= (int) $id ?>">
                <input type="hidden" name="csrf" value="<?= viewInternEsc($csrf) ?>">
                <input type="hidden" name="action" value="toggle_save">
                <button type="submit" class="save-btn">
                    <i class="fa-<?= $isSaved ? 'solid' : 'regular' ?> fa-bookmark"></i>
                    <?= $isSaved ? 'Remove Saved' : 'Save Internship' ?>
                </button>
            </form>
        </div>
    </section>

    <section class="internship-stat-wrapper">
        <div class="internship-stat">
            <div class="stat-circle"><i class="fa-regular fa-clock"></i></div>
            <div>
                <label>Duration</label>
                <strong><?= viewInternEsc((string) $internship['duration_label']) ?></strong>
            </div>
        </div>

        <div class="internship-stat">
            <div class="stat-circle"><i class="fa-solid fa-location-dot"></i></div>
            <div>
                <label>Location / Mode</label>
                <strong><?= viewInternEsc((string) ($internship['location'] ?: ($internship['work_mode'] ?: 'Not specified'))) ?></strong>
            </div>
        </div>

        <div class="internship-stat">
            <div class="stat-circle"><i class="fa-regular fa-calendar"></i></div>
            <div>
                <label>Deadline</label>
                <strong><?= viewInternEsc((string) $internship['formatted_deadline']) ?></strong>
            </div>
        </div>
    </section>

    <div class="internship-content-grid">
        <div>
            <section class="info-card">
                <h2><i class="fa-solid fa-circle-info"></i> Internship Overview</h2>
                <p><?= nl2br(viewInternEsc((string) ($internship['description'] ?? 'No description provided.'))) ?></p>

                <h3>Responsibilities</h3>
                <?php if ($responsibilities): ?>
                    <ul>
                        <?php foreach ($responsibilities as $item): ?>
                            <li><?= viewInternEsc($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Responsibilities will be shared during onboarding.</p>
                <?php endif; ?>

                <h3>Learning Opportunities &amp; Benefits</h3>
                <?php if ($benefits): ?>
                    <ul>
                        <?php foreach ($benefits as $item): ?>
                            <li><?= viewInternEsc($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Hands-on industry mentorship and real project experience.</p>
                <?php endif; ?>
            </section>
        </div>

        <div>
            <section class="info-card">
                <h2><i class="fa-solid fa-code"></i> Required Skills</h2>
                <?php if (!empty($internship['skills'])): ?>
                    <?php foreach ($internship['skills'] as $skill): ?>
                        <span class="skill-pill"><?= viewInternEsc($skill) ?></span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="skill-pill"><?= viewInternEsc((string) $internship['industry_name']) ?></span>
                <?php endif; ?>
            </section>

            <section class="company-card">
                <div class="company-header">
                    <div class="company-logo"><span><?= viewInternEsc($initials) ?></span></div>
                    <div>
                        <h3><?= viewInternEsc($companyName) ?></h3>
                        <p><i class="fa-solid fa-certificate"></i> <?= strcasecmp((string) ($internship['company_verified_status'] ?? ''), 'Verify') === 0 ? 'Verified Company' : 'Partner Company' ?></p>
                    </div>
                </div>

                <?php if (!empty($internship['paid_status'])): ?>
                    <p class="company-description">
                        Stipend Status: <strong><?= viewInternEsc((string) $internship['paid_status']) ?></strong>
                        <?php if (!empty($internship['stipend']) && (float) $internship['stipend'] > 0): ?>
                            (<?= number_format((float) $internship['stipend'], 2) ?>)
                        <?php endif; ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($internship['company_hq_location'])): ?>
                    <p class="company-hq">
                        <i class="fa-solid fa-building"></i> <?= viewInternEsc((string) $internship['company_hq_location']) ?>
                    </p>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const toast = document.getElementById("viewInternshipToast");
    if (toast) {
        setTimeout(() => toast.remove(), 3500);
    }
});
</script>

<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>