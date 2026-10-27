<?php
require_once __DIR__ . '/project_bootstrap.php';
$id = projectValidId($_GET['id'] ?? null);
$project = $id ? $projectRepo->find($id) : null;
if (!$project) {
    http_response_code(404);
    exit('Project not found or unavailable.');
}
$isSaved = in_array($id, $projectRepo->savedIds($projectEmail), true);
$isApplied = $projectRepo->alreadyApplied($projectEmail, $id);
$tags = array_filter(array_map('trim', explode(',', (string) ($project['technology_tags'] ?? ''))));
$organization = (string) ($project['organization_name'] ?? 'Organization');
$words = preg_split('/\s+/', trim($organization));
$initials = strtoupper(implode('', array_map(static fn ($word) => mb_substr($word, 0, 1), array_slice($words, 0, 2))));
$pageTitle = 'Project Details | SkillBridge';
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/dashboard.css">'
    . '<link rel="stylesheet" href="../../Assets/CSS/Student/projects.css">'
    . '<link rel="stylesheet" href="../../Assets/CSS/Student/view_project.css">';
include __DIR__ . '/../../Includes/student_sidebar.php';
include __DIR__ . '/../../Includes/dash_header.php';
?>
<main class="content view-project-page">
    <?php projectRenderToast(projectToast()); ?>
    <div class="breadcrumb"><a href="projects.php">Projects</a><span>&gt;</span><strong>View Project</strong></div>
    <section class="project-header-card">
        <div class="project-detail-info">
            <div class="project-tags"><span><?= projectEsc($project['category_name']) ?></span><span class="active-tag"><?= projectEsc(ucwords((string) $project['status'])) ?></span></div>
            <h1><?= projectEsc($project['title']) ?></h1>
            <p><?= projectEsc($project['description']) ?></p>
        </div>
        <div class="project-buttons">
            <?php if (!$isApplied): ?>
                <a href="proposal.php?project_id=<?= $id ?>" class="proposal-btn"><i class="fa-solid fa-paper-plane"></i> Submit Proposal</a>
            <?php else: ?>
                <span class="project-saved-label">Already Applied</span>
            <?php endif; ?>
            <form method="post" action="project_action.php">
                <input type="hidden" name="csrf" value="<?= projectEsc($projectCsrf) ?>">
                <input type="hidden" name="project_id" value="<?= $id ?>">
                <input type="hidden" name="action" value="toggle_save">
                <input type="hidden" name="return_to" value="view_project.php">
                <button class="save-btn" type="submit"><i class="fa-<?= $isSaved ? 'solid' : 'regular' ?> fa-bookmark"></i> <?= $isSaved ? 'Remove Saved' : 'Save Project' ?></button>
            </form>
        </div>
    </section>
    <section class="project-stat-wrapper">
        <div class="project-stat"><div class="stat-circle"><i class="fa-regular fa-clock"></i></div><div><label>Duration</label><strong><?= projectEsc($project['duration_label']) ?></strong></div></div>
        <div class="project-stat"><div class="stat-circle"><i class="fa-solid fa-users"></i></div><div><label>Team Size</label><strong><?= (int) $project['members'] ?> Members</strong></div></div>
        <div class="project-stat"><div class="stat-circle"><i class="fa-regular fa-calendar"></i></div><div><label>Deadline</label><strong><?= projectEsc($project['deadline']) ?></strong></div></div>
    </section>
    <div class="project-content-grid">
        <div>
            <section class="info-card">
                <h2><i class="fa-solid fa-circle-info"></i> Project Overview</h2>
                <p><?= nl2br(projectEsc($project['description'])) ?></p>
                <h3>Key Objectives</h3>
                <ul>
                    <?php if (!empty($project['learning_objectives'])): ?><li><?= projectEsc($project['learning_objectives']) ?></li><?php endif; ?>
                    <?php if (!empty($project['expected_outcomes'])): ?><li><?= projectEsc($project['expected_outcomes']) ?></li><?php endif; ?>
                </ul>
            </section>
        </div>
        <div>
            <section class="info-card">
                <h2><i class="fa-solid fa-code"></i> Required Skills</h2>
                <?php foreach ($tags as $tag): ?><span class="skill-pill"><?= projectEsc($tag) ?></span><?php endforeach; ?>
            </section>
            <section class="organization-card">
                <div class="organization-header">
                    <div class="organization-logo"><span><?= projectEsc($initials) ?></span></div>
                    <div><h3><?= projectEsc($organization) ?></h3><p><i class="fa-solid fa-building"></i> Project Organization</p></div>
                </div>
                <p class="organization-description">Explore this organization's project opportunities and collaborate through SkillBridge.</p>
            </section>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>
