<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role('organization');
$user = current_user();
$organization_email = $user['email'];

// ---- Load the project (must belong to this organization) ----
$projectId = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ? AND organization_email = ?");
$stmt->bind_param("is", $projectId, $organization_email);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

if (!$project) {
    header("Location: manage_projects.php");
    exit;
}

$flash = null;

// ---- Handle update ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title               = trim($_POST['title'] ?? '');
    $category            = trim($_POST['category'] ?? '');
    $keywords            = trim($_POST['keywords'] ?? '');
    $description         = trim($_POST['description'] ?? '');
    $learning_objectives = trim($_POST['learning_objectives'] ?? '');
    $expected_outcomes   = trim($_POST['expected_outcomes'] ?? '');
    $difficulty          = trim($_POST['difficulty'] ?? 'Intermediate');
    $duration_weeks      = trim($_POST['duration_weeks'] ?? '');
    $students_required   = (int)($_POST['students_required'] ?? 1);
    $preferred_year      = trim($_POST['preferred_year'] ?? 'Any Year');
    $deadline            = trim($_POST['deadline'] ?? '');
    $visibility          = trim($_POST['visibility'] ?? 'Public');
    $status              = trim($_POST['status'] ?? $project['status']);

    $duration_text = $duration_weeks !== '' ? $duration_weeks . ' Weeks' : null;

    if (empty($title) || empty($category) || empty($description)) {
        $flash = ['type' => 'error', 'message' => 'Please fill Title, Category and Description.'];
    } else {
        try {
            $tech_stack = $keywords; // reused for landing-page display cards

            $sql = "UPDATE projects SET
                        title = ?, category = ?, keywords = ?, description = ?,
                        learning_objectives = ?, expected_outcomes = ?, difficulty = ?,
                        duration = ?, members = ?, preferred_year = ?, deadline = ?,
                        visibility = ?, status = ?, tech_stack = ?
                    WHERE id = ? AND organization_email = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssssisssssis",
                $title, $category, $keywords, $description,
                $learning_objectives, $expected_outcomes, $difficulty,
                $duration_text, $students_required, $preferred_year, $deadline,
                $visibility, $status, $tech_stack,
                $projectId, $organization_email
            );

            if ($stmt->execute()) {
                header("Location: manage_projects.php?updated=1");
                exit;
            } else {
                $flash = ['type' => 'error', 'message' => 'Failed to update project.'];
            }
        } catch (Exception $e) {
            $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Keep the (unsaved) posted values on screen if validation failed
    $project = array_merge($project, [
        'title' => $title, 'category' => $category, 'keywords' => $keywords,
        'description' => $description, 'learning_objectives' => $learning_objectives,
        'expected_outcomes' => $expected_outcomes, 'difficulty' => $difficulty,
        'duration' => $duration_text, 'members' => $students_required,
        'preferred_year' => $preferred_year, 'deadline' => $deadline,
        'visibility' => $visibility, 'status' => $status,
    ]);
}

// Extract just the leading number out of "12 Weeks" for the number input
preg_match('/\d+/', $project['duration'] ?? '', $m);
$durationWeeksValue = $m[0] ?? '';

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";

?>

