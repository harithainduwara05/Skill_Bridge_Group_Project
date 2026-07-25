<?php
require_once __DIR__ . '/../Config/db.php';

$limit = 4; // Number of projects per page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $limit;

// Total count for pagination
$sql_count = "SELECT COUNT(*) as total FROM projects";
$result_count = $conn->query($sql_count);
$total_projects = ($result_count) ? $result_count->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_projects / $limit);

// Fetch projects for the current page
$sql_projects = "SELECT * FROM projects LIMIT $limit OFFSET $offset";
$result_projects = $conn->query($sql_projects);

$page_css = "Assets/CSS/projects.css";
include '../Includes/header.php';
?>

<div class="projects-container">
    <!-- Left Column -->
    <div class="projects-main">

        <div class="projects-header-card">
            <h1>Explore Real-World Projects</h1>
            <p>Collaborate with organizations and apply your skills to meaningful projects that bridge the gap between
                study and industry.</p>
        </div>

        <div class="filter-bar">
            <div class="filter-group search">
                <label>Search Projects</label>
                <input type="text" placeholder="🔍 Search projects...">
            </div>
            <div class="filter-group">
                <label>Category</label>
                <select>
                    <option>All Categories</option>
                    <option>Web Development</option>
                    <option>Data Science</option>
                    <option>UI/UX Design</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Difficulty</label>
                <select>
                    <option>Any Level</option>
                    <option>Beginner</option>
                    <option>Intermediate</option>
                    <option>Advanced</option>
                </select>
            </div>
        </div>

        <div class="projects-grid">
            <?php if (isset($result_projects) && $result_projects->num_rows > 0): ?>
                <?php while ($project = $result_projects->fetch_assoc()): ?>
                    <div class="project-card">
                        <div class="project-icon"><?php echo htmlspecialchars($project['icon'] ?? '📁'); ?></div>
                        <?php if (!empty($project['tag'])): ?>
                            <div class="tag <?php echo htmlspecialchars($project['tag_class'] ?? ''); ?>">
                                <?php echo htmlspecialchars($project['tag']); ?></div>
                        <?php endif; ?>
                        <h3 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h3>
                        <div class="project-company"><?php echo htmlspecialchars($project['company']); ?></div>

                        <div class="tech-stack">
                            <?php
                            if (!empty($project['tech_stack'])) {
                                $techs = explode(',', $project['tech_stack']);
                                foreach ($techs as $tech):
                                    ?>
                                    <span class="tech-badge"><?php echo htmlspecialchars(trim($tech)); ?></span>
                                <?php
                                endforeach;
                            }
                            ?>
                        </div>

                        <div class="project-meta">
                            <span>🕒 <?php echo htmlspecialchars($project['duration']); ?></span>
                            <span>👥 <?php echo htmlspecialchars($project['members']); ?> Members</span>
                        </div>

                        <div class="project-deadline">
                            📅 Deadline: <?php echo htmlspecialchars($project['deadline']); ?>
                        </div>

                        <div class="project-actions">
                            <button class="btn-outline" onclick="window.location.href='../Auth/login.php'">View Details</button>
                            <button class="btn-fill" onclick="window.location.href='../Auth/login.php'">Apply Now</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="grid-column: span 2; color: #555;">No projects available at the moment.</p>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="page-btn" style="text-decoration:none;">&lt;</a>
                <?php else: ?>
                    <div class="page-btn" style="opacity: 0.5; cursor: not-allowed;">&lt;</div>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="page-btn <?php echo ($page == $i) ? 'active' : ''; ?>"
                        style="text-decoration:none;"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="page-btn" style="text-decoration:none;">&gt;</a>
                <?php else: ?>
                    <div class="page-btn" style="opacity: 0.5; cursor: not-allowed;">&gt;</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- Right Sidebar -->
    <div class="projects-sidebar">
        <div class="sidebar-header">
            <h3>Project Details</h3>
            <p>Select a project to see preview</p>
        </div>

        <div class="sidebar-content">
            <div class="sidebar-section">
                <div class="sidebar-label">SELECTED PROJECT</div>
                <div class="sidebar-title">AI Model Optimization</div>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-label">DESCRIPTION</div>
                <div class="sidebar-desc">
                    Optimize existing machine learning models for edge deployment. Focus on reducing latency and
                    footprint without compromising accuracy for real-time video processing.
                </div>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-label">REQUIRED SKILLS</div>
                <ul class="skills-list">
                    <li>Deep Learning</li>
                    <li>PyTorch/TensorFlow</li>
                    <li>Model Quantization</li>
                </ul>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-label">APPLICATION STATUS</div>
                <div class="status-box">
                    <span class="status-text">Open Seats</span>
                    <span class="status-value">2/4</span>
                </div>
            </div>

            <button class="btn-submit-app" onclick="window.location.href='../Auth/login.php'">Submit
                Application</button>
        </div>
    </div>
</div>

<?php include '../Includes/footer.php'; ?>