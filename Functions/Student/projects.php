<?php
require_once __DIR__ . '/project_bootstrap.php';

$filterStatus = strtolower(trim((string) ($_GET['filter_status'] ?? 'all')));
if (!in_array($filterStatus, ['all', 'active', 'not_applied'], true)) {
    $filterStatus = 'all';
}

$filters = [
    'filter_status' => $filterStatus,
    'category' => trim((string) ($_GET['category'] ?? '')),
    'difficulty' => trim((string) ($_GET['difficulty'] ?? ''))
];
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 9;
$error = null;
$stats = ['available' => 0, 'active' => 0, 'completed' => 0];
$categories = $projects = $savedIds = [];
$total = 0;
try {
    $stats = $projectRepo->stats($projectEmail);
    $categories = $projectRepo->categories();
    $total = $projectRepo->count($filters, $projectEmail);
    $page = min($page, max(1, (int) ceil($total / $limit)));
    $projects = $projectRepo->list($filters, $page, $limit, $projectEmail);
    $savedIds = $projectRepo->savedIds($projectEmail);
} catch (Throwable $exception) {
    error_log('Student projects listing: ' . $exception->getMessage());
    $error = 'Projects could not be loaded. Check your database connection and schema.';
}
$pageTitle = 'Explore Projects | SkillBridge';
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/dashboard.css">'
    . '<link rel="stylesheet" href="../../Assets/CSS/Student/projects.css?v=' . filemtime(__DIR__ . '/../../Assets/CSS/Student/projects.css') . '">';
