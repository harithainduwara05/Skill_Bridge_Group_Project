<?php
require_once '../../../Config/db.php';
require_once '../../../Session/Session.php';
require_role('company');

$user = current_user();
$companyEmail = $user['email'] ?? $user['Email'] ?? '';
$companyStmt = $conn->prepare('SELECT Name FROM company WHERE Email = ? LIMIT 1');
$companyStmt->bind_param('s', $companyEmail);
$companyStmt->execute();
$company = $companyStmt->get_result()->fetch_assoc();
$applicationId = (int) ($_GET['application_id'] ?? 0);
$candidate = null;

if ($company && $applicationId > 0) {
    $applicationStmt = $conn->prepare("SELECT s.Email, s.Name, s.University, s.degree, s.year, s.bio, s.profile_image, s.github, s.linkedin, s.website,
                                               ia.status AS application_status, ia.applied_date, i.title AS internship_title
                                        FROM internship_applications ia
                                        INNER JOIN internships i ON i.id = ia.internship_id
                                        INNER JOIN student s ON s.Email = ia.Email
                                        WHERE ia.application_id = ? AND TRIM(i.company) = TRIM(?)
                                        LIMIT 1");
    $applicationStmt->bind_param('is', $applicationId, $company['Name']);
    $applicationStmt->execute();
    $candidate = $applicationStmt->get_result()->fetch_assoc();
}

$skills = $projects = $certificates = [];
if ($candidate) {
    $email = $candidate['Email'];
    $stmt = $conn->prepare('SELECT skill_name, level FROM skills WHERE Email = ? ORDER BY skill_name');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $skills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $conn->prepare('SELECT p.title, p.description, p.category, p.tech_stack, p.company, p.deadline, sp.role, sp.progress, sp.status FROM student_projects sp INNER JOIN projects p ON p.id = sp.project_id WHERE sp.Email = ? ORDER BY sp.student_project_id DESC');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $conn->prepare('SELECT certificate_name, issuer, certificate_file, status FROM certificates WHERE Email = ? ORDER BY certificate_id DESC');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $certificates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$pageTitle = 'Candidate Details';
include '../../../Includes/company_sidebar.php';
include '../../../Includes/dash_header.php';
?>
<link rel="stylesheet" href="<?= $GLOBALS['BASE_URL'] ?>/Assets/CSS/Company/candidates.css?v=<?= filemtime(__DIR__ . '/../../../Assets/CSS/Company/candidates.css') ?>">

<main class="company-candidates-page">
    <a class="back-applications-btn" href="applications.php"><span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>Back to Applications</a>

    <?php if (!$candidate): ?>
        <section class="candidate-card candidate-not-found"><h1>Candidate details unavailable</h1><p>This application could not be found for your company.</p></section>
    <?php else: ?>
        <?php
            $candidateName = trim($candidate['Name'] ?? 'Candidate');
            $nameParts = preg_split('/\s+/', $candidateName, -1, PREG_SPLIT_NO_EMPTY);
            $initials = strtoupper(substr($nameParts[0] ?? 'C', 0, 1) . substr($nameParts[count($nameParts) - 1] ?? '', 0, 1));
            $academicYear = trim((string) ($candidate['year'] ?? ''));
            $degree = trim((string) ($candidate['degree'] ?? ''));
        ?>
        <div class="candidate-layout">
            <div class="candidate-main-column">
                <section class="candidate-profile-card">
                    <div class="profile-accent"></div>
                    <div class="candidate-avatar"><?= htmlspecialchars($initials) ?></div>
                    <div class="candidate-info">
                        <h1><?= htmlspecialchars($candidateName) ?></h1>
                        <h3><?= htmlspecialchars($candidate['University'] ?? 'University not provided') ?></h3>
                        <p class="candidate-degree"><?= htmlspecialchars($degree ?: 'Degree not provided') ?><?= $academicYear !== '' ? ' · ' . htmlspecialchars($academicYear) : '' ?></p>
                        <div class="candidate-contact">
                            <span><span class="material-symbols-outlined">mail</span><?= htmlspecialchars($candidate['Email']) ?></span>
                            <?php foreach (['github' => 'GitHub', 'linkedin' => 'LinkedIn', 'website' => 'Website'] as $field => $label): ?>
                                <?php if (!empty($candidate[$field])): ?><a href="<?= htmlspecialchars($candidate[$field], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($label) ?></a><?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php if (!empty($candidate['bio'])): ?><p class="candidate-bio"><?= nl2br(htmlspecialchars($candidate['bio'])) ?></p><?php endif; ?>
                    </div>
                </section>

                <div class="candidate-overview-grid">
                    <section class="candidate-card skills-card">
                        <div class="section-title"><span class="section-icon orange"><span class="material-symbols-outlined">bolt</span></span><h2>Skills &amp; Expertise</h2></div>
                        <div class="skills-list">
                            <?php foreach ($skills as $skill): ?><span><?= htmlspecialchars($skill['skill_name']) ?><?= !empty($skill['level']) ? ' · ' . htmlspecialchars($skill['level']) : '' ?></span><?php endforeach; ?>
                            <?php if (!$skills): ?><span>No skills listed</span><?php endif; ?>
                        </div>
                    </section>
                    <section class="academic-card">
                        <h2>Academic Snapshot</h2>
                        <div class="academic-grid">
                            <div class="academic-item"><span>Academic Year</span><strong><?= htmlspecialchars($academicYear ?: 'Not provided') ?></strong></div>
                            <div class="academic-item"><span>Projects</span><strong><?= count($projects) ?></strong></div>
                            <div class="academic-item"><span>Certificates</span><strong><?= count($certificates) ?></strong></div>
                            <div class="academic-item"><span>Degree</span><strong><?= htmlspecialchars($degree ?: 'Not provided') ?></strong></div>
                        </div>
                    </section>
                </div>

                <section class="portfolio-section">
                    <div class="portfolio-heading"><h2>Portfolio Projects</h2></div>
                    <?php if ($projects): ?>
                        <div class="portfolio-grid">
                            <?php foreach ($projects as $project): ?>
                                <article class="portfolio-card">
                                    <div class="portfolio-content">
                                        <?php if (!empty($project['category'])): ?><span class="project-category"><?= htmlspecialchars($project['category']) ?></span><?php endif; ?>
                                        <h3><?= htmlspecialchars($project['title']) ?></h3>
                                        <?php if (!empty($project['description'])): ?><p><?= nl2br(htmlspecialchars($project['description'])) ?></p><?php endif; ?>
                                        <div class="project-meta">
                                            <?php if (!empty($project['role'])): ?><span>Role: <?= htmlspecialchars($project['role']) ?></span><?php endif; ?>
                                            <?php if ($project['progress'] !== null): ?><span>Progress: <?= (int) $project['progress'] ?>%</span><?php endif; ?>
                                            <?php if (!empty($project['status'])): ?><span><?= htmlspecialchars($project['status']) ?></span><?php endif; ?>
                                        </div>
                                        <?php if (!empty($project['tech_stack'])): ?><div class="tech-stack"><?php foreach (array_slice(array_map('trim', explode(',', $project['tech_stack'])), 0, 5) as $tech): ?><span><?= htmlspecialchars($tech) ?></span><?php endforeach; ?></div><?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?><p class="candidate-empty">No projects listed.</p><?php endif; ?>
                </section>
            </div>

            <aside class="candidate-side-column">
                <section class="resume-card" id="resumePreview">
                    <div class="resume-header">
                        <div class="resume-heading"><strong>Candidate CV Preview</strong><span class="resume-profile-note">Built from candidate profile</span></div>
                        <button type="button" class="candidate-btn candidate-btn-outline resume-download-btn" id="downloadCvBtn">
                            <span class="material-symbols-outlined" aria-hidden="true">download</span>Download CV
                        </button>
                    </div>
                    <div class="resume-preview"><div class="resume-paper">
                        <h2><?= htmlspecialchars($candidateName) ?></h2>
                        <p class="resume-contact"><?= htmlspecialchars($candidate['Email']) ?></p>
                        <div class="resume-divider"></div>
                        <div class="resume-section"><h4>EDUCATION</h4><strong><?= htmlspecialchars($candidate['University'] ?? 'University not provided') ?></strong><p><?= htmlspecialchars($degree ?: 'Degree not provided') ?><?= $academicYear !== '' ? ' · ' . htmlspecialchars($academicYear) : '' ?></p></div>
                        <?php if (!empty($candidate['bio'])): ?><div class="resume-section"><h4>PROFILE</h4><p><?= nl2br(htmlspecialchars($candidate['bio'])) ?></p></div><?php endif; ?>
                        <div class="resume-section"><h4>SKILLS</h4><p><?= htmlspecialchars(implode(', ', array_column($skills, 'skill_name')) ?: 'No skills listed') ?></p></div>
                        <?php if ($projects): ?><div class="resume-section"><h4>PROJECTS</h4><?php foreach ($projects as $project): ?><strong><?= htmlspecialchars($project['title']) ?></strong><?php if (!empty($project['description'])): ?><p><?= htmlspecialchars($project['description']) ?></p><?php endif; ?><?php endforeach; ?></div><?php endif; ?>
                    </div></div>
                </section>

                <section class="candidate-card certificates-card">
                    <div class="section-title"><span class="section-icon orange"><span class="material-symbols-outlined">verified</span></span><h2>Certificates</h2></div>
                    <div class="certificate-list">
                        <?php foreach ($certificates as $certificate): ?>
                            <div class="certificate-item"><div class="certificate-icon"><span class="material-symbols-outlined">workspace_premium</span></div><div class="certificate-info"><strong><?= htmlspecialchars($certificate['certificate_name'] ?? 'Certificate') ?></strong><span><?= htmlspecialchars($certificate['issuer'] ?? 'Issuer not provided') ?><?= !empty($certificate['status']) ? ' · ' . htmlspecialchars($certificate['status']) : '' ?></span></div>
                                <?php if (!empty($certificate['certificate_file'])): ?><a href="<?= htmlspecialchars('../../../' . ltrim($certificate['certificate_file'], '/\\'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" aria-label="Open certificate"><span class="material-symbols-outlined">open_in_new</span></a><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$certificates): ?><p class="candidate-empty">No certificates listed.</p><?php endif; ?>
                    </div>
                </section>
            </aside>
        </div>
    <?php endif; ?>

    <?php include '../../../Includes/company_dashboard_footer.php'; ?>
</main>
<script src="<?= $GLOBALS['BASE_URL'] ?>/Assets/JS/Company/candidates.js?v=<?= filemtime(__DIR__ . '/../../../Assets/JS/Company/candidates.js') ?>"></script>
<?php include '../../../Includes/dash_footer.php'; ?>
