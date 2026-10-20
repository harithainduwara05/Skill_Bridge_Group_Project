<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../Config/db.php';
include '../../Session/Session.php';

require_once __DIR__ . '/../../Backend/StudentBackend.php';
require_once __DIR__ . '/profile_completion.php';

require_role('student');

$user = current_user();

if (!$user) {
    die('Session expired');
}

$email = $user['Email'] ?? $user['email'] ?? null;

if (!$email) {
    die('Email not found');
}

$studentManager = new StudentManager();

$skillCount = $studentManager->getSkillCount($email);
$certificateCount = $studentManager->getCertificateCount($email);
$projectCount = 0;

/** Experience-based level policy used consistently by PHP and JavaScript. */
function skillLevelForYears(int $years): string
{
    if ($years <= 1) {
        return 'Beginner';
    }
    if ($years <= 3) {
        return 'Intermediate';
    }
    if ($years <= 5) {
        return 'Advanced';
    }

    return 'Expert';
}

function validExperienceYears($years): bool
{
    return filter_var($years, FILTER_VALIDATE_INT) !== false
        && (int) $years >= 0
        && (int) $years <= 30;
}

/** Full years elapsed since the skill was added. */
function elapsedSkillYears($createdAt): int
{
    try {
        $created = new DateTime($createdAt);
        $today = new DateTime();

        if ($created > $today) {
            return 0;
        }

        return $created->diff($today)->y;
    } catch (Exception $e) {
        return 0;
    }
}

function totalSkillYears($initial, $createdAt): int
{
    return min(30, max(1, (int) $initial) + elapsedSkillYears($createdAt));
}

// -----------------------------------------------------------------------------
// ADD SKILL
// -----------------------------------------------------------------------------
if (isset($_POST['save_skill'])) {
    $skill_name = trim($_POST['skill_name']);
    // Category is not collected from students; retain database compatibility.
    $category = 'Other';
    $initial_experience = $_POST['initial_experience'] ?? '';

    if (!validExperienceYears($initial_experience)) {
        $_SESSION['skill_error'] = 'Experience must be a whole number from 0 to 30.';
        header('Location: skills.php');
        exit();
    }

    $initial_experience = max(1, (int) $initial_experience);
    $level = skillLevelForYears($initial_experience);
    $percentage = calculatePercentage($level);

    // Check for a duplicate skill belonging to this student.
    $check = $conn->prepare(
        'SELECT skill_id
         FROM skills
         WHERE Email = ?
           AND LOWER(skill_name) = LOWER(?)'
    );
    $check->bind_param('ss', $email, $skill_name);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['skill_error'] = 'You already added this skill.';
        header('Location: skills.php');
        exit();
    }

    $sql = 'INSERT INTO skills
                (Email, skill_name, category, level, initial_experience, percentage)
            VALUES (?, ?, ?, ?, ?, ?)';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        'ssssii',
        $email,
        $skill_name,
        $category,
        $level,
        $initial_experience,
        $percentage
    );
    $stmt->execute();

    header('Location: skills.php');
    exit();
}

