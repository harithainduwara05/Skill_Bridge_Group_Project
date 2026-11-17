<?php
require_once __DIR__ . '/project_bootstrap.php';
$id = projectValidId($_GET['project_id'] ?? null);
$project = $id ? $projectRepo->find($id) : null;
if (!$project) {
    http_response_code(404);
    exit('Project not found or unavailable.');
}
$applied = $projectRepo->alreadyApplied($projectEmail, $id);
$tags = array_filter(array_map('trim', explode(',', (string) ($project['technology_tags'] ?? ''))));
$pageTitle = 'Submit Proposal | SkillBridge';
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/dashboard.css">'
    . '<link rel="stylesheet" href="../../Assets/CSS/Student/projects.css">'
    . '<link rel="stylesheet" href="../../Assets/CSS/Student/proposal.css">';
include __DIR__ . '/../../Includes/student_sidebar.php';
include __DIR__ . '/../../Includes/dash_header.php';
?>
<main class="content proposal-page">
    <?php projectRenderToast(projectToast()); ?>
    <div class="proposal-header">
        <div class="breadcrumb"><a href="projects.php">Projects</a><span>&gt;</span><a href="view_project.php?id=<?= $id ?>">View Project</a><span>&gt;</span><strong>Submit Proposal</strong></div>
        <h1>New Proposal Submission</h1>
        <p>Detailed approach and portfolio attachment for this project opportunity.</p>
    </div>
    <?php if ($applied): ?>
        <p class="project-alert">You have already submitted an application for this project.</p>
        <a class="btn-secondary" href="view_project.php?id=<?= $id ?>">Back to Project</a>
    <?php else: ?>
        <form action="project_action.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= projectEsc($projectCsrf) ?>">
            <input type="hidden" name="action" value="apply">
            <input type="hidden" name="project_id" value="<?= $id ?>">
            <div class="proposal-layout">
                <div class="proposal-left">
                    <div class="proposal-card">
                        <label for="proposalTitle">Proposal Title</label>
                        <input id="proposalTitle" name="title" type="text" maxlength="255" placeholder="e.g. Building an AI Dashboard System" required>
                        <small>A concise name for your application approach.</small>
                        <label for="proposalIntro">Description</label>
                        <textarea id="proposalIntro" name="introduction" rows="5" placeholder="Introduce yourself and explain why you are a good fit..." required></textarea>
                        <label for="proposalApproach">Solution Approach</label>
                        <textarea id="proposalApproach" name="approach" rows="6" placeholder="Explain your workflow, tools, and methodology..." required></textarea>
                    </div>
                    <div class="bottom-grid">
                        <div class="mini-card proposal-skills-card">
                            <h3>Technologies Used</h3>
                            <div class="skill-tags"><?php foreach ($tags as $tag): ?><span><?= projectEsc($tag) ?></span><?php endforeach; ?></div>
                            <label for="proposalSkills">Your Relevant Skills</label>
                            <input id="proposalSkills" class="proposal-skills-input" type="text" name="skills" placeholder="PHP, MySQL, React" aria-label="Relevant skills separated by commas">
                        </div>
                        <div class="mini-card">
                            <h3>Role Interested In</h3>
                            <select name="role" class="role-select" required>
                                <option value="">Select project role...</option>
                                <?php foreach (['Frontend Developer', 'Backend Engineer', 'UI/UX Designer', 'Data Analyst', 'AI Engineer', 'Project Manager', 'QA Engineer', 'Other Contributor'] as $role): ?>
                                    <option value="<?= projectEsc($role) ?>"><?= projectEsc($role) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="proposal-right">
                    <div class="upload-card">
                        <h2><i class="fa-solid fa-paperclip"></i> Attachments</h2>
                        <p>Attach a resume, portfolio, or supporting document (PDF, JPG, PNG, or WebP, up to 5 MB).</p>
                        <label class="upload-box" for="proposalAttachment">
                            <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                            <strong>Choose an attachment</strong>
                            <small id="proposalFileLabel">Click to select a PDF or image</small>
                            <input id="proposalAttachment" name="portfolio_attachment" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp">
                        </label>
                        <button class="proposal-clear-file" id="proposalClearFile" type="button" hidden>Remove selected file</button>
                    </div>
                    <div class="summary-card">
                        <h3>Submission Summary</h3>
                        <p><?= projectEsc($project['title']) ?></p>
                        <button type="submit" class="submit-btn">Submit Proposal <i class="fa-solid fa-paper-plane"></i></button>
                        <small>By submitting, you agree to our Student Professional Standards.</small>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</main>
<script>
const proposalFileInput = document.getElementById('proposalAttachment');
const proposalFileLabel = document.getElementById('proposalFileLabel');
const proposalClearFile = document.getElementById('proposalClearFile');

if (proposalFileInput) {
    proposalFileInput.addEventListener('change', () => {
        const file = proposalFileInput.files[0];
        if (!file) {
            proposalFileLabel.textContent = 'Click to select a PDF or image';
            proposalClearFile.hidden = true;
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert('File must be 5 MB or smaller.');
            proposalFileInput.value = '';
            proposalFileLabel.textContent = 'Click to select a PDF or image';
            proposalClearFile.hidden = true;
            return;
        }
        proposalFileLabel.textContent = file.name;
        proposalClearFile.hidden = false;
    });
    proposalClearFile.addEventListener('click', () => {
        proposalFileInput.value = '';
        proposalFileLabel.textContent = 'Click to select a PDF or image';
        proposalClearFile.hidden = true;
    });
}
</script>
<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>