<main class="content">
    <div class="dashboard-header">
        <div>
            <h1>Edit Project</h1>
            <p>Update the details of your posted project.</p>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
    <div class="flash-toast flash-<?= htmlspecialchars($flash['type']) ?>" style="margin:0 28px 16px;">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif; ?>

    <form id="editProjectForm" action="edit_project.php?id=<?= (int)$project['id'] ?>" method="POST">

        <div class="post-form-card">

            <!-- ===================== PROJECT BASICS ===================== -->
            <div class="post-section">
                <div class="post-section-title">
                    <span class="material-symbols-outlined">info</span>
                    Project Basics
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Project Title</label>
                        <input type="text" name="title" class="form-input" value="<?= htmlspecialchars($project['title']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Project Category</label>
                        <select name="category" class="form-select" required>
                            <option value="">Select a category</option>
                            <?php
                            $categories = ['Web Development', 'Mobile Development', 'AI / Machine Learning', 'Data Science', 'UI/UX Design', 'Cloud & DevOps', 'Cybersecurity', 'Other'];
                            foreach ($categories as $cat):
                            ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= ($project['category'] === $cat) ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Required Skills</label>
                    <div class="chip-input-box" id="chipBox">
                        <input type="text" id="chipInput" placeholder="Type and press enter...">
                    </div>
                    <input type="hidden" name="keywords" id="keywordsHidden" value="<?= htmlspecialchars($project['keywords'] ?? '') ?>">
                    <div class="form-hint">Press Enter after each skill (e.g. React, Python, Figma).</div>
                </div>
            </div>

            <!-- ===================== PROJECT DETAILS ===================== -->
            <div class="post-section">
                <div class="post-section-title">
                    <span class="material-symbols-outlined">description</span>
                    Project Details
                </div>

                <div class="form-group">
                    <label class="form-label">Project Description</label>
                    <textarea name="description" class="form-textarea" required><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Learning Objectives</label>
                        <textarea name="learning_objectives" class="form-textarea"><?= htmlspecialchars($project['learning_objectives'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Expected Outcomes</label>
                        <textarea name="expected_outcomes" class="form-textarea"><?= htmlspecialchars($project['expected_outcomes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- ===================== LOGISTICS & REQUIREMENTS ===================== -->
            <div class="post-section">
                <div class="post-section-title">
                    <span class="material-symbols-outlined">settings</span>
                    Logistics &amp; Requirements
                </div>

                <div class="form-group">
                    <label class="form-label">Difficulty Level</label>
                    <div class="toggle-group" id="difficultyGroup">
                        <?php foreach (['Beginner', 'Intermediate', 'Advanced'] as $level): ?>
                            <div class="toggle-btn <?= ($project['difficulty'] === $level) ? 'selected' : '' ?>" data-value="<?= $level ?>"><?= $level ?></div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="difficulty" id="difficultyHidden" value="<?= htmlspecialchars($project['difficulty'] ?? 'Intermediate') ?>">
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label">Project Duration (weeks)</label>
                        <input type="number" min="1" name="duration_weeks" class="form-input" value="<?= htmlspecialchars($durationWeeksValue) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. of Students Required</label>
                        <input type="number" min="1" name="students_required" class="form-input" value="<?= (int)($project['members'] ?? 1) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Preferred Academic Year</label>
                        <select name="preferred_year" class="form-select">
                            <?php foreach (['Any Year', 'Year 1', 'Year 2', 'Year 3', 'Year 4'] as $yr): ?>
                                <option value="<?= $yr ?>" <?= ($project['preferred_year'] === $yr) ? 'selected' : '' ?>><?= $yr ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Application Deadline</label>
                        <input type="date" name="deadline" class="form-input" value="<?= htmlspecialchars($project['deadline'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Project Visibility</label>
                        <div class="radio-inline">
                            <label class="radio-option">
                                <input type="radio" name="visibility" value="Public" <?= ($project['visibility'] === 'Public') ? 'checked' : '' ?>>
                                Public <span style="color:#9ca3af; font-weight:400;">(Visible to all students)</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="visibility" value="Private" <?= ($project['visibility'] === 'Private') ? 'checked' : '' ?>>
                                Private <span style="color:#9ca3af; font-weight:400;">(Invite only)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Project Status</label>
                    <select name="status" class="form-select">
                        <?php foreach (['open' => 'Open', 'reviewing' => 'Reviewing', 'inprogress' => 'Active', 'closed' => 'Closed'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($project['status'] === $val) ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- ===================== FOOTER ACTIONS ===================== -->
            <div class="post-form-footer">
                <div class="footer-left">
                    <a href="manage_projects.php" class="btn-outline">Cancel</a>
                </div>
                <button type="submit" class="btn-solid">
                    <span class="material-symbols-outlined" style="font-size:16px;">save</span>
                    Save Changes
                </button>
            </div>

        </div>
    </form>

</main>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<script>
    // Pre-fill skill chips from existing keywords, then reuse the same chip logic as post-project.js
    document.addEventListener('DOMContentLoaded', function () {
        const chipBox = document.getElementById('chipBox');
        const chipInput = document.getElementById('chipInput');
        const keywordsHidden = document.getElementById('keywordsHidden');
        const existing = keywordsHidden.value.split(',').map(s => s.trim()).filter(Boolean);

        function addChip(text) {
            const chip = document.createElement('span');
            chip.className = 'chip';
            chip.textContent = text;
            const remove = document.createElement('span');
            remove.textContent = ' \u00d7';
            remove.style.cursor = 'pointer';
            remove.onclick = function () {
                chip.remove();
                updateHidden();
            };
            chip.appendChild(remove);
            chipBox.insertBefore(chip, chipInput);
        }

        function updateHidden() {
            const chips = Array.from(chipBox.querySelectorAll('.chip')).map(c => c.firstChild.textContent);
            keywordsHidden.value = chips.join(', ');
        }

        existing.forEach(addChip);

        chipInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && chipInput.value.trim() !== '') {
                e.preventDefault();
                addChip(chipInput.value.trim());
                chipInput.value = '';
                updateHidden();
            }
        });

        // Difficulty toggle
        const difficultyGroup = document.getElementById('difficultyGroup');
        const difficultyHidden = document.getElementById('difficultyHidden');
        if (difficultyGroup) {
            difficultyGroup.querySelectorAll('.toggle-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    difficultyGroup.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('selected'));
                    btn.classList.add('selected');
                    difficultyHidden.value = btn.dataset.value;
                });
            });
        }
    });
</script>

<?php include "../../../Includes/dash_footer.php"; ?>