// -----------------------------------------------------------------------------
// UPDATE SKILL
// -----------------------------------------------------------------------------
if (isset($_POST['update_skill'])) {
    $skill_id = $_POST['skill_id'];
    $skill_name = trim($_POST['skill_name']);
    $initial_experience = $_POST['initial_experience'] ?? '';

    if (!validExperienceYears($initial_experience)) {
        $_SESSION['skill_error'] = 'Experience must be a whole number from 0 to 30.';
        header('Location: skills.php');
        exit();
    }

    $initial_experience = max(1, (int) $initial_experience);
    $level = skillLevelForYears($initial_experience);
    $percentage = calculatePercentage($level);

    // Convert displayed total experience back to the original baseline.
    $originalSkill = $conn->prepare(
        'SELECT created_at FROM skills WHERE skill_id = ? AND Email = ?'
    );
    $originalSkill->bind_param('is', $skill_id, $email);
    $originalSkill->execute();
    $originalRow = $originalSkill->get_result()->fetch_assoc();

    if (!$originalRow) {
        $_SESSION['skill_error'] = 'Skill not found.';
        header('Location: skills.php');
        exit();
    }

    $elapsedYears = elapsedSkillYears($originalRow['created_at']);

    if ($initial_experience < $elapsedYears) {
        $_SESSION['skill_error'] = 'Total experience cannot be less than the full years since this skill was added ('
            . $elapsedYears . ').';
        header('Location: skills.php');
        exit();
    }

    $initial_experience = $initial_experience - $elapsedYears;

    // Check for another skill with the same name.
    $check = $conn->prepare(
        'SELECT skill_id
         FROM skills
         WHERE Email = ?
           AND LOWER(skill_name) = LOWER(?)
           AND skill_id != ?'
    );
    $check->bind_param('ssi', $email, $skill_name, $skill_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['skill_error'] = 'This skill already exists.';
        header('Location: skills.php');
        exit();
    }

    $sql = 'UPDATE skills
            SET skill_name = ?, level = ?, initial_experience = ?, percentage = ?
            WHERE skill_id = ? AND Email = ?';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        'ssiiis',
        $skill_name,
        $level,
        $initial_experience,
        $percentage,
        $skill_id,
        $email
    );
    $stmt->execute();

    header('Location: skills.php');
    exit();
}

// -----------------------------------------------------------------------------
// DELETE SKILL
// -----------------------------------------------------------------------------
if (isset($_POST['delete_skill'])) {
    $skill_id = $_POST['skill_id'];

    $stmt = $conn->prepare(
        'DELETE FROM skills WHERE skill_id = ? AND Email = ?'
    );
    $stmt->bind_param('is', $skill_id, $email);
    $stmt->execute();

    header('Location: skills.php');
    exit();
}

function calculateExperience($initial, $created_at)
{
    $years = totalSkillYears($initial, $created_at);
    return $years === 1 ? '1 Year' : $years . ' Years';
}

function calculatePercentage($level)
{
    $levels = [
        'Beginner' => 40,
        'Intermediate' => 60,
        'Advanced' => 80,
        'Expert' => 95,
    ];

    return $levels[$level] ?? 0;
}

// -----------------------------------------------------------------------------
// LOAD STUDENT SKILLS AND STATISTICS
// -----------------------------------------------------------------------------
$student = $studentManager->getStudent($email);
$skills = $studentManager->getSkills($email);

// Refresh calculated experience level and percentage on page visit.
$syncSkill = $conn->prepare(
    'UPDATE skills SET level = ?, percentage = ? WHERE skill_id = ? AND Email = ?'
);

foreach ($skills as &$skill) {
    $currentYears = totalSkillYears($skill['initial_experience'], $skill['created_at']);
    $derivedLevel = skillLevelForYears($currentYears);
    $derivedPercentage = calculatePercentage($derivedLevel);

    if ($skill['level'] !== $derivedLevel || (int) $skill['percentage'] !== $derivedPercentage) {
        $syncSkill->bind_param('siis', $derivedLevel, $derivedPercentage, $skill['skill_id'], $email);
        $syncSkill->execute();
    }

    $skill['level'] = $derivedLevel;
    $skill['percentage'] = $derivedPercentage;
    $skill['current_experience'] = $currentYears;
}
unset($skill);

$totalSkills = $studentManager->getSkillCount($email);
$averagePercentage = $studentManager->getAverageSkillPercentage($email);
$profileStrength = calculateProfileCompletion(
    $student,
    $skillCount,
    $certificateCount,
    $projectCount
);


$pageTitle = 'My Skills | SkillBridge';
$extra_css = '
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../Assets/CSS/Student/skills.css">';

include '../../Includes/student_sidebar.php';
include '../../Includes/dash_header.php';
?>

<div class="skills-container">
    <!-- Header -->
    <div class="skills-header">
        <div>
            <h1>My Skills</h1>
            <p>Showcase your professional expertise and track your skill development.</p>
        </div>

        <button class="add-skill-btn" onclick="openSkillModal()">
            <i class="fa-solid fa-plus"></i> Add Skill
        </button>
    </div>

    <!-- Statistics -->
    <div class="skill-statistics">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <h2><?= $totalSkills ?></h2>
                <p>Total Skills</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <h2><?= $averagePercentage ?>%</h2>
                <p>Average Proficiency</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-award"></i>
            </div>
            <div>
                <h2><?= $profileStrength ?>%</h2>
                <p>Profile Completion</p>
            </div>
        </div>
    </div>

    <!-- All Skills -->
    <div class="section-title skills-section-heading">
        <h2>All Skills</h2>
        <div class="skill-filters" role="group" aria-label="Filter skills by level">
            <button type="button" class="skill-filter-btn active" data-filter="all" aria-pressed="true">All</button>
            <button type="button" class="skill-filter-btn" data-filter="Beginner" aria-pressed="false">Beginner</button>
            <button type="button" class="skill-filter-btn" data-filter="Intermediate" aria-pressed="false">Intermediate</button>
            <button type="button" class="skill-filter-btn" data-filter="Advanced" aria-pressed="false">Advanced</button>
            <button type="button" class="skill-filter-btn" data-filter="Expert" aria-pressed="false">Expert</button>
        </div>
    </div>

    <div class="skills-grid">
        <?php foreach ($skills as $skill): ?>
            <div class="skill-card" data-level="<?= htmlspecialchars($skill['level'], ENT_QUOTES, 'UTF-8') ?>">
                <div class="card-actions">
                    <button
                        type="button"
                        class="edit-btn"
                        onclick='editSkill(
                            <?= json_encode($skill["skill_id"]) ?>,
                            <?= json_encode($skill["skill_name"]) ?>,
                            <?= json_encode($skill["level"]) ?>,
                            <?= json_encode($skill["current_experience"]) ?>
                        )'
                    >
                        <i class="fa-solid fa-pen"></i>
                    </button>

                    <form method="POST">
                        <input type="hidden" name="skill_id" value="<?= $skill['skill_id'] ?>">
                        <button
                            type="submit"
                            name="delete_skill"
                            class="delete-btn"
                            onclick="return confirm('Delete this skill?')"
                        >
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>

                <div class="skill-icon-large">
                    <?= strtoupper(substr($skill['skill_name'], 0, 1)) ?>
                </div>

                <h3><?= htmlspecialchars($skill['skill_name']) ?></h3>
                <span class="level-badge"><?= htmlspecialchars($skill['level']) ?></span>

                <div class="skill-progress">
                    <div class="progress-header">
                        <span>Skill Level</span>
                        <strong><?= $skill['percentage'] ?>%</strong>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: <?= $skill['percentage'] ?>%"></div>
                    </div>
                </div>

                <div class="experience-box">
                    <i class="fa-solid fa-calendar"></i>
                    <?= calculateExperience($skill['initial_experience'], $skill['created_at']) ?> Experience
                </div>

                <div class="added-date">
                    Added:
                    <?php
                    $date = new DateTime($skill['created_at']);
                    echo $date->format('d M Y');
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <p id="skillsEmptyMessage" class="skills-empty-message" hidden>No skills match the selected level.</p>

    <!-- Add / Edit Skill Modal -->
    <div class="modal" id="skillModal">
        <div class="modal-content">
            <span class="close" onclick="closeSkillModal()">&times;</span>
            <h2 id="modalTitle">Add Skill</h2>

            <form method="POST" id="skillForm">
                <input type="hidden" name="skill_id" id="skill_id">

                <label>Skill Name</label>
                <input type="text" name="skill_name" id="skill_name" placeholder="Example: React" required>

                <label>Experience (Years)</label>
                <input
                    type="number"
                    name="initial_experience"
                    id="initial_experience"
                    min="0"
                    max="30"
                    step="1"
                    required
                >
                <small id="experienceHint">
                    Beginner: 0–1, Intermediate: 2–3, Advanced: 4–5, Expert: 6–30 years.
                </small>

                <label>Level</label>
                <input type="text" id="level" readonly placeholder="Enter years of experience">

                <button type="submit" name="save_skill" id="saveButton" class="save-btn">
                    Save Skill
                </button>
            </form>
        </div>
    </div>

    <script>
        const skillForm = document.getElementById('skillForm');
        const skillLevel = document.getElementById('level');
        const skillYears = document.getElementById('initial_experience');

        function updateSkillLevel() {
            skillYears.setCustomValidity('');
            skillLevel.value = '';

            if (skillYears.value === '') {
                return;
            }

            const years = Number(skillYears.value);
            if (!Number.isInteger(years) || years < 0 || years > 30) {
                skillYears.setCustomValidity('Enter a whole number between 0 and 30.');
                return;
            }

            skillLevel.value = years <= 1 ? 'Beginner'
                : years <= 3 ? 'Intermediate'
                : years <= 5 ? 'Advanced' : 'Expert';
        }

        skillYears.addEventListener('input', updateSkillLevel);
        skillYears.addEventListener('change', () => {
            if (skillYears.value === '0') {
                skillYears.value = '1';
                updateSkillLevel();
            }
        });

        skillForm.addEventListener('submit', event => {
            updateSkillLevel();
            if (!skillForm.checkValidity()) {
                event.preventDefault();
                skillForm.reportValidity();
            }
        });

        function openSkillModal() {
            document.getElementById('skillModal').style.display = 'flex';
            document.getElementById('modalTitle').innerHTML = 'Add Skill';
            document.getElementById('skillForm').reset();
            skillYears.setCustomValidity('');
            skillLevel.value = '';
            document.getElementById('skill_id').value = '';
            document.getElementById('saveButton').name = 'save_skill';
            document.getElementById('saveButton').innerHTML = 'Save Skill';
        }

        function closeSkillModal() {
            document.getElementById('skillModal').style.display = 'none';
        }

        function editSkill(id, name, level, experience) {
            document.getElementById('skillModal').style.display = 'flex';
            document.getElementById('modalTitle').innerHTML = 'Edit Skill';
            document.getElementById('skill_id').value = id;
            document.getElementById('skill_name').value = name;
            document.getElementById('level').value = level;
            document.getElementById('initial_experience').value = experience;
            updateSkillLevel();
            document.getElementById('saveButton').name = 'update_skill';
            document.getElementById('saveButton').innerHTML = 'Update Skill';
        }


        // Filter existing skills without reloading the page.
        const filterButtons = document.querySelectorAll('.skill-filter-btn');
        const skillCards = document.querySelectorAll('.skills-grid .skill-card');
        const emptyMessage = document.getElementById('skillsEmptyMessage');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                const selectedLevel = button.dataset.filter;
                let visibleCount = 0;

                filterButtons.forEach(item => {
                    const isActive = item === button;
                    item.classList.toggle('active', isActive);
                    item.setAttribute('aria-pressed', String(isActive));
                });

                skillCards.forEach(card => {
                    const matches = selectedLevel === 'all' || card.dataset.level === selectedLevel;
                    card.hidden = !matches;
                    if (matches) visibleCount++;
                });

                if (emptyMessage) emptyMessage.hidden = visibleCount !== 0;
            });
        });

        window.onclick = function (event) {
            const modal = document.getElementById('skillModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        };
    </script>
</div>

<?php if (isset($_SESSION['skill_error'])): ?>
    <div id="toastMessage" class="toast-message">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span>
            <?php
            echo htmlspecialchars($_SESSION['skill_error'], ENT_QUOTES, 'UTF-8');
            unset($_SESSION['skill_error']);
            ?>
        </span>
    </div>

    <script>
        setTimeout(function () {
            const toast = document.getElementById('toastMessage');
            if (toast) {
                toast.classList.add('hide');
                setTimeout(() => {
                    toast.remove();
                }, 500);
            }
        }, 3000);
    </script>
<?php endif; ?>

<?php include '../../Includes/dash_footer.php'; ?>
