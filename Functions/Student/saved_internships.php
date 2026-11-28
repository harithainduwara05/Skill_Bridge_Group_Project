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

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'unsave') {
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Invalid security token.');
    }
    $targetId = filter_var($_POST['internship_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($targetId) {
        try {
            $repo->toggleSaved($email, (int) $targetId);
            $_SESSION['student_internship_flash'] = [
                'text'  => 'Internship removed successfully.',
                'error' => false,
            ];
        } catch (Throwable $e) {
            error_log('Remove saved internship error: ' . $e->getMessage());
            $_SESSION['student_internship_flash'] = [
                'text'  => 'Unable to remove saved internship.',
                'error' => true,
            ];
        }
    }
    header('Location: saved_internships.php', true, 303);
    exit;
}

$savedInternships = $repo->savedInternships($email);
$appliedIds = $repo->appliedIds($email);

$flash = $_SESSION['student_internship_flash'] ?? null;
unset($_SESSION['student_internship_flash']);

function savedEsc(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$pageTitle = 'Saved Internships | SkillBridge';
$cssVer = is_file(__DIR__ . '/../../Assets/CSS/Student/saved_internships.css') ? (string) filemtime(__DIR__ . '/../../Assets/CSS/Student/saved_internships.css') : '1';
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/dashboard.css">'
    . '<link rel="stylesheet" href="../../Assets/CSS/Student/saved_internships.css?v=' . $cssVer . '">'
    . '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">';

include __DIR__ . '/../../Includes/student_sidebar.php';
include __DIR__ . '/../../Includes/dash_header.php';
?>

<main class="content saved-internship-page">
    <section class="saved-header">
        <div>
            <div class="breadcrumb">
                <a href="internships.php">Internships</a>
                <span>&gt;</span>
                <strong>Saved Internships</strong>
            </div>
            <h1>Saved Internships</h1>
            <p>View and manage internships you saved for future applications.</p>
        </div>
        <a href="internships.php" class="btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Explore Internships
        </a>
    </section>

    <?php if ($flash): ?>
        <div class="internship-toast <?= !empty($flash['error']) ? 'error' : '' ?>" id="savedInternshipToast" role="status">
            <span><?= savedEsc((string) $flash['text']) ?></span>
            <button type="button" onclick="this.parentElement.remove()" aria-label="Close">&times;</button>
        </div>
    <?php endif; ?>

    <section class="saved-grid">
        <?php if (empty($savedInternships)): ?>
            <div class="empty-saved-state">
                No saved internships yet. <a href="internships.php">Explore internships</a> to bookmark one.
            </div>
        <?php else: ?>
            <?php foreach ($savedInternships as $internship): ?>
                <?php
                $iid = (int) $internship['id'];
                $companyName = (string) ($internship['company_name'] ?? 'Company');
                $words = preg_split('/\s+/', trim($companyName)) ?: [];
                $initials = strtoupper(mb_substr($words[0] ?? 'C', 0, 1) . mb_substr($words[1] ?? '', 0, 1));
                $badgeLabel = (string) ($internship['work_mode'] ?: ($internship['internship_type'] ?: 'Internship'));
                $locLabel = (string) ($internship['location'] ?: $badgeLabel);
                $isApplied = in_array($iid, $appliedIds, true);
                $isExpired = !empty($internship['is_expired']);
                ?>
                <article class="saved-card">
                    <div class="saved-card-top">
                        <div class="company-logo"><?= savedEsc($initials) ?></div>
                        <span class="status-badge"><?= savedEsc($badgeLabel) ?></span>
                    </div>

                    <h2><?= savedEsc((string) $internship['title']) ?></h2>
                    <h3><?= savedEsc($companyName) ?></h3>

                    <p class="location">
                        <i class="fa-solid fa-location-dot"></i>
                        <?= savedEsc($locLabel) ?>
                    </p>

                    <div class="skill-tags">
                        <?php foreach (array_slice($internship['skills'] ?? [], 0, 4) as $skill): ?>
                            <span><?= savedEsc($skill) ?></span>
                        <?php endforeach; ?>
                        <?php if (empty($internship['skills'])): ?>
                            <span><?= savedEsc((string) ($internship['industry_name'] ?? 'General')) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="saved-info">
                        <div>
                            <small>Duration</small>
                            <strong><?= savedEsc((string) $internship['duration_label']) ?></strong>
                        </div>
                        <div>
                            <small>Deadline</small>
                            <strong><?= savedEsc((string) $internship['formatted_deadline']) ?></strong>
                        </div>
                    </div>

                    <div class="saved-actions">
                        <a href="view_internship.php?id=<?= $iid ?>" class="btn-outline">
                            <i class="fa-solid fa-eye"></i> View Details
                        </a>

                        <?php if ($isApplied): ?>
                            <span class="status-pill is-applied">Applied</span>
                        <?php elseif ($isExpired): ?>
                            <span class="status-pill is-closed">Closed</span>
                        <?php elseif ($canApply): ?>
                            <a href="submit_application.php?id=<?= $iid ?>" class="btn-primary">
                                <i class="fa-solid fa-paper-plane"></i> Apply Now
                            </a>
                        <?php endif; ?>

                        <form method="POST" action="saved_internships.php" class="remove-form">
                            <input type="hidden" name="csrf" value="<?= savedEsc($csrf) ?>">
                            <input type="hidden" name="action" value="unsave">
                            <input type="hidden" name="internship_id" value="<?= $iid ?>">
                            <button type="submit" class="remove-btn">
                                <i class="fa-solid fa-bookmark"></i> Remove
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const toast = document.getElementById("savedInternshipToast");
    if (toast) {
        setTimeout(() => toast.remove(), 3500);
    }
});
</script>

<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>