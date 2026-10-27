<?php

declare(strict_types=1);
require_once __DIR__ . '/../../Config/db.php';
require_once __DIR__ . '/../../Session/Session.php';
require_once __DIR__ . '/../../Backend/Student/StudentPortfolioRepository.php';

require_role('student');

$user = current_user();
$email = $user['Email'] ?? $user['email'] ?? null;

if (!is_string($email) || $email === '') {
    http_response_code(403);
    exit('Student email not found.');
}

$repo = new StudentPortfolioRepository($conn);

if (empty($_SESSION['student_portfolio_csrf'])) {
    $_SESSION['student_portfolio_csrf'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['student_portfolio_csrf'];

function portfolioJson(array $data): string{
    return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
}

function portfolioEsc(?string $value): string{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Returns a stored filename only, never a path supplied by the browser. */
function portfolioUpload(string $field, string $type): ?string{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024 || $file['size'] < 1) {
        throw new InvalidArgumentException('The uploaded file must be smaller than 5 MB.');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new InvalidArgumentException('Invalid upload.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $allowed = $type === 'certificate'
        ? ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf']
        : ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) {
        throw new InvalidArgumentException('Unsupported file type.');
    }

    $folder = __DIR__ . '/../../Assets/Images/Student/Portfolio/';
    if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
        throw new RuntimeException('Upload directory is unavailable.');
    }

    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $folder . $name)) {
        throw new RuntimeException('Could not store uploaded file.');
    }
    return $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Invalid form token. Refresh the page and try again.');
    }

    try {
        $action = (string) ($_POST['action'] ?? '');

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($action === 'save_project') {
            $data = $_POST;
            $data['project_image'] = portfolioUpload('project_image', 'project');
            $data['technologies'] = array_filter(array_map('trim', explode(',', (string) ($_POST['technologies'] ?? ''))));
            $repo->saveProject($email, $data, $id ?: null);

            $_SESSION['portfolio_notice'] = 'Project saved successfully.';
        } elseif ($action === 'delete_project' && $id) {
            $repo->deleteProject($email, $id);

            $_SESSION['portfolio_notice'] = 'Project deleted.';
        } elseif ($action === 'save_internal' && $id) {
            $repo->editInternalPresentation($email, $id, (string) ($_POST['title'] ?? ''), (string) ($_POST['description'] ?? ''));

            $_SESSION['portfolio_notice'] = 'Portfolio display updated. The original organization project was not changed.';
        } elseif ($action === 'hide_internal' && $id) {
            $repo->hideInternalProject($email, $id);

            $_SESSION['portfolio_notice'] = 'Organization project removed from your portfolio only.';
        } elseif ($action === 'save_certificate') {
            $data = $_POST;

            $data['certificate_file'] = portfolioUpload('certificate_file', 'certificate');

            $repo->saveCertificate($email, $data, $id ?: null);

            $_SESSION['portfolio_notice'] = 'Certificate saved successfully.';
        } elseif ($action === 'delete_certificate' && $id) {
            $repo->deleteCertificate($email, $id);

            $_SESSION['portfolio_notice'] = 'Certificate deleted.';
        } else {
            throw new InvalidArgumentException('Unknown action or invalid record ID.');
        }
    } catch (Throwable $exception) {
        // Do not expose SQL exceptions to the browser.

        $_SESSION['portfolio_error'] = $exception instanceof InvalidArgumentException

            ? $exception->getMessage()

            : 'Unable to save the request. Check the database migration and server logs.';

        error_log('Portfolio: ' . $exception->getMessage());
    }

    header('Location: portfolio.php', true, 303);

    exit;
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 6;
// Keep the page renderable if the database upgrade has not yet been installed.
// The detailed database error is written to the PHP error log.
$databaseError = null;
$summary = ['projects' => 0, 'certificates' => 0, 'views' => 0];
$internalProjects = [];
$projects = [];
$certificates = [];
$pageCount = 1;

try {
    $summary = $repo->summary($email);
    $internalProjects = $repo->internalProjects($email);
    $summary['projects'] += count($internalProjects);
    $externalCount = $repo->countProjects($email);
    $pageCount = max(1, (int) ceil($externalCount / $perPage));
    $page = min($page, $pageCount);
    $projects = $repo->projects($email, $page, $perPage);
    $certificates = $repo->certificates($email);
} catch (Throwable $exception) {
    error_log('SkillBridge Portfolio database read failed: ' . $exception->getMessage());

    $databaseError = 'Portfolio data could not be loaded. Confirm the Student Portfolio database migrations have been imported.';
}

