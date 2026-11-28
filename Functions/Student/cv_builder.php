<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../../Config/db.php';
require_once __DIR__ . '/../../Session/Session.php';
require_once __DIR__ . '/../../Backend/Student/StudentBackend.php';

require_role('student');

$user = current_user();
if (!$user) {
    http_response_code(401);
    exit('Session expired.');
}

$email = trim((string) ($user['Email'] ?? $user['email'] ?? ''));
if ($email === '') {
    http_response_code(403);
    exit('Student email not found.');
}

function cvHasTable(mysqli $conn, string $table): bool
{
    $stmt = $conn->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
    );
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $table);
    $stmt->execute();
    $exists = (int) $stmt->get_result()->fetch_row()[0] > 0;
    $stmt->close();
    return $exists;
}

function cvHasColumn(mysqli $conn, string $table, string $column): bool
{
    $stmt = $conn->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $exists = (int) $stmt->get_result()->fetch_row()[0] > 0;
    $stmt->close();
    return $exists;
}

function cvEsc(?string $val): string
{
    return htmlspecialchars((string) ($val ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Handle AJAX JSON save to student_resumes & student_resume_items
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $rawBody = file_get_contents('php://input') ?: '';
    $payload = json_decode($rawBody, true);
    if (!is_array($payload)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Invalid request payload.']);
        exit;
    }

    $csrfToken = (string) ($payload['csrf'] ?? '');
    if (empty($_SESSION['student_cv_csrf']) || !hash_equals($_SESSION['student_cv_csrf'], $csrfToken)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'message' => 'Invalid security token. Please refresh and try again.']);
        exit;
    }

    $fullName = trim((string) ($payload['name'] ?? ''));
    $title = trim((string) ($payload['title'] ?? ''));
    $github = trim((string) ($payload['github'] ?? ''));
    $linkedin = trim((string) ($payload['linkedin'] ?? ''));
    $website = trim((string) ($payload['website'] ?? ''));
    $summary = trim((string) ($payload['summary'] ?? ''));
    $resumeTitle = trim((string) ($payload['resume_title'] ?? ''));
    $templateRaw = strtolower(trim((string) ($payload['template'] ?? 'modern')));
    $templateMap = [
        'modern'  => 'modern',
        'classic' => 'classic',
        'minimal' => 'minimal',
    ];
    $dbTemplate = $templateMap[$templateRaw] ?? 'modern';

    $selectedSkillIds = array_values(array_unique(array_filter(array_map(
        static fn($v) => (int) $v,
        is_array($payload['skill_ids'] ?? null) ? $payload['skill_ids'] : []
    ), static fn($id) => $id > 0)));

    $selectedProjectIds = array_values(array_unique(array_filter(array_map(
        static fn($v) => (int) $v,
        is_array($payload['project_ids'] ?? null) ? $payload['project_ids'] : []
    ), static fn($id) => $id > 0)));

    try {
        $conn->begin_transaction();

        $sets = [];
        $types = '';
        $params = [];

        if ($fullName !== '') {
            $sets[] = 'Name = ?';
            $types .= 's';
            $params[] = $fullName;
        }
        if (cvHasColumn($conn, 'student', 'bio')) {
            $sets[] = 'bio = ?';
            $types .= 's';
            $params[] = $summary;
        }
        if (cvHasColumn($conn, 'student', 'github')) {
            $sets[] = 'github = ?';
            $types .= 's';
            $params[] = $github;
        }
        if (cvHasColumn($conn, 'student', 'linkedin')) {
            $sets[] = 'linkedin = ?';
            $types .= 's';
            $params[] = $linkedin;
        }
        if (cvHasColumn($conn, 'student', 'website')) {
            $sets[] = 'website = ?';
            $types .= 's';
            $params[] = $website;
        }

        if ($sets) {
            $sql = 'UPDATE student SET ' . implode(', ', $sets) . ' WHERE Email = ?';
            $types .= 's';
            $params[] = $email;
            $uStmt = $conn->prepare($sql);
            if ($uStmt) {
                $uStmt->bind_param($types, ...$params);
                $uStmt->execute();
                $uStmt->close();
            }
        }

        if ($resumeTitle === '') {
            $resumeTitle = $title !== '' ? ($title . ' CV') : ($fullName !== '' ? ($fullName . ' CV') : 'Professional CV');
        }

        $resumeId = 0;
        if (cvHasTable($conn, 'student_resumes')) {
            $rSel = $conn->prepare(
                'SELECT resume_id FROM student_resumes WHERE LOWER(TRIM(student_email)) = LOWER(TRIM(?)) ORDER BY is_primary DESC, resume_id DESC LIMIT 1'
            );
            if ($rSel) {
                $rSel->bind_param('s', $email);
                $rSel->execute();
                $existingResume = $rSel->get_result()->fetch_assoc();
                $rSel->close();
                if ($existingResume) {
                    $resumeId = (int) $existingResume['resume_id'];
                }
            }

            if ($resumeId > 0) {
                $rUpd = $conn->prepare(
                    'UPDATE student_resumes SET template_type = ?, resume_title = ?, custom_summary = ?, is_primary = 1 WHERE resume_id = ? AND LOWER(TRIM(student_email)) = LOWER(TRIM(?))'
                );
                if ($rUpd) {
                    $rUpd->bind_param('sssis', $dbTemplate, $resumeTitle, $summary, $resumeId, $email);
                    $rUpd->execute();
                    $rUpd->close();
                }
            } else {
                $rIns = $conn->prepare(
                    'INSERT INTO student_resumes (student_email, template_type, resume_title, custom_summary, is_primary) VALUES (?, ?, ?, ?, 1)'
                );
                if ($rIns) {
                    $rIns->bind_param('ssss', $email, $dbTemplate, $resumeTitle, $summary);
                    $rIns->execute();
                    $resumeId = (int) $conn->insert_id;
                    $rIns->close();
                }
            }

            if ($resumeId > 0 && cvHasTable($conn, 'student_resume_items')) {
                $delItems = $conn->prepare('DELETE FROM student_resume_items WHERE resume_id = ?');
                if ($delItems) {
                    $delItems->bind_param('i', $resumeId);
                    $delItems->execute();
                    $delItems->close();
                }

                $insItem = $conn->prepare(
                    'INSERT INTO student_resume_items (resume_id, item_type, item_reference_id) VALUES (?, ?, ?)'
                );
                if ($insItem) {
                    foreach ($selectedSkillIds as $sid) {
                        $typeSkill = 'skill';
                        $insItem->bind_param('isi', $resumeId, $typeSkill, $sid);
                        $insItem->execute();
                    }
                    foreach ($selectedProjectIds as $pid) {
                        $typeProj = 'portfolio_project';
                        $insItem->bind_param('isi', $resumeId, $typeProj, $pid);
                        $insItem->execute();
                    }
                    $insItem->close();
                }
            }
        }

        $conn->commit();
        echo json_encode(['ok' => true, 'message' => 'CV saved successfully.']);
    } catch (Throwable $e) {
        $conn->rollback();
        error_log('CV Builder save error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Unable to save CV right now.']);
    }
    exit;
}

if (empty($_SESSION['student_cv_csrf'])) {
    $_SESSION['student_cv_csrf'] = bin2hex(random_bytes(32));
}
$cvCsrf = $_SESSION['student_cv_csrf'];

// Load student profile
$student = [
    'Name'          => '',
    'Email'         => $email,
    'profile_image' => 'default.png',
    'bio'           => '',
    'github'        => '',
    'linkedin'      => '',
    'website'       => '',
    'University'    => '',
    'degree'        => '',
    'year'          => '',
];

$stmt = $conn->prepare(
    'SELECT Email, Name, profile_image, bio, github, linkedin, website, University, degree, year
     FROM student
     WHERE LOWER(TRIM(Email)) = LOWER(TRIM(?))
     LIMIT 1'
);
if ($stmt) {
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $student = array_merge($student, $row);
    }
    $stmt->close();
}

// Load saved CV resume & items if available
$savedResume = [
    'resume_id'      => 0,
    'template_type'  => 'modern',
    'resume_title'   => (($student['degree'] ?: 'Student') . ' CV'),
    'custom_summary' => (string) ($student['bio'] ?? ''),
];
$savedSkillIds = [];
$savedProjectIds = [];
$hasSavedResumeRow = false;

if (cvHasTable($conn, 'student_resumes')) {
    $rStmt = $conn->prepare(
        'SELECT resume_id, template_type, resume_title, custom_summary
         FROM student_resumes
         WHERE LOWER(TRIM(student_email)) = LOWER(TRIM(?))
         ORDER BY is_primary DESC, resume_id DESC
         LIMIT 1'
    );
    if ($rStmt) {
        $rStmt->bind_param('s', $email);
        $rStmt->execute();
        if ($rRow = $rStmt->get_result()->fetch_assoc()) {
            $savedResume = array_merge($savedResume, $rRow);
            $hasSavedResumeRow = true;
        }
        $rStmt->close();
    }

    if ((int) $savedResume['resume_id'] > 0 && cvHasTable($conn, 'student_resume_items')) {
        $rid = (int) $savedResume['resume_id'];
        $iStmt = $conn->prepare('SELECT item_type, item_reference_id FROM student_resume_items WHERE resume_id = ?');
        if ($iStmt) {
            $iStmt->bind_param('i', $rid);
            $iStmt->execute();
            $iRes = $iStmt->get_result();
            while ($ir = $iRes->fetch_assoc()) {
                $refId = (int) ($ir['item_reference_id'] ?? 0);
                if ($refId <= 0) {
                    continue;
                }
                if (($ir['item_type'] ?? '') === 'skill') {
                    $savedSkillIds[] = $refId;
                } elseif (in_array($ir['item_type'] ?? '', ['portfolio_project', 'platform_project'], true)) {
                    $savedProjectIds[] = $refId;
                }
            }
            $iStmt->close();
        }
    }
}

// Load student skills from student_skills + master_skills
$skills = [];
if (cvHasTable($conn, 'student_skills') && cvHasTable($conn, 'master_skills')) {
    $catJoin = cvHasTable($conn, 'skill_categories')
        ? 'LEFT JOIN skill_categories sc ON sc.category_id = ms.category_id'
        : '';
    $catCol = cvHasTable($conn, 'skill_categories') ? "COALESCE(sc.category_name, 'General')" : "'General'";
    $skSql = "SELECT
                ss.skill_id,
                ms.skill_name,
                {$catCol} AS category_name,
                ss.initial_experience,
                ss.created_at
              FROM student_skills ss
              INNER JOIN master_skills ms ON ms.skill_id = ss.skill_id
              {$catJoin}
              WHERE LOWER(TRIM(ss.Email)) = LOWER(TRIM(?))
              ORDER BY ss.initial_experience DESC, ms.skill_name ASC";
    $skStmt = $conn->prepare($skSql);
    if ($skStmt) {
        $skStmt->bind_param('s', $email);
        $skStmt->execute();
        $skRes = $skStmt->get_result();
        while ($sRow = $skRes->fetch_assoc()) {
            $yrs = max(1, (int) ($sRow['initial_experience'] ?? 1));
            $level = $yrs <= 1 ? 'Beginner' : ($yrs <= 3 ? 'Intermediate' : ($yrs <= 5 ? 'Advanced' : 'Expert'));
            $pct = $yrs <= 1 ? 40 : ($yrs <= 3 ? 60 : ($yrs <= 5 ? 80 : 95));
            $skills[] = [
                'id'         => (int) $sRow['skill_id'],
                'skill_name' => (string) $sRow['skill_name'],
                'category'   => (string) $sRow['category_name'],
                'level'      => $level,
                'percentage' => $pct,
            ];
        }
        $skStmt->close();
    }
}

// Load student portfolio projects
$projects = [];
if (cvHasTable($conn, 'student_portfolio_projects')) {
    $pStmt = $conn->prepare(
        'SELECT portfolio_project_id AS id, title, description, repository_url, project_status, start_date, end_date
         FROM student_portfolio_projects
         WHERE LOWER(TRIM(student_email)) = LOWER(TRIM(?))
         ORDER BY CASE WHEN LOWER(COALESCE(project_status, "")) = "completed" THEN 0 ELSE 1 END, portfolio_project_id DESC'
    );
    if ($pStmt) {
        $pStmt->bind_param('s', $email);
        $pStmt->execute();
        $pRes = $pStmt->get_result();
        while ($pRow = $pRes->fetch_assoc()) {
            $statusVal = trim((string) ($pRow['project_status'] ?? 'Currently Working'));
            $isCompletedProj = strcasecmp($statusVal, 'Completed') === 0;
            $periodText = '';
            if (!empty($pRow['start_date']) || !empty($pRow['end_date'])) {
                $sFmt = !empty($pRow['start_date']) ? date('M Y', strtotime((string) $pRow['start_date'])) : 'Start';
                $eFmt = !empty($pRow['end_date']) ? date('M Y', strtotime((string) $pRow['end_date'])) : ($isCompletedProj ? 'Completed' : 'Present');
                $periodText = $sFmt . ' - ' . $eFmt;
            }
            $projects[] = [
                'id'           => (int) $pRow['id'],
                'title'        => (string) ($pRow['title'] ?? ''),
                'status'       => $isCompletedProj ? 'Completed' : ($statusVal !== '' ? $statusVal : 'Currently Working'),
                'is_completed' => $isCompletedProj,
                'description'  => (string) ($pRow['description'] ?? ''),
                'repo_url'     => (string) ($pRow['repository_url'] ?? ''),
                'period'       => $periodText,
            ];
        }
        $pStmt->close();
    }
}

if (empty($projects) && cvHasTable($conn, 'portfolio')) {
    $lpStmt = $conn->prepare(
        'SELECT portfolio_id AS id, title, description, project_link
         FROM portfolio
         WHERE LOWER(TRIM(Email)) = LOWER(TRIM(?))
         ORDER BY portfolio_id DESC'
    );
    if ($lpStmt) {
        $lpStmt->bind_param('s', $email);
        $lpStmt->execute();
        $lpRes = $lpStmt->get_result();
        while ($pRow = $lpRes->fetch_assoc()) {
            $projects[] = [
                'id'           => (int) $pRow['id'],
                'title'        => (string) ($pRow['title'] ?? ''),
                'status'       => 'Completed',
                'is_completed' => true,
                'description'  => (string) ($pRow['description'] ?? ''),
                'repo_url'     => (string) ($pRow['project_link'] ?? ''),
                'period'       => '',
            ];
        }
        $lpStmt->close();
    }
}

// Load student achievements / certificates for the sidebar ACHIEVEMENTS block
$achievements = [];
if (cvHasTable($conn, 'certificates')) {
    $cStmt = $conn->prepare(
        'SELECT certificate_name, issuer, issue_date
         FROM certificates
         WHERE LOWER(TRIM(Email)) = LOWER(TRIM(?))
         ORDER BY issue_date DESC, certificate_id DESC
         LIMIT 3'
    );
    if ($cStmt) {
        $cStmt->bind_param('s', $email);
        $cStmt->execute();
        $cRes = $cStmt->get_result();
        while ($cRow = $cRes->fetch_assoc()) {
            $achievements[] = [
                'title'  => (string) ($cRow['certificate_name'] ?? 'Achievement'),
                'detail' => trim((string) ($cRow['issuer'] ?? '')) . (!empty($cRow['issue_date']) ? ' • ' . date('Y', strtotime((string) $cRow['issue_date'])) : ''),
            ];
        }
        $cStmt->close();
    }
}

$completedProjects = array_values(array_filter($projects, static fn($p) => !empty($p['is_completed'])));

if (!$hasSavedResumeRow) {
    $savedSkillIds = array_map(static fn($s) => (int) $s['id'], array_slice($skills, 0, 6));
    $savedProjectIds = array_map(static fn($p) => (int) $p['id'], $completedProjects);
}

$activeTemplate = strtolower((string) ($savedResume['template_type'] ?? 'modern'));
if (!in_array($activeTemplate, ['modern', 'classic', 'minimal'], true)) {
    $activeTemplate = 'modern';
}

$summaryText = trim((string) ($savedResume['custom_summary'] ?? '')) !== ''
    ? (string) $savedResume['custom_summary']
    : (string) ($student['bio'] ?? '');

$avatarFile = basename((string) ($student['profile_image'] ?? 'default.png'));
if ($avatarFile === '' || $avatarFile === 'default.png') {
    $avatarSrc = '../../Assets/Images/Profile/default.png';
} elseif (is_file(__DIR__ . '/../../Assets/Images/Student/Profile/' . $avatarFile)) {
    $avatarSrc = '../../Assets/Images/Student/Profile/' . rawurlencode($avatarFile);
} elseif (is_file(__DIR__ . '/../../Assets/Images/Student/' . $avatarFile)) {
    $avatarSrc = '../../Assets/Images/Student/' . rawurlencode($avatarFile);
} else {
    $avatarSrc = '../../Assets/Images/Profile/default.png';
}

$pageTitle = 'CV Builder | SkillBridge';
$cssVer = is_file(__DIR__ . '/../../Assets/CSS/Student/cv_builder.css')
    ? (string) filemtime(__DIR__ . '/../../Assets/CSS/Student/cv_builder.css')
    : '1';

$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/dashboard.css">'
    . '<link rel="stylesheet" href="../../Assets/CSS/Student/cv_builder.css?v=' . $cssVer . '">'
    . '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">';

include __DIR__ . '/../../Includes/student_sidebar.php';
include __DIR__ . '/../../Includes/dash_header.php';
?>