include __DIR__ . '/../../Includes/student_sidebar.php';
include __DIR__ . '/../../Includes/dash_header.php';
$images = ['project1.png', 'project2.webp', 'project3.jpg', 'project4.png', 'project5.jpg'];
?>
<main class="content projects-page">
    <section class="projects-header">
        <div>
            <h1>Explore Projects</h1>
            <p>Discover real-world internships and collaborative tech projects from global organizations.</p>
        </div>
        <div class="project-header-actions">
            <a href="save_project.php" class="btn-secondary"><i class="fa-solid fa-bookmark"></i> Saved Projects</a>
        </div>
    </section>
    <?php projectRenderToast(projectToast()); ?>
    <?php if ($error): ?><p class="project-alert" role="alert"><?= projectEsc($error) ?></p><?php endif; ?>
    <section class="project-stats">
        <div class="stat-card"><div class="stat-icon blue"><i class="fa-solid fa-folder-open"></i></div><div><span>Total Projects</span><h2><?= (int) $stats['available'] ?></h2></div></div>
        <div class="stat-card"><div class="stat-icon orange"><i class="fa-solid fa-spinner"></i></div><div><span>Active Projects</span><h2><?= (int) $stats['active'] ?></h2></div></div>
        <div class="stat-card"><div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div><div><span>Completed Projects</span><h2><?= (int) $stats['completed'] ?></h2></div></div>
    </section>
    <form class="project-toolbar" method="get" action="projects.php">
        <div class="filter-group">
            <label class="filter-label" for="statusFilter">Filters:</label>
            <select class="filter-select" id="statusFilter" name="filter_status">
                <option value="all" <?= $filters['filter_status'] === 'all' ? 'selected' : '' ?>>Projects: All Projects</option>
                <option value="active" <?= $filters['filter_status'] === 'active' ? 'selected' : '' ?>>Active Projects</option>
                <option value="not_applied" <?= $filters['filter_status'] === 'not_applied' ? 'selected' : '' ?>>Not Applied Yet</option>
            </select>
            <select class="filter-select" id="categoryFilter" name="category">
                <option value="">Category: All</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= projectEsc($category) ?>" <?= $filters['category'] === $category ? 'selected' : '' ?>><?= projectEsc($category) ?></option>
                <?php endforeach; ?>
            </select>
            <select class="filter-select" id="difficultyFilter" name="difficulty">
                <option value="">Difficulty: All</option>
                <?php foreach (['Beginner', 'Intermediate', 'Advanced'] as $difficulty): ?>
                    <option value="<?= $difficulty ?>" <?= $filters['difficulty'] === $difficulty ? 'selected' : '' ?>><?= $difficulty ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="toolbar-right">
            <button type="submit" class="btn-primary project-apply-filter">Apply Filters</button>
            <a class="clear-filter" href="projects.php"><i class="fa-solid fa-filter-circle-xmark"></i> Clear Filters</a>
        </div>
    </form>
    <section class="projects-grid">
        <?php foreach ($projects as $project): ?>
            <?php
            $id = (int) $project['id'];
            $projectImage = $images[($id - 1) % count($images)];
            $tags = array_filter(array_map('trim', explode(',', (string) ($project['technology_tags'] ?? ''))));
            $isActiveWorking = (int) ($project['is_student_active'] ?? 0) === 1;
            $isApplied = (int) ($project['is_student_applied'] ?? 0) === 1;
            $teamId = (int) ($project['student_team_id'] ?? 0);

            if ($isActiveWorking) {
                $viewHref = $teamId > 0 ? "view_team.php?id={$teamId}" : "teams.php?project_id={$id}";
            } else {
                $viewHref = "view_project.php?id={$id}";
            }
            ?>
            <article class="project-card"
                     data-category="<?= projectEsc($project['category_name']) ?>"
                     data-difficulty="<?= projectEsc($project['difficulty']) ?>"
                     <?php if ($isActiveWorking): ?>
                         onclick="if(!event.target.closest('a,button,form')){ window.location.href='<?= projectEsc($viewHref) ?>'; }"
                         style="cursor:pointer;"
                     <?php endif; ?>>
                <div class="project-image">
                    <img src="../../Assets/Images/Student/Projects/<?= projectEsc($projectImage) ?>" alt="" loading="lazy">
                    <span class="difficulty"><?= projectEsc($project['difficulty']) ?></span>
                </div>
                <div class="project-content">
                    <small><?= projectEsc($project['organization_name']) ?></small>
                    <h2><?= projectEsc($project['title']) ?></h2>
                    <p><?= projectEsc(mb_strimwidth((string) ($project['description'] ?? ''), 0, 155, '...')) ?></p>
                    <div class="tech-tags">
                        <?php foreach (array_slice($tags, 0, 5) as $tag): ?><span><?= projectEsc($tag) ?></span><?php endforeach; ?>
                    </div>
                    <div class="project-info">
                        <div><i class="fa-regular fa-clock"></i> <?= projectEsc($project['duration_label']) ?></div>
                        <div><i class="fa-regular fa-calendar"></i> <?= projectEsc($project['deadline']) ?></div>
                    </div>
                    <div class="project-actions">
                        <?php if ($isActiveWorking): ?>
                            <a href="<?= projectEsc($viewHref) ?>" class="btn-outline" style="width:100%;"><i class="fa-solid fa-eye"></i> View Project</a>
                        <?php else: ?>
                            <a href="view_project.php?id=<?= $id ?>" class="btn-outline"><i class="fa-solid fa-eye"></i> View Project</a>
                            <?php if (in_array($id, $savedIds, true)): ?><span class="project-saved-label" title="Saved"><i class="fa-solid fa-bookmark"></i></span><?php endif; ?>
                            <?php if (!$isApplied): ?>
                                <a href="proposal.php?project_id=<?= $id ?>" class="btn-primary">Apply Now</a>
                            <?php else: ?>
                                <span class="project-saved-label">Applied</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
    <?php if (!$projects): ?><p class="project-empty">No matching projects found.</p><?php endif; ?>
    <?php $pages = max(1, (int) ceil($total / $limit)); ?>
    <?php if ($pages > 1): ?>
        <nav class="pagination" aria-label="Project pages">
            <?php for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): ?>
                <?php $qs = http_build_query(array_merge($filters, ['page' => $i])); ?>
                <a class="<?= $i === $page ? 'active' : '' ?>" href="?<?= projectEsc($qs) ?>"><?= $i ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>