$notice = $_SESSION['portfolio_notice'] ?? null;
$error = $_SESSION['portfolio_error'] ?? null;
unset($_SESSION['portfolio_notice'], $_SESSION['portfolio_error']);
$pageTitle = 'My Portfolio | SkillBridge';
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/portfolio.css">'

    . '<link rel="stylesheet" href="https\://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">';

include __DIR__ . '/../../Includes/student_sidebar.php';

include __DIR__ . '/../../Includes/dash_header.php';

?>
<main class="content portfolio-page">
    <section class="portfolio-header">
        <div>
            <h1>My Portfolio</h1>
            <p>Showcase your projects, certificates, and professional achievements.</p>
        </div>
        <div class="portfolio-buttons">
            <button type="button" class="btn-project" onclick="portfolioOpen('projectModal')">+ Add Project</button>
            <button type="button" class="btn-certificate" onclick="portfolioOpen('certificateModal')">+ Add Certificate</button>
        </div>
    </section>

    <?php if ($notice || $error || $databaseError): ?>
        <div class="portfolio-toast-stack" aria-live="polite" aria-atomic="false">
            <?php foreach (array_filter([
                $notice ? ['message' => $notice, 'error' => false] : null,
                $error ? ['message' => $error, 'error' => true] : null,
                $databaseError ? ['message' => $databaseError, 'error' => true] : null
            ]) as $toast): ?>
                <div class="portfolio-toast <?= $toast['error'] ? 'is-error' : 'is-success' ?>"
                     role="<?= $toast['error'] ? 'alert' : 'status' ?>" data-timeout="<?= $toast['error'] ? 7000 : 4500 ?>">
                    <i class="fa-solid <?= $toast['error'] ? 'fa-circle-exclamation' : 'fa-circle-check' ?>" aria-hidden="true"></i>
                    <span><?= portfolioEsc($toast['message']) ?></span>
                    <button type="button" class="portfolio-toast-close" aria-label="Dismiss notification">&times;</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <section class="portfolio-stats" aria-label="Portfolio statistics">
        <div class="stat-card"><div class="stat-icon blue"><i class="fa-solid fa-folder-open"></i></div><div><span>Projects</span><h2><?= $summary['projects'] ?></h2><p>Portfolio projects</p></div></div>
        <div class="stat-card"><div class="stat-icon orange"><i class="fa-solid fa-certificate"></i></div><div><span>Certificates</span><h2><?= $summary['certificates'] ?></h2><p>Professional credentials</p></div></div>
        <div class="stat-card"><div class="stat-icon green"><i class="fa-solid fa-eye"></i></div><div><span>Profile Views</span><h2><?= $summary['views'] ?></h2><p>Recorded public views</p></div></div>
    </section>

    <section class="portfolio-section">
        <div class="section-title"><h2>All Projects</h2></div>
        <div class="project-grid">
            <?php if ($page === 1): foreach ($internalProjects as $project): ?>
                <article class="project-card">
                    <button type="button" class="portfolio-card-open" onclick='portfolioViewProject(<?= portfolioJson($project) ?>)' aria-label="View <?= portfolioEsc($project['title']) ?> details">
                        <div class="project-image"><div class="portfolio-empty-image"><i class="fa-solid fa-people-group"></i></div></div>

                        <div class="project-content">
                            <span class="portfolio-source">SkillBridge organization project</span>
                            <h3><?= portfolioEsc($project['title']) ?></h3>
                            <p class="portfolio-description-preview"><?= portfolioEsc($project['description']) ?></p>
                            <span class="portfolio-see-more">See more <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                            <div class="tags"><?php foreach ($project['technologies'] as $technology): ?><span><?= portfolioEsc($technology) ?></span><?php endforeach; ?></div>
                            <p class="portfolio-small"><?= portfolioEsc($project['company']) ?> · <?= portfolioEsc($project['project_status']) ?></p>
                        </div>
                    </button>
                    <div class="project-links portfolio-card-actions">
                        <button type="button" class="edit-btn" title="Edit portfolio display" onclick='portfolioEditInternal(<?= portfolioJson($project) ?>)'><i class="fa-solid fa-pen"></i></button>
                        <form method="post" onsubmit="return confirm('Remove this project from your portfolio? The original project will remain unchanged.')">
                            <input type="hidden" name="csrf" value="<?= portfolioEsc($csrf) ?>">
                            <input type="hidden" name="action" value="hide_internal">
                            <input type="hidden" name="id" value="<?= (int) $project['student_project_id'] ?>">
                            <button class="delete-btn" title="Remove from my portfolio" type="submit"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </article>

            <?php endforeach; endif; ?>
            <?php foreach ($projects as $project): ?>
                <?php
                $filename = basename((string) ($project['project_image'] ?? ''));
                $image = '';
                if ($filename !== '') {
                    // Support both uploaded files and existing legacy portfolio images.
                    foreach (['Assets/Images/Student/Portfolio/', 'Assets/Images/UI/portfolio/', 'Assets/Images/Student/'] as $dir) {
                        if (is_file(__DIR__ . '/../../' . $dir . $filename)) {
                            $image = '../../' . $dir . rawurlencode($filename);
                            break;
                        }
                    }
                }
                ?>

                <article class="project-card">
                    <button type="button" class="portfolio-card-open" onclick='portfolioViewProject(<?= portfolioJson($project + ['source' => 'external', 'id' => $project['portfolio_project_id']]) ?>)' aria-label="View <?= portfolioEsc($project['title']) ?> details">
                    <div class="project-image">
                        <?php if ($image): ?><img src="<?= portfolioEsc($image) ?>" alt="Project cover" loading="lazy"><?php else: ?><div class="portfolio-empty-image"><i class="fa-solid fa-folder-open"></i></div><?php endif; ?>
                    </div>

                    <div class="project-content">
                        <span class="portfolio-source">Personal project</span>
                        <h3><?= portfolioEsc($project['title']) ?></h3>
                        <p class="portfolio-description-preview"><?= portfolioEsc($project['description']) ?></p>
                            <span class="portfolio-see-more">See more <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                        <div class="tags"><?php foreach ($project['technologies'] as $technology): ?><span><?= portfolioEsc($technology) ?></span><?php endforeach; ?></div>
                        <p class="portfolio-small"><?= portfolioEsc($project['project_status']) ?></p>
                    </div>
                    </button>

                    <div class="project-content portfolio-card-actions">
                        <div class="project-links">
                            <button type="button" class="edit-btn" title="Edit project" onclick='portfolioEditProject(<?= json_encode($project, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="fa-solid fa-pen"></i></button>
                            <form method="post" onsubmit="return confirm('Delete this project?')">
                                <input type="hidden" name="csrf" value="<?= portfolioEsc($csrf) ?>">
                                <input type="hidden" name="action" value="delete_project">
                                <input type="hidden" name="id" value="<?= (int) $project['portfolio_project_id'] ?>">
                                <button type="submit" class="delete-btn" title="Delete project"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </article>

            <?php endforeach; ?>

        </div>

        <?php if (!$projects && !$internalProjects): ?><p class="portfolio-empty">No portfolio projects yet. Add your first project.</p><?php endif; ?>
        <?php if ($pageCount > 1): ?>
            <nav class="portfolio-pagination" aria-label="Portfolio pagination">
                <?php for ($i = 1; $i <= $pageCount; $i++): ?>
                    <a class="page-number <?= $page === $i ? 'active' : '' ?>" href="?page=<?= $i ?>"><?= $i ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </section>

    <section class="portfolio-section">
        <div class="section-title"><h2>Licenses & Certifications</h2></div>
        <div class="certificate-grid">
            <?php foreach ($certificates as $certificate): ?>
                <?php $fileUrl = '';
                $storedName = basename((string) ($certificate['certificate_file'] ?? ''));

                if ($storedName !== '') {
                    foreach (['Portfolio', 'Certificates', ''] as $folder) {
                        $relative = 'Assets/Images/Student/' . ($folder !== '' ? $folder . '/' : '') . $storedName;

                        if (is_file(__DIR__ . '/../../' . $relative)) {
                            $fileUrl = '../../Assets/Images/Student/' . ($folder !== '' ? $folder . '/' : '') . rawurlencode($storedName);

                            break;
                        }
                    }
                } ?>

                <?php $certificate['preview_url'] = $fileUrl ?: null; ?>

                <article class="certificate-card">
                    <button
                        type="button"
                        class="portfolio-card-open"
                        onclick='portfolioViewCertificate(<?= portfolioJson($certificate) ?>)'
                        aria-label="View <?= portfolioEsc($certificate['certificate_name']) ?> certificate"
                    >
                        <div class="certificate-image">
                            <?php if ($fileUrl && str_ends_with(strtolower(parse_url($fileUrl, PHP_URL_PATH) ?? ''), '.pdf')): ?>
                                <div class="certificate-pdf-thumb">
                                    <iframe
                                        src="<?= portfolioEsc($fileUrl) ?>#page=1&toolbar=0&navpanes=0&scrollbar=0&view=FitH"
                                        title="Certificate first-page PDF preview"
                                        loading="lazy"
                                        tabindex="-1"
                                        aria-hidden="true"
                                    ></iframe>
                                    <span class="certificate-pdf-label"><i class="fa-solid fa-file-pdf"></i> PDF certificate</span>
                                </div>
                            <?php elseif ($fileUrl): ?>
                                <img src="<?= portfolioEsc($fileUrl) ?>" alt="Certificate preview" loading="lazy">
                            <?php else: ?>
                                <div class="certificate-placeholder">
                                    <i class="fa-solid fa-certificate" aria-hidden="true"></i>
                                    <span>No file preview</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="certificate-content">
                            <h3><?= portfolioEsc($certificate['certificate_name']) ?></h3>
                            <p class="certificate-issuer"><?= portfolioEsc($certificate['issuer']) ?></p>
                            <div class="certificate-info">
                                <span><i class="fa-regular fa-calendar"></i> <?= portfolioEsc($certificate['issue_date'] ?: 'Date not specified') ?></span>
                                <?php if (!empty($certificate['credential_id'])): ?>
                                    <span class="certificate-credential-id" title="Credential ID">ID: <?= portfolioEsc($certificate['credential_id']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($certificate['skills'])): ?>
                                <div class="certificate-skills" aria-label="Certificate skills">
                                    <?php foreach (array_slice($certificate['skills'], 0, 3) as $skill): ?>
                                        <span><?= portfolioEsc($skill) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </button>
                    <div class="certificate-actions">
                        <button
                            type="button"
                            class="edit-btn"
                            title="Edit certificate"
                            aria-label="Edit certificate"
                            onclick='portfolioEditCertificate(<?= portfolioJson($certificate) ?>)'
                        ><i class="fa-solid fa-pen"></i></button>
                        <form method="post" onsubmit="return confirm('Delete this certificate?')">
                            <input type="hidden" name="csrf" value="<?= portfolioEsc($csrf) ?>">
                            <input type="hidden" name="action" value="delete_certificate">
                            <input type="hidden" name="id" value="<?= (int) $certificate['certificate_id'] ?>">
                            <button type="submit" class="delete-btn" title="Delete certificate" aria-label="Delete certificate"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </article>

            <?php endforeach; ?>

        </div>

        <?php if (!$certificates): ?><p class="portfolio-empty">No certificates uploaded yet.</p><?php endif; ?>

    </section>

</main>

<div class="modal" id="projectModal" role="dialog" aria-modal="true" aria-labelledby="projectModalTitle">
    <form class="modal-box" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= portfolioEsc($csrf) ?>">
        <input type="hidden" name="action" value="save_project">
        <input type="hidden" name="id" value="">
        <div class="modal-header"><h2 id="projectModalTitle">Add Project</h2><button type="button" class="close" onclick="portfolioClose('projectModal')" aria-label="Close">×</button></div>
        <div class="modal-content">
            <label>Project Name<input name="title" type="text" maxlength="150" required></label>
            <label>Description<textarea name="description" maxlength="10000"></textarea></label>
            <label>Skills Used (comma-separated)<input name="technologies" type="text" placeholder="PHP, React, MySQL"></label>
            <label>Project URL<input name="repository_url" type="url" placeholder="https\://github.com/..."></label>
            <label>Project Image<input name="project_image" type="file" accept="image/jpeg,image/png,image/webp"></label>
            <label>Project Status<select name="project_status"><option>Completed</option><option selected>Currently Working</option></select></label>
            <label>Start Date<input name="start_date" type="date"></label>
            <label>End Date<input name="end_date" type="date"></label>
        </div>
        <div class="modal-footer"><button type="submit" class="save-btn">Save Project</button></div>
    </form>
</div>

<div class="modal" id="certificateModal" role="dialog" aria-modal="true" aria-labelledby="certificateModalTitle">
    <form class="modal-box" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= portfolioEsc($csrf) ?>">
        <input type="hidden" name="action" value="save_certificate">
        <input type="hidden" name="id" value="">
        <div class="modal-header"><h2 id="certificateModalTitle">Add Certificate</h2><button type="button" class="close" onclick="portfolioClose('certificateModal')" aria-label="Close">×</button></div>
        <div class="modal-content">
            <label>Certificate Name<input name="certificate_name" maxlength="150" required></label>
            <label>Issuing Organization<input name="issuer" maxlength="150" required></label>
            <label>Issue Date<input name="issue_date" type="date"></label>
            <label>Expiration Date<input name="expiration_date" type="date"></label>
            <label>Skills Used (comma-separated)
                <input name="skills" maxlength="1500" placeholder="Python, TensorFlow, Machine Learning">
            </label>
            <p class="portfolio-field-hint">List the skills demonstrated by this certificate.</p>
            <label>Credential ID<input name="credential_id" maxlength="150"></label>
            <label>Credential URL<input name="credential_url" type="url"></label>
            <label>Certificate File (PDF or image)<input name="certificate_file" type="file" accept="application/pdf,image/jpeg,image/png,image/webp"></label>
        </div>
        <div class="modal-footer"><button type="submit" class="save-btn">Save Certificate</button></div>
    </form>
</div>

<div class="modal" id="projectViewModal" role="dialog" aria-modal="true" aria-labelledby="projectViewTitle">

    <div class="modal-box portfolio-detail">

        <div class="modal-header"><h2 id="projectViewTitle">Project Details</h2><button class="close" type="button" onclick="portfolioClose('projectViewModal')">×</button></div>

        <div class="modal-content" id="projectViewContent"></div>
    </div>
</div>

<div class="modal" id="certificateViewModal" role="dialog" aria-modal="true" aria-labelledby="certificateViewTitle">
    <div class="modal-box portfolio-detail">
        <div class="modal-header"><h2 id="certificateViewTitle">Certificate Details</h2><button class="close" type="button" onclick="portfolioClose('certificateViewModal')">×</button></div>
        <div class="modal-content" id="certificateViewContent"></div>
    </div>
</div>

<div class="modal" id="internalEditModal" role="dialog" aria-modal="true" aria-labelledby="internalEditTitle">
    <form class="modal-box" method="post">
        <input type="hidden" name="csrf" value="<?= portfolioEsc($csrf) ?>">
        <input type="hidden" name="action" value="save_internal">
        <input type="hidden" name="id" value="">
        <div class="modal-header"><h2 id="internalEditTitle">Edit Portfolio Display</h2><button class="close" type="button" onclick="portfolioClose('internalEditModal')">×</button></div>
        <div class="modal-content">
            <p>These edits affect your portfolio only, not the organization's project.</p>
            <label>Portfolio title<input name="title" maxlength="150" required></label>
            <label>Portfolio description<textarea name="description" maxlength="10000"></textarea></label>
        </div>
        <div class="modal-footer"><button class="save-btn" type="submit">Save Changes</button></div>
    </form>
</div>

<script>

function portfolioText(value) { return document.createTextNode(String(value ?? '')); }
function portfolioField(label, value) {
    const p = document.createElement('p');
    const strong = document.createElement('strong');
    strong.textContent = label + ': ';
    p.append(strong, portfolioText(value || 'Not specified'));
    return p;
}

function portfolioDetailRow(label, value) {
    const row = document.createElement('div');
    row.className = 'portfolio-detail-row';
    const key = document.createElement('span');
    key.className = 'portfolio-detail-label';
    key.textContent = label;
    const content = document.createElement('span');
    content.className = 'portfolio-detail-value';
    content.textContent = String(value ?? 'Not specified');
    row.append(key, content);
    return row;
}

function portfolioViewProject(project) {
    const area = document.getElementById('projectViewContent');
    const isInternal = project.source === 'internal';
    area.replaceChildren();

    const source = document.createElement('span');
    source.className = 'portfolio-detail-source';
    source.textContent = isInternal ? 'SkillBridge organization project' : 'Personal project';
    area.append(source);

    const descriptionHeading = document.createElement('h3');
    descriptionHeading.className = 'portfolio-detail-heading';
    descriptionHeading.textContent = 'About this project';
    const description = document.createElement('p');
    description.className = 'portfolio-detail-description';
    description.textContent = project.description || 'No description provided.';
    area.append(descriptionHeading, description);

    const infoHeading = document.createElement('h3');
    infoHeading.className = 'portfolio-detail-heading';
    infoHeading.textContent = 'Project information';
    const info = document.createElement('div');
    info.className = 'portfolio-detail-info';
    info.append(portfolioDetailRow('Status', project.project_status));
    if (project.company) info.append(portfolioDetailRow('Organization', project.company));
    if (project.role) info.append(portfolioDetailRow('Role', project.role));
    if (project.progress !== undefined && project.progress !== null) {
        info.append(portfolioDetailRow('Progress', project.progress + '%'));
    }
    if (project.start_date) info.append(portfolioDetailRow('Started', project.start_date));
    if (project.end_date) info.append(portfolioDetailRow('Ended', project.end_date));
    area.append(infoHeading, info);

    if (project.technologies?.length) {
        const heading = document.createElement('h3');
        heading.className = 'portfolio-detail-heading';
        heading.textContent = 'Technologies used';
        const tags = document.createElement('div');
        tags.className = 'portfolio-detail-tags';
        project.technologies.forEach(technology => {
            const tag = document.createElement('span');
            tag.textContent = technology;
            tags.append(tag);
        });
        area.append(heading, tags);
    }

    if (project.repository_url && /^https?:\/\//i.test(project.repository_url)) {
        const link = document.createElement('a');
        link.className = 'portfolio-detail-link';
        link.href = project.repository_url;
        link.rel = 'noopener noreferrer';
        link.target = '_blank';
        link.textContent = 'View project repository ↗';
        area.append(link);
    }
    if (isInternal) {
        const note = document.createElement('p');
        note.className = 'portfolio-detail-note';
        note.textContent = 'This is an organization-managed project. Your edits affect only how it appears in your portfolio.';
        area.append(note);
    }
    document.getElementById('projectViewTitle').textContent = project.title || 'Project details';
    const modal = document.getElementById('projectViewModal');
    modal.style.display = 'flex';
    area.scrollTop = 0;
}
function portfolioEditInternal(project) {
    const modal = document.getElementById('internalEditModal');

    const form = modal.querySelector('form');

    form.elements.id.value = project.id;

    form.elements.title.value = project.title || '';

    form.elements.description.value = project.description || '';

    modal.style.display = 'flex';
}

function portfolioViewCertificate(certificate) {
    const area = document.getElementById('certificateViewContent');
    area.replaceChildren();

    const details = document.createElement('div');
    details.className = 'portfolio-certificate-details';
    details.append(portfolioDetailRow('Issued by', certificate.issuer));
    details.append(portfolioDetailRow('Issue date', certificate.issue_date));
    if (certificate.expiration_date) {
        details.append(portfolioDetailRow('Expires', certificate.expiration_date));
    }
    if (certificate.credential_id) {
        details.append(portfolioDetailRow('Credential ID', certificate.credential_id));
    }
    if (certificate.skills?.length) {
        details.append(portfolioDetailRow('Skills used', certificate.skills.join(', ')));
    }
    area.append(details);

    const actions = document.createElement('div');
    actions.className = 'portfolio-certificate-links';

    function addCertificateLink(href, title, iconClass, secondary = false) {
        if (!/^https?:\/\//i.test(href)) return;
        const link = document.createElement('a');
        link.href = href;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.className = 'portfolio-certificate-link' + (secondary ? ' is-secondary' : '');
        const icon = document.createElement('i');
        icon.className = 'fa-solid ' + iconClass;
        icon.setAttribute('aria-hidden', 'true');
        link.append(icon, document.createTextNode(title));
        actions.append(link);
    }

    if (certificate.credential_url) {
        addCertificateLink(certificate.credential_url, 'Verify credential', 'fa-arrow-up-right-from-square');
    }

    if (certificate.preview_url) {
        const url = certificate.preview_url;
        const isPdf = url.toLowerCase().split('?')[0].endsWith('.pdf');
        const preview = document.createElement('section');
        preview.className = 'portfolio-certificate-document';
        const heading = document.createElement('h3');
        heading.textContent = 'Certificate preview';
        preview.append(heading);

        if (isPdf) {
            const frame = document.createElement('iframe');
            frame.src = url + (url.includes('#') ? '' : '#page=1&toolbar=1&navpanes=0');
            frame.title = 'PDF preview of ' + (certificate.certificate_name || 'certificate');
            frame.className = 'portfolio-certificate-preview is-pdf';
            frame.loading = 'lazy';
            preview.append(frame);
        } else {
            const img = document.createElement('img');
            img.src = url;
            img.alt = 'Certificate preview';
            img.className = 'portfolio-certificate-preview is-image';
            img.loading = 'lazy';
            preview.append(img);
        }
        area.append(preview);
        addCertificateLink(url, isPdf ? 'Open full PDF' : 'Open full image', 'fa-arrow-up-right-from-square', true);
    } else {
        const missing = document.createElement('p');
        missing.className = 'portfolio-preview-notice';
        missing.textContent = 'No preview file is available for this certificate. You can upload a new one by editing it.';
        area.append(missing);
    }

    if (actions.childElementCount) area.append(actions);
    document.getElementById('certificateViewTitle').textContent = certificate.certificate_name || 'Certificate details';
    const modal = document.getElementById('certificateViewModal');
    modal.style.display = 'flex';
    area.scrollTop = 0;
}

function portfolioOpen(id) {
    const modal = document.getElementById(id);
    modal.querySelector('form').reset();
    modal.querySelector('[name="id"]').value = '';
    modal.querySelector('h2').textContent = id === 'projectModal' ? 'Add Project' : 'Add Certificate';
    modal.style.display = 'flex';
}

function portfolioClose(id) {
    document.getElementById(id).style.display = 'none';
}

function portfolioEditProject(project) {
    portfolioOpen('projectModal');
    const modal = document.getElementById('projectModal');
    modal.querySelector('h2').textContent = 'Edit Project';
    const form = modal.querySelector('form');
    for (const name of ['title', 'description', 'repository_url', 'project_status', 'start_date', 'end_date']) {
        form.elements[name].value = project[name] || '';
    }
    form.elements.id.value = project.portfolio_project_id;
    form.elements.technologies.value = (project.technologies || []).join(', ');
}

function portfolioEditCertificate(certificate) {
    portfolioOpen('certificateModal');
    const modal = document.getElementById('certificateModal');
    modal.querySelector('h2').textContent = 'Edit Certificate';
    const form = modal.querySelector('form');
    for (const name of ['certificate_name', 'issuer', 'issue_date', 'expiration_date', 'credential_id', 'credential_url']) {
        form.elements[name].value = certificate[name] || '';
    }
    form.elements.skills.value = (certificate.skills || []).join(', ');
    form.elements.id.value = certificate.certificate_id;
}

document.querySelectorAll('.portfolio-toast').forEach(toast => {
    let timer;
    function dismiss() {
        clearTimeout(timer);
        if (toast.classList.contains('is-hiding')) return;
        toast.classList.add('is-hiding');
        toast.addEventListener('transitionend', () => toast.remove(), { once: true });
        setTimeout(() => toast.remove(), 350);
    }
    toast.querySelector('.portfolio-toast-close').addEventListener('click', dismiss);
    timer = setTimeout(dismiss, Number(toast.dataset.timeout) || 4500);
});

for (const modal of document.querySelectorAll('.modal')) {
    modal.addEventListener('click', event => {
        if (event.target === modal) modal.style.display = 'none';
    });
}

document.addEventListener('keydown', event => {
    if (event.key === 'Escape') document.querySelectorAll('.modal').forEach(modal => modal.style.display = 'none');
});

</script>
<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>