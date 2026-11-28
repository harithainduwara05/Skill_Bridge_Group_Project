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

if (!canApplyInternship($email)) {
    http_response_code(403);
    exit('Only Year 3 and Year 4 students can submit internship applications.');
}

if (empty($_SESSION['student_internship_csrf'])) {
    $_SESSION['student_internship_csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['student_internship_csrf'];

$id = filter_var($_GET['id'] ?? $_POST['internship_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    http_response_code(400);
    exit('Invalid internship ID.');
}

$repo = new StudentInternshipRepository($conn);
$internship = $repo->findInternship((int) $id);
if (!$internship) {
    http_response_code(404);
    exit('Internship not found.');
}

$errorMsg = null;
$formData = [
    'application_title'   => (string) ($internship['title'] ?? '') . ' Application',
    'cover_note'          => '',
    'relevant_experience' => '',
    'selection_rationale' => '',
];

if ($repo->hasApplied($email, (int) $id)) {
    header('Location: applications.php', true, 303);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Invalid security token.');
    }

    $formData['application_title']   = trim((string) ($_POST['application_title'] ?? ''));
    $formData['cover_note']          = trim((string) ($_POST['cover_note'] ?? ''));
    $formData['relevant_experience'] = trim((string) ($_POST['relevant_experience'] ?? ''));
    $formData['selection_rationale'] = trim((string) ($_POST['selection_rationale'] ?? ''));

    $uploadedPath = null;
    try {
        if (isset($_FILES['resume_file']) && (int) ($_FILES['resume_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['resume_file'];
            if ((int) ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                throw new InvalidArgumentException('Resume upload failed.');
            }
            if ((int) ($file['size'] ?? 0) > 10 * 1024 * 1024) {
                throw new InvalidArgumentException('Resume file must be 10MB or smaller.');
            }
            $ext = strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf', 'doc', 'docx'], true)) {
                throw new InvalidArgumentException('Resume must be a PDF, DOC, or DOCX file.');
            }
            $uploadDir = __DIR__ . '/../../Assets/Uploads/internship_resumes/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                throw new RuntimeException('Unable to create resume upload directory.');
            }
            $storedName = 'resume_' . (int) $id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (!move_uploaded_file((string) $file['tmp_name'], $uploadDir . $storedName)) {
                throw new RuntimeException('Failed to store uploaded resume.');
            }
            $uploadedPath = 'Assets/Uploads/internship_resumes/' . $storedName;
        }

        $repo->submitApplication($email, (int) $id, $formData + ['resume_file' => $uploadedPath]);
        header('Location: applications.php?applied=1', true, 303);
        exit;
    } catch (Throwable $e) {
        if ($uploadedPath && is_file(__DIR__ . '/../../' . $uploadedPath)) {
            @unlink(__DIR__ . '/../../' . $uploadedPath);
        }
        error_log('Submit internship application error: ' . $e->getMessage());
        $errorMsg = $e->getMessage();
    }
}

function appEsc(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$pageTitle = 'Submit Application | SkillBridge';
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/dashboard.css">'
    . '<link rel="stylesheet" href="../../Assets/CSS/Student/submit_application.css">'
    . '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">';

include __DIR__ . '/../../Includes/student_sidebar.php';
include __DIR__ . '/../../Includes/dash_header.php';
?>

<main class="application-page">
    <div class="application-header">
        <div class="breadcrumb">
            <a href="internships.php">Internships</a>
            <span>&gt;</span>
            <a href="view_internship.php?id=<?= (int) $id ?>">View Internship</a>
            <span>&gt;</span>
            <strong>Apply Now</strong>
        </div>

        <h1>Internship Application</h1>
        <p>Complete your application and submit your professional profile to <?= appEsc((string) ($internship['company_name'] ?? 'Company')) ?>.</p>
    </div>

    <?php if ($errorMsg): ?>
        <div style="margin-bottom: 20px; padding: 14px 18px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; color: #991b1b;">
            <?= appEsc($errorMsg) ?>
        </div>
    <?php endif; ?>

    <form class="application-layout" action="submit_application.php?id=<?= (int) $id ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= appEsc($csrf) ?>">
        <input type="hidden" name="internship_id" value="<?= (int) $id ?>">

        <div class="application-left">
            <section class="application-card">
                <h2><i class="fa-solid fa-user"></i> Personal Introduction</h2>

                <label for="application_title">Application Title *</label>
                <input id="application_title" name="application_title" type="text" value="<?= appEsc($formData['application_title']) ?>" required>

                <label for="cover_note">Introduction / Cover Note *</label>
                <textarea id="cover_note" name="cover_note" rows="5" placeholder="Introduce yourself and explain why you are interested in this internship." required><?= appEsc($formData['cover_note']) ?></textarea>
            </section>

            <section class="application-card">
                <h2><i class="fa-solid fa-briefcase"></i> Professional Information</h2>

                <label for="relevant_experience">Relevant Experience *</label>
                <textarea id="relevant_experience" name="relevant_experience" rows="5" placeholder="Describe your academic projects, coursework, or prior experience." required><?= appEsc($formData['relevant_experience']) ?></textarea>

                <label for="selection_rationale">Why should we select you? *</label>
                <textarea id="selection_rationale" name="selection_rationale" rows="5" placeholder="Explain your strengths and what you will bring to the team." required><?= appEsc($formData['selection_rationale']) ?></textarea>
            </section>

            <section class="application-card">
                <h2><i class="fa-solid fa-code"></i> Required Skills</h2>
                <div class="skill-tags">
                    <?php foreach ($internship['skills'] ?? [] as $skill): ?>
                        <span><?= appEsc($skill) ?></span>
                    <?php endforeach; ?>
                    <?php if (empty($internship['skills'])): ?>
                        <span><?= appEsc((string) ($internship['industry_name'] ?? 'General')) ?></span>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <div class="application-right">
            <section class="upload-card">
                <h2><i class="fa-solid fa-paperclip"></i> Resume / CV</h2>
                <p>Attach your resume (PDF, DOC, or DOCX, max 10MB).</p>
                <div class="upload-box">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <strong>Choose Resume File</strong>
                    <small>PDF, DOC, DOCX up to 10MB</small>
                    <input type="file" name="resume_file" accept=".pdf,.doc,.docx,application/pdf">
                </div>
            </section>

            <section class="summary-card">
                <h2>Application Summary</h2>
                <div class="summary-item">
                    <span>Company</span>
                    <strong><?= appEsc((string) ($internship['company_name'] ?? 'Company')) ?></strong>
                </div>
                <div class="summary-item">
                    <span>Position</span>
                    <strong><?= appEsc((string) ($internship['title'] ?? 'Internship')) ?></strong>
                </div>
                <div class="summary-item">
                    <span>Deadline</span>
                    <strong><?= appEsc((string) ($internship['formatted_deadline'] ?? 'Open')) ?></strong>
                </div>

                <button type="submit" class="submit-btn">
                    Submit Application <i class="fa-solid fa-paper-plane"></i>
                </button>
            </section>
        </div>
    </form>
</main>

<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>