<main class="content cv-builder-page" data-csrf="<?= cvEsc($cvCsrf) ?>">
    <div class="builder-ui">
        <section class="cv-builder-header">
            <div>
                <h1>Create Your Professional CV</h1>
                <p>Choose a standard CV template, drag in completed portfolio projects, and export a clean single-page PDF.</p>
            </div>

            <div class="cv-header-actions">
                <button type="button" class="save-cv-btn" id="saveCvBtn">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
                <button type="button" class="export-cv-btn" id="exportCvBtn">
                    <i class="fa-solid fa-file-pdf"></i> Export CV
                </button>
            </div>
        </section>

        <!-- STANDARDIZED CV TEMPLATES -->
        <section class="template-section">
            <div class="section-header">
                <h2>Choose CV Template</h2>
                <p>Select a standard resume structure for your live preview and export.</p>
            </div>

            <div class="template-grid" id="templateGrid">
                <div class="template-card <?= $activeTemplate === 'modern' ? 'active' : '' ?>" data-template="modern">
                    <div class="cv-tpl-box cv-tpl-box-charcoal">
                        <div class="cv-tpl-left">
                            <div class="cv-tpl-avatar"></div>
                            <div class="cv-tpl-pill"></div>
                            <div class="cv-tpl-pill short"></div>
                        </div>
                        <div class="cv-tpl-right">
                            <div class="cv-tpl-bar dark"></div>
                            <div class="cv-tpl-bar muted"></div>
                            <div class="cv-tpl-panel"></div>
                            <div class="cv-tpl-bar"></div>
                        </div>
                    </div>
                    <h3>Charcoal Sidebar</h3>
                    <p>Dark sidebar with portrait, achievements, amber skill tags, and timeline projects.</p>
                </div>

                <div class="template-card <?= $activeTemplate === 'classic' ? 'active' : '' ?>" data-template="classic">
                    <div class="cv-tpl-box cv-tpl-box-classic">
                        <div class="cv-tpl-banner">
                            <div class="cv-tpl-bar light"></div>
                            <div class="cv-tpl-bar gold"></div>
                        </div>
                        <div class="cv-tpl-cols">
                            <div class="cv-tpl-col">
                                <div class="cv-tpl-bar"></div>
                                <div class="cv-tpl-bar short"></div>
                            </div>
                            <div class="cv-tpl-col wide">
                                <div class="cv-tpl-bar"></div>
                                <div class="cv-tpl-panel"></div>
                            </div>
                        </div>
                    </div>
                    <h3>Executive Classic</h3>
                    <p>Traditional header banner with structured sections for formal applications.</p>
                </div>

                <div class="template-card <?= $activeTemplate === 'minimal' ? 'active' : '' ?>" data-template="minimal">
                    <div class="cv-tpl-box cv-tpl-box-minimal">
                        <div class="cv-tpl-bar dark"></div>
                        <div class="cv-tpl-divider"></div>
                        <div class="cv-tpl-bar"></div>
                        <div class="cv-tpl-bar short"></div>
                        <div class="cv-tpl-divider"></div>
                        <div class="cv-tpl-bar"></div>
                    </div>
                    <h3>Clean Minimal</h3>
                    <p>Editorial single-column CV emphasizing clarity, whitespace, and projects.</p>
                </div>
            </div>
        </section>

        <section class="builder-layout">
            <div class="editor-panel">
                <section class="builder-card">
                    <div class="card-title">
                        <i class="fa-solid fa-user"></i>
                        <h2>Personal &amp; Profile Details</h2>
                    </div>

                    <div class="profile-upload">
                        <img id="editorPhotoPreview" src="<?= cvEsc($avatarSrc) ?>" alt="Profile photo">
                        <div>
                            <input type="file" id="profileUpload" accept="image/*" hidden>
                            <button type="button" id="changePhotoBtn">Change Photo</button>
                            <p>Preview a custom photo for export</p>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div>
                            <label for="resumeTitle">CV Title</label>
                            <input type="text" id="resumeTitle" value="<?= cvEsc((string) ($savedResume['resume_title'] ?? '')) ?>" placeholder="e.g. Software Engineer CV">
                        </div>

                        <div>
                            <label for="fullName">Full Name</label>
                            <input type="text" id="fullName" value="<?= cvEsc((string) ($student['Name'] ?? '')) ?>">
                        </div>

                        <div>
                            <label for="professionalTitle">Job / Role Title</label>
                            <input type="text" id="professionalTitle" value="<?= cvEsc((string) ($student['degree'] ?? '')) ?>">
                        </div>

                        <div>
                            <label for="emailField">Email</label>
                            <input type="email" id="emailField" readonly value="<?= cvEsc((string) ($student['Email'] ?? $email)) ?>">
                        </div>

                        <div>
                            <label for="phoneField">Phone Number</label>
                            <input type="text" id="phoneField" placeholder="Enter phone number">
                        </div>

                        <div>
                            <label for="locationField">City / Location</label>
                            <input type="text" id="locationField" placeholder="Enter city or location">
                        </div>

                        <div>
                            <label for="universityField">Institution / University</label>
                            <input type="text" id="universityField" readonly value="<?= cvEsc((string) ($student['University'] ?? '')) ?>">
                        </div>

                        <div>
                            <label for="linkedinField">LinkedIn</label>
                            <input type="text" id="linkedinField" value="<?= cvEsc((string) ($student['linkedin'] ?? '')) ?>">
                        </div>

                        <div>
                            <label for="githubField">GitHub</label>
                            <input type="text" id="githubField" value="<?= cvEsc((string) ($student['github'] ?? '')) ?>">
                        </div>

                        <div>
                            <label for="websiteField">Website / Portfolio</label>
                            <input type="text" id="websiteField" value="<?= cvEsc((string) ($student['website'] ?? '')) ?>">
                        </div>
                    </div>

                    <div style="margin-top: 16px;">
                        <label for="summaryInput">Profile Summary</label>
                        <textarea id="summaryInput" rows="4" placeholder="Write a concise summary of who you are, your strengths, and what you bring..."><?= cvEsc($summaryText) ?></textarea>
                    </div>
                </section>

                <!-- SKILLS DRAG & DROP -->
                <section class="builder-card">
                    <div class="card-title">
                        <i class="fa-solid fa-code"></i>
                        <h2>Skills</h2>
                    </div>
                    <p class="helper-text">Drag skills into Selected Skills or click a skill to add/remove it.</p>

                    <div class="skills-builder">
                        <div class="available-skills">
                            <h3>Available Skills</h3>
                            <div class="skill-list" id="availableSkillsList">
                                <?php foreach ($skills as $sk): ?>
                                    <?php $sid = (int) $sk['id']; ?>
                                    <div class="drag-skill"
                                         draggable="true"
                                         data-skill-id="<?= $sid ?>"
                                         data-skill-name="<?= cvEsc((string) $sk['skill_name']) ?>">
                                        <?= cvEsc((string) $sk['skill_name']) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="selected-skills">
                            <h3>Selected Skills</h3>
                            <div class="skill-drop-zone" id="skillDropZone">
                                <?php foreach ($skills as $sk): ?>
                                    <?php $sid = (int) $sk['id']; ?>
                                    <?php if (in_array($sid, $savedSkillIds, true)): ?>
                                        <span class="selected-skill"
                                              data-skill-id="<?= $sid ?>"
                                              data-skill-name="<?= cvEsc((string) $sk['skill_name']) ?>">
                                            <span><?= cvEsc((string) $sk['skill_name']) ?></span>
                                            <button type="button" class="remove-skill-btn" aria-label="Remove skill">&times;</button>
                                        </span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- COMPLETED PORTFOLIO PROJECTS DRAG & DROP -->
                <section class="builder-card">
                    <div class="card-title">
                        <i class="fa-solid fa-folder-open"></i>
                        <h2>Completed Portfolio Projects</h2>
                    </div>
                    <p class="helper-text">Drag your <strong>Completed</strong> portfolio projects into the CV Projects drop zone below.</p>

                    <div class="cv-projects-builder">
                        <div class="available-projects-box">
                            <h3>Completed Projects</h3>
                            <div class="cv-project-source-list" id="completedProjectsSource">
                                <?php if (empty($completedProjects)): ?>
                                    <p class="empty-source-msg">No completed portfolio projects found yet. Mark a project as <strong>Completed</strong> in your Portfolio to drag it into your CV.</p>
                                <?php else: ?>
                                    <?php foreach ($completedProjects as $proj): ?>
                                        <?php $pid = (int) $proj['id']; ?>
                                        <div class="draggable-project-card"
                                             draggable="true"
                                             data-project-id="<?= $pid ?>"
                                             data-project-title="<?= cvEsc((string) $proj['title']) ?>"
                                             data-project-status="<?= cvEsc((string) $proj['status']) ?>"
                                             data-project-period="<?= cvEsc((string) $proj['period']) ?>"
                                             data-project-desc="<?= cvEsc((string) $proj['description']) ?>"
                                             data-project-repo="<?= cvEsc((string) $proj['repo_url']) ?>">
                                            <div class="drag-project-top">
                                                <span class="drag-grip"><i class="fa-solid fa-grip-vertical"></i></span>
                                                <span class="completed-pill">Completed</span>
                                            </div>
                                            <h4><?= cvEsc((string) $proj['title']) ?></h4>
                                            <?php if (!empty($proj['description'])): ?>
                                                <p><?= cvEsc(mb_strimwidth((string) $proj['description'], 0, 110, '...')) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="selected-projects-box">
                            <h3>Included in CV (Drop Completed Projects Here)</h3>
                            <div class="project-drop-zone" id="projectDropZone">
                                <?php foreach ($projects as $proj): ?>
                                    <?php
                                    $pid = (int) $proj['id'];
                                    if (in_array($pid, $savedProjectIds, true)):
                                    ?>
                                        <div class="selected-project-item"
                                             data-project-id="<?= $pid ?>"
                                             data-project-title="<?= cvEsc((string) $proj['title']) ?>"
                                             data-project-status="<?= cvEsc((string) $proj['status']) ?>"
                                             data-project-period="<?= cvEsc((string) $proj['period']) ?>"
                                             data-project-desc="<?= cvEsc((string) $proj['description']) ?>"
                                             data-project-repo="<?= cvEsc((string) $proj['repo_url']) ?>">
                                            <div class="selected-project-info">
                                                <strong><?= cvEsc((string) $proj['title']) ?></strong>
                                                <?php if (!empty($proj['description'])): ?>
                                                    <small><?= cvEsc(mb_strimwidth((string) $proj['description'], 0, 95, '...')) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <button type="button" class="remove-project-btn" aria-label="Remove project">&times;</button>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- LIVE CV PREVIEW -->
            <div class="preview-panel">
                <div class="preview-header">
                    <h3>Live CV Preview</h3>
                    <div class="zoom-controls">
                        <button type="button" id="zoomOutBtn">-</button>
                        <span id="zoomLevel">100%</span>
                        <button type="button" id="zoomInBtn">+</button>
                    </div>
                </div>

                <div class="cv-preview-wrapper">
                    <div id="cvPreview" class="cv-document tpl-<?= cvEsc($activeTemplate) ?>">
                        <aside class="cv-sidebar">
                            <div class="cv-photo-frame">
                                <img id="previewPhoto" src="<?= cvEsc($avatarSrc) ?>" alt="Student photo">
                            </div>

                            <div class="sidebar-section">
                                <h4>ACHIEVEMENTS</h4>
                                <?php if (!empty($achievements)): ?>
                                    <?php foreach ($achievements as $ach): ?>
                                        <div class="achievement-entry">
                                            <strong><?= cvEsc((string) $ach['title']) ?></strong>
                                            <?php if (!empty($ach['detail'])): ?>
                                                <p><?= cvEsc((string) $ach['detail']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="achievement-entry">
                                        <strong>Completed Portfolio Milestones</strong>
                                        <p>Delivered verified academic and team software projects.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="sidebar-section">
                                <h4>SKILLS</h4>
                                <div id="previewSkills" class="sidebar-skill-pills">
                                    <?php foreach ($skills as $sk): ?>
                                        <?php if (in_array((int) $sk['id'], $savedSkillIds, true)): ?>
                                            <span class="cv-skill-tag" data-preview-skill-id="<?= (int) $sk['id'] ?>"><?= cvEsc((string) $sk['skill_name']) ?></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="sidebar-section sidebar-contact">
                                <h4>CONTACT</h4>
                                <p id="previewLocationRow" style="display:none;"><i class="fa-solid fa-house"></i> <span id="previewLocation"></span></p>
                                <p id="previewPhoneRow" style="display:none;"><i class="fa-solid fa-phone"></i> <span id="previewPhone"></span></p>
                                <p><i class="fa-regular fa-envelope"></i> <span id="previewEmail"><?= cvEsc((string) ($student['Email'] ?? $email)) ?></span></p>
                                <p id="previewLinkedinRow" style="<?= empty($student['linkedin']) ? 'display:none;' : '' ?>"><i class="fa-brands fa-linkedin"></i> <span id="previewLinkedin"><?= cvEsc((string) ($student['linkedin'] ?? '')) ?></span></p>
                                <p id="previewGithubRow" style="<?= empty($student['github']) ? 'display:none;' : '' ?>"><i class="fa-brands fa-github"></i> <span id="previewGithub"><?= cvEsc((string) ($student['github'] ?? '')) ?></span></p>
                            </div>
                        </aside>

                        <div class="cv-content">
                            <header class="cv-main-header">
                                <h1 id="previewName"><?= cvEsc((string) ($student['Name'] ?? 'Full name')) ?></h1>
                                <p id="previewTitle" class="cv-role-subtitle"><?= cvEsc((string) ($student['degree'] ?? 'Your job title')) ?></p>
                                <div class="cv-inline-meta">
                                    <span id="previewInlineEmail"><?= cvEsc((string) ($student['Email'] ?? $email)) ?></span>
                                    <span id="previewInlinePhone"></span>
                                    <span id="previewInlineLocation"></span>
                                </div>
                            </header>

                            <section class="cv-section-block">
                                <h2>PROFILE</h2>
                                <p id="previewSummary"><?= nl2br(cvEsc($summaryText)) ?></p>
                            </section>

                            <section class="cv-section-block">
                                <h2>COMPLETED PROJECTS</h2>
                                <div id="previewProjects" class="timeline-list">
                                    <?php foreach ($projects as $proj): ?>
                                        <?php if (in_array((int) $proj['id'], $savedProjectIds, true)): ?>
                                            <div class="timeline-item" data-preview-project-id="<?= (int) $proj['id'] ?>">
                                                <div class="timeline-marker"></div>
                                                <div class="timeline-body">
                                                    <h3><?= cvEsc((string) $proj['title']) ?></h3>
                                                    <div class="timeline-sub">
                                                        <span><?= cvEsc((string) $proj['status']) ?></span>
                                                        <?php if (!empty($proj['period'])): ?>
                                                            <span>| <?= cvEsc((string) $proj['period']) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (!empty($proj['description'])): ?>
                                                        <p><?= cvEsc((string) $proj['description']) ?></p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($proj['repo_url'])): ?>
                                                        <div class="timeline-tags">
                                                            <span class="cv-project-chip"><?= cvEsc((string) $proj['repo_url']) ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </section>

                            <section class="cv-section-block cv-inline-skills-block">
                                <h2>SKILLS</h2>
                                <div id="previewInlineSkills" class="sidebar-skill-pills">
                                    <?php foreach ($skills as $sk): ?>
                                        <?php if (in_array((int) $sk['id'], $savedSkillIds, true)): ?>
                                            <span class="cv-skill-tag"><?= cvEsc((string) $sk['skill_name']) ?></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </section>

                            <section class="cv-section-block">
                                <h2>EDUCATION</h2>
                                <div class="timeline-list">
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-body">
                                            <h3 id="previewDegree"><?= cvEsc((string) ($student['degree'] ?? 'Degree or Course')) ?></h3>
                                            <div class="timeline-sub">
                                                <span id="previewUniversity"><?= cvEsc((string) ($student['University'] ?? '')) ?></span>
                                                <?php if (!empty($student['year'])): ?>
                                                    <span id="previewYear">| Year <?= cvEsc((string) $student['year']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="timeline-tags">
                                                <span class="cv-project-chip" id="previewWebsite"><?= cvEsc((string) ($student['website'] ?? 'Academic Profile')) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- ISOLATED SINGLE-PAGE PRINT TARGET -->
    <div id="cvPrintSheet" class="cv-print-sheet" aria-hidden="true"></div>

    <div id="saveToast" class="save-toast" role="status">
        <span id="saveToastText">CV saved successfully.</span>
        <button type="button" id="closeSaveToastBtn" aria-label="Close">&times;</button>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const pageEl = document.querySelector(".cv-builder-page");
    const csrfToken = pageEl ? (pageEl.getAttribute("data-csrf") || "") : "";
    const cvPreview = document.getElementById("cvPreview");
    const cvPrintSheet = document.getElementById("cvPrintSheet");

    function bindTextMirror(inputId, targetIds) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const targets = (Array.isArray(targetIds) ? targetIds : [targetIds])
            .map(function (id) { return document.getElementById(id); })
            .filter(Boolean);
        if (!targets.length) return;
        input.addEventListener("input", function () {
            const val = this.value;
            targets.forEach(function (target) {
                target.textContent = val;
            });
        });
    }

    bindTextMirror("fullName", ["previewName"]);
    bindTextMirror("professionalTitle", ["previewTitle", "previewDegree"]);

    const phoneInput = document.getElementById("phoneField");
    const phoneRow = document.getElementById("previewPhoneRow");
    const phoneSpan = document.getElementById("previewPhone");
    const inlinePhone = document.getElementById("previewInlinePhone");
    if (phoneInput) {
        phoneInput.addEventListener("input", function () {
            const v = this.value.trim();
            if (phoneSpan) phoneSpan.textContent = v;
            if (phoneRow) phoneRow.style.display = v === "" ? "none" : "flex";
            if (inlinePhone) inlinePhone.textContent = v ? ("• " + v) : "";
        });
    }

    const locInput = document.getElementById("locationField");
    const locRow = document.getElementById("previewLocationRow");
    const locSpan = document.getElementById("previewLocation");
    const inlineLoc = document.getElementById("previewInlineLocation");
    if (locInput) {
        locInput.addEventListener("input", function () {
            const v = this.value.trim();
            if (locSpan) locSpan.textContent = v;
            if (locRow) locRow.style.display = v === "" ? "none" : "flex";
            if (inlineLoc) inlineLoc.textContent = v ? ("• " + v) : "";
        });
    }

    function bindOptionalRow(inputId, rowId, spanId) {
        const inp = document.getElementById(inputId);
        const row = document.getElementById(rowId);
        const sp = document.getElementById(spanId);
        if (!inp) return;
        inp.addEventListener("input", function () {
            const v = this.value.trim();
            if (sp) sp.textContent = v;
            if (row) row.style.display = v === "" ? "none" : "flex";
        });
    }

    bindOptionalRow("linkedinField", "previewLinkedinRow", "previewLinkedin");
    bindOptionalRow("githubField", "previewGithubRow", "previewGithub");
    bindTextMirror("websiteField", ["previewWebsite"]);

    const summaryInput = document.getElementById("summaryInput");
    const previewSummary = document.getElementById("previewSummary");
    if (summaryInput && previewSummary) {
        summaryInput.addEventListener("input", function () {
            previewSummary.textContent = this.value;
        });
    }

    // Photo preview
    const photoInput = document.getElementById("profileUpload");
    const changePhotoBtn = document.getElementById("changePhotoBtn");
    const editorPhotoPreview = document.getElementById("editorPhotoPreview");
    const previewPhoto = document.getElementById("previewPhoto");
    if (changePhotoBtn && photoInput) {
        changePhotoBtn.addEventListener("click", function () {
            photoInput.click();
        });
    }
    if (photoInput) {
        photoInput.addEventListener("change", function () {
            const file = this.files && this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (ev) {
                const src = ev.target && ev.target.result ? String(ev.target.result) : "";
                if (src) {
                    if (editorPhotoPreview) editorPhotoPreview.src = src;
                    if (previewPhoto) previewPhoto.src = src;
                }
            };
            reader.readAsDataURL(file);
        });
    }

    // Template switching
    document.querySelectorAll(".template-card").forEach(function (card) {
        card.addEventListener("click", function () {
            document.querySelectorAll(".template-card").forEach(function (c) {
                c.classList.remove("active");
            });
            this.classList.add("active");
            const tpl = this.getAttribute("data-template") || "modern";
            if (cvPreview) {
                cvPreview.classList.remove("tpl-modern", "tpl-classic", "tpl-minimal");
                cvPreview.classList.add("tpl-" + tpl);
            }
        });
    });

    // Skills drag-and-drop & preview sync
    const skillDropZone = document.getElementById("skillDropZone");
    const previewSkills = document.getElementById("previewSkills");
    const previewInlineSkills = document.getElementById("previewInlineSkills");
    let draggedSkill = null;

    function syncSkillPreview() {
        if (!skillDropZone) return;
        const selected = Array.from(skillDropZone.querySelectorAll(".selected-skill"));
        const html = selected.map(function (el) {
            const name = el.getAttribute("data-skill-name") || "";
            const span = document.createElement("span");
            span.className = "cv-skill-tag";
            span.textContent = name;
            return span.outerHTML;
        }).join("");
        if (previewSkills) previewSkills.innerHTML = html;
        if (previewInlineSkills) previewInlineSkills.innerHTML = html;
    }

    function addSkillToSelected(skillId, skillName) {
        if (!skillDropZone || !skillId || !skillName) return;
        if (skillDropZone.querySelector('.selected-skill[data-skill-id="' + skillId + '"]')) return;
        const chip = document.createElement("span");
        chip.className = "selected-skill";
        chip.setAttribute("data-skill-id", String(skillId));
        chip.setAttribute("data-skill-name", skillName);

        const label = document.createElement("span");
        label.textContent = skillName;

        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "remove-skill-btn";
        btn.setAttribute("aria-label", "Remove skill");
        btn.innerHTML = "&times;";

        chip.appendChild(label);
        chip.appendChild(btn);
        skillDropZone.appendChild(chip);
        syncSkillPreview();
    }

    document.querySelectorAll(".drag-skill").forEach(function (item) {
        item.addEventListener("dragstart", function (e) {
            draggedSkill = {
                id: this.getAttribute("data-skill-id"),
                name: this.getAttribute("data-skill-name") || this.textContent.trim()
            };
            if (e.dataTransfer) {
                e.dataTransfer.setData("text/plain", String(draggedSkill.id || ""));
            }
        });
        item.addEventListener("click", function () {
            addSkillToSelected(
                this.getAttribute("data-skill-id"),
                this.getAttribute("data-skill-name") || this.textContent.trim()
            );
        });
    });

    if (skillDropZone) {
        skillDropZone.addEventListener("dragover", function (e) {
            e.preventDefault();
            skillDropZone.classList.add("drag-over");
        });
        skillDropZone.addEventListener("dragleave", function () {
            skillDropZone.classList.remove("drag-over");
        });
        skillDropZone.addEventListener("drop", function (e) {
            e.preventDefault();
            skillDropZone.classList.remove("drag-over");
            if (draggedSkill && draggedSkill.id) {
                addSkillToSelected(draggedSkill.id, draggedSkill.name);
            }
            draggedSkill = null;
        });
        skillDropZone.addEventListener("click", function (e) {
            const rm = e.target.closest(".remove-skill-btn");
            if (rm) {
                const chip = rm.closest(".selected-skill");
                if (chip) chip.remove();
                syncSkillPreview();
            }
        });
    }

    // Completed portfolio projects drag-and-drop & preview sync
    const projectDropZone = document.getElementById("projectDropZone");
    const previewProjects = document.getElementById("previewProjects");
    let draggedProject = null;

    function syncProjectPreview() {
        if (!previewProjects || !projectDropZone) return;
        const items = Array.from(projectDropZone.querySelectorAll(".selected-project-item"));
        previewProjects.innerHTML = items.map(function (el) {
            const title = el.getAttribute("data-project-title") || "";
            const status = el.getAttribute("data-project-status") || "Completed";
            const period = el.getAttribute("data-project-period") || "";
            const desc = el.getAttribute("data-project-desc") || "";
            const repo = el.getAttribute("data-project-repo") || "";

            const wrap = document.createElement("div");
            wrap.className = "timeline-item";

            const marker = document.createElement("div");
            marker.className = "timeline-marker";
            wrap.appendChild(marker);

            const body = document.createElement("div");
            body.className = "timeline-body";

            const h3 = document.createElement("h3");
            h3.textContent = title;
            body.appendChild(h3);

            const sub = document.createElement("div");
            sub.className = "timeline-sub";
            const stSpan = document.createElement("span");
            stSpan.textContent = status;
            sub.appendChild(stSpan);
            if (period) {
                const pSpan = document.createElement("span");
                pSpan.textContent = " | " + period;
                sub.appendChild(pSpan);
            }
            body.appendChild(sub);

            if (desc) {
                const pDesc = document.createElement("p");
                pDesc.textContent = desc;
                body.appendChild(pDesc);
            }
            if (repo) {
                const tagsDiv = document.createElement("div");
                tagsDiv.className = "timeline-tags";
                const chip = document.createElement("span");
                chip.className = "cv-project-chip";
                chip.textContent = repo;
                tagsDiv.appendChild(chip);
                body.appendChild(tagsDiv);
            }

            wrap.appendChild(body);
            return wrap.outerHTML;
        }).join("");
    }

    function addProjectToCv(proj) {
        if (!projectDropZone || !proj || !proj.id || !proj.title) return;
        const pid = String(proj.id);
        if (projectDropZone.querySelector('.selected-project-item[data-project-id="' + pid + '"]')) {
            syncProjectPreview();
            return;
        }

        const item = document.createElement("div");
        item.className = "selected-project-item";
        item.setAttribute("data-project-id", pid);
        item.setAttribute("data-project-title", proj.title);
        item.setAttribute("data-project-status", proj.status || "Completed");
        item.setAttribute("data-project-period", proj.period || "");
        item.setAttribute("data-project-desc", proj.desc || "");
        item.setAttribute("data-project-repo", proj.repo || "");

        const info = document.createElement("div");
        info.className = "selected-project-info";

        const strong = document.createElement("strong");
        strong.textContent = proj.title;
        info.appendChild(strong);

        if (proj.desc) {
            const small = document.createElement("small");
            small.textContent = proj.desc.length > 95 ? proj.desc.slice(0, 95) + "..." : proj.desc;
            info.appendChild(small);
        }

        const rmBtn = document.createElement("button");
        rmBtn.type = "button";
        rmBtn.className = "remove-project-btn";
        rmBtn.setAttribute("aria-label", "Remove project");
        rmBtn.innerHTML = "&times;";

        item.appendChild(info);
        item.appendChild(rmBtn);
        projectDropZone.appendChild(item);
        syncProjectPreview();
    }

    document.querySelectorAll(".draggable-project-card").forEach(function (card) {
        card.addEventListener("dragstart", function (e) {
            draggedProject = {
                id: this.getAttribute("data-project-id"),
                title: this.getAttribute("data-project-title") || "",
                status: this.getAttribute("data-project-status") || "Completed",
                period: this.getAttribute("data-project-period") || "",
                desc: this.getAttribute("data-project-desc") || "",
                repo: this.getAttribute("data-project-repo") || ""
            };
            card.classList.add("dragging");
            if (e.dataTransfer) {
                e.dataTransfer.setData("text/plain", String(draggedProject.id || ""));
            }
        });
        card.addEventListener("dragend", function () {
            card.classList.remove("dragging");
        });
    });

    if (projectDropZone) {
        projectDropZone.addEventListener("dragover", function (e) {
            e.preventDefault();
            projectDropZone.classList.add("drag-over");
        });
        projectDropZone.addEventListener("dragleave", function () {
            projectDropZone.classList.remove("drag-over");
        });
        projectDropZone.addEventListener("drop", function (e) {
            e.preventDefault();
            projectDropZone.classList.remove("drag-over");
            if (draggedProject && draggedProject.id) {
                addProjectToCv(draggedProject);
            }
            draggedProject = null;
        });
        projectDropZone.addEventListener("click", function (e) {
            const rm = e.target.closest(".remove-project-btn");
            if (rm) {
                const row = rm.closest(".selected-project-item");
                if (row) row.remove();
                syncProjectPreview();
            }
        });
    }

    // Zoom controls
    let zoom = 1.0;
    const zoomLevelEl = document.getElementById("zoomLevel");
    function applyZoom() {
        if (cvPreview) {
            cvPreview.style.transform = "scale(" + zoom + ")";
            cvPreview.style.transformOrigin = "top center";
        }
        if (zoomLevelEl) {
            zoomLevelEl.textContent = Math.round(zoom * 100) + "%";
        }
    }
    const zoomInBtn = document.getElementById("zoomInBtn");
    const zoomOutBtn = document.getElementById("zoomOutBtn");
    if (zoomInBtn) {
        zoomInBtn.addEventListener("click", function () {
            zoom = Math.min(1.25, Number((zoom + 0.1).toFixed(2)));
            applyZoom();
        });
    }
    if (zoomOutBtn) {
        zoomOutBtn.addEventListener("click", function () {
            zoom = Math.max(0.7, Number((zoom - 0.1).toFixed(2)));
            applyZoom();
        });
    }

    // Toast helper
    const toastEl = document.getElementById("saveToast");
    const toastTextEl = document.getElementById("saveToastText");
    const closeToastBtn = document.getElementById("closeSaveToastBtn");
    let toastTimer = null;

    function showCvToast(msg, isError) {
        if (!toastEl || !toastTextEl) return;
        toastTextEl.textContent = msg;
        toastEl.classList.toggle("error", Boolean(isError));
        toastEl.classList.add("show");
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(function () {
            toastEl.classList.remove("show");
        }, 3500);
    }

    if (closeToastBtn && toastEl) {
        closeToastBtn.addEventListener("click", function () {
            toastEl.classList.remove("show");
        });
    }

    // Save CV
    const saveBtn = document.getElementById("saveCvBtn");
    if (saveBtn) {
        saveBtn.addEventListener("click", function () {
            const skillIds = Array.from(document.querySelectorAll("#skillDropZone .selected-skill"))
                .map(function (el) { return Number(el.getAttribute("data-skill-id")) || 0; })
                .filter(function (id) { return id > 0; });

            const projectIds = Array.from(document.querySelectorAll("#projectDropZone .selected-project-item"))
                .map(function (el) { return Number(el.getAttribute("data-project-id")) || 0; })
                .filter(function (id) { return id > 0; });

            const activeCard = document.querySelector(".template-card.active");
            const templateKey = activeCard ? (activeCard.getAttribute("data-template") || "modern") : "modern";

            const payload = {
                csrf: csrfToken,
                resume_title: document.getElementById("resumeTitle") ? document.getElementById("resumeTitle").value : "",
                name: document.getElementById("fullName") ? document.getElementById("fullName").value : "",
                title: document.getElementById("professionalTitle") ? document.getElementById("professionalTitle").value : "",
                phone: phoneInput ? phoneInput.value : "",
                location: locInput ? locInput.value : "",
                linkedin: document.getElementById("linkedinField") ? document.getElementById("linkedinField").value : "",
                github: document.getElementById("githubField") ? document.getElementById("githubField").value : "",
                website: document.getElementById("websiteField") ? document.getElementById("websiteField").value : "",
                summary: summaryInput ? summaryInput.value : "",
                skill_ids: skillIds,
                project_ids: projectIds,
                template: templateKey
            };

            saveBtn.disabled = true;
            fetch("cv_builder.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                saveBtn.disabled = false;
                if (res && res.ok) {
                    showCvToast(res.message || "CV saved successfully.", false);
                } else {
                    showCvToast((res && res.message) || "Could not save CV.", true);
                }
            })
            .catch(function () {
                saveBtn.disabled = false;
                showCvToast("Network error while saving CV.", true);
            });
        });
    }

    // Export single-page CV cleanly via print
    const exportBtn = document.getElementById("exportCvBtn");
    if (exportBtn && cvPreview && cvPrintSheet) {
        exportBtn.addEventListener("click", function () {
            const clone = cvPreview.cloneNode(true);
            clone.removeAttribute("id");
            clone.style.transform = "none";

            const srcImg = cvPreview.querySelector("img");
            const dstImg = clone.querySelector("img");
            if (srcImg && dstImg && srcImg.src) {
                dstImg.src = srcImg.src;
            }

            cvPrintSheet.innerHTML = "";
            cvPrintSheet.appendChild(clone);

            const prevTitle = document.title;
            const personName = (document.getElementById("fullName")?.value || "Student_CV").trim();
            document.title = personName.replace(/[^a-zA-Z0-9_-]+/g, "_") + "_CV";

            const cleanup = function () {
                document.title = prevTitle;
                cvPrintSheet.innerHTML = "";
                window.removeEventListener("afterprint", cleanup);
            };
            window.addEventListener("afterprint", cleanup);
            window.print();
            setTimeout(cleanup, 1000);
        });
    }
});
</script>

<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>