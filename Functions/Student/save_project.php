<?php
require_once __DIR__ . '/project_bootstrap.php';
$error = null;
$saved = [];
try {
    $saved = $projectRepo->saved($projectEmail);
} catch (Throwable $exception) {
    error_log('Student saved projects: ' . $exception->getMessage());
    $error = 'Saved projects could not be loaded. Import the saved-projects SQL migration if needed.';
}
$pageTitle = 'Saved Projects | SkillBridge';
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/dashboard.css">'
    . '<link rel="stylesheet" href="../../Assets/CSS/Student/projects.css">'
    . '<link rel="stylesheet" href="../../Assets/CSS/Student/saved_projects.css">';
include __DIR__ . '/../../Includes/student_sidebar.php';
include __DIR__ . '/../../Includes/dash_header.php';
$images = ['project1.png', 'project2.webp', 'project3.jpg', 'project4.png', 'project5.jpg'];
?>
<main class="content saved-projects-page">
    <section class="saved-header">
        <div>
            <div class="breadcrumb"><a href="projects.php">Projects</a><span>&gt;</span><strong>Saved Projects</strong></div>
            <h1>Saved Projects</h1>
            <p>View and manage projects you saved for future applications.</p>
        </div>
        <a href="projects.php" class="btn-secondary"><i class="fa-solid fa-arrow-left"></i> Explore Projects</a>
    </section>
    <?php projectRenderToast(projectToast()); ?>
    <?php if ($error): ?><p class="project-alert" role="alert"><?= projectEsc($error) ?></p><?php endif; ?>
    <section class="saved-grid">
        <?php foreach ($saved as $project): ?>
            <?php
            $id = (int) $project['id'];
            $image = $images[($id - 1) % count($images)];
            $tags = array_filter(array_map('trim', explode(',', (string) ($project['technology_tags'] ?? ''))));
            ?>
            <article class="saved-project-card">
                <div class="saved-image">
                    <img src="../../Assets/Images/UI/projects/<?= projectEsc($image) ?>" alt="" loading="lazy">
                    <span><?= projectEsc($project['difficulty']) ?></span>
                </div>
                <div class="saved-content">
                    <small><?= projectEsc($project['organization_name']) ?></small>
                    <h2><?= projectEsc($project['title']) ?></h2>
                    <p><?= projectEsc(mb_strimwidth((string) ($project['description'] ?? ''), 0, 165, '...')) ?></p>
                    <div class="saved-tags"><?php foreach (array_slice($tags, 0, 5) as $tag): ?><span><?= projectEsc($tag) ?></span><?php endforeach; ?></div>
                    <div class="saved-actions">
                        <a class="btn-outline" href="view_project.php?id=<?= $id ?>">View Details</a>
                        <form method="post" action="project_action.php">
                            <input type="hidden" name="csrf" value="<?= projectEsc($projectCsrf) ?>">
                            <input type="hidden" name="project_id" value="<?= $id ?>">
                            <input type="hidden" name="action" value="toggle_save">
                            <input type="hidden" name="return_to" value="save_project.php">
                            <button class="remove-btn" type="submit"><i class="fa-solid fa-bookmark"></i> Remove</button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
    <?php if (!$saved): ?><p class="project-empty">No saved projects yet. Explore projects to bookmark one.</p><?php endif; ?>
</main>
<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>
