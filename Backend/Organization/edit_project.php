<?php
/*
| Organization backend - Edit Project: loads the project and saves the changes
| Used by: Functions/Organization/edit_project.php
| (the page already loaded Config/db.php + Session/Session.php and checked the role)
*/

require_once __DIR__ . '/project_validation.php';
require_once __DIR__ . '/org_db.php';

$user = current_user();
$organization_email = $user['email'];
$categories = orgCategories($conn);   // [category_id => name] for the Category dropdown

// ---- Load the project (must belong to this organization) ----
$projectId = (int)($_GET['id'] ?? 0);

// (category, keywords, duration come from project_categories / project_skills, see org_db.php)
$stmt = $conn->prepare(ORG_PROJECT_SELECT . " WHERE p.id = ? AND p.organization_email = ?");
$stmt->bind_param("is", $projectId, $organization_email);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
if ($project) {
    // lowercase, because the Admin saves some statuses as 'Hold'
    $project['status'] = strtolower(trim($project['status'] ?? ''));
    // Admin's Accept / Reactivate saves the status as 'Active' -> for the organization
    // that means "Published" (old data may still say 'accepted'). The organization's own Active is 'inprogress'.
    if (in_array($project['status'], ['active', 'approved', 'accepted'], true)) $project['status'] = 'published';
}

// On Hold / Rejected projects can't be edited here (On Hold is fixed from the
// hold popup in Manage Projects, Rejected is final)
if (!$project || in_array($project['status'], ['hold', 'rejected'], true)) {
    header("Location: manage_projects.php");
    exit;
}

// How many students are actually assigned right now — shown next to the
// "Students Required" field so it's obvious if the team is over capacity.
$assignedStmt = $conn->prepare("SELECT COUNT(*) FROM student_projects WHERE project_id = ?");
$assignedStmt->bind_param("i", $projectId);
$assignedStmt->execute();
$assignedCount = (int)$assignedStmt->get_result()->fetch_row()[0];

// Active only when the team is full: an Active project whose team is not full
// (old data, a student left, or Students Required was raised) is shown as Published.
if ($project['status'] === 'inprogress' && $assignedCount < max(1, (int)$project['members'])) {
    $project['status'] = 'published';
}

$flash  = null;
$errors = [];   // field name => error message (shown under each field)

// Saved category / deadline before this edit: an older project may use a category that is
// not in the list, and a deadline that is not changed is not checked again (it may be past
// for an Active / Completed project). Older deadlines look like "Oct 09, 2026" -> "2026-10-09".
$originalCategory = $project['category'] ?? '';
$originalDeadline = !empty($project['deadline']) && strtotime($project['deadline'])
                  ? date('Y-m-d', strtotime($project['deadline'])) : '';

// ---- Project status flow ----
//   Draft -> Reviewing (sent to Admin) -> Published (accepted by Admin, students can see it)
//         -> Active (organization, only when the team is full) -> Completed
// "Reviewing" is waiting for the Admin, and "Hold" / "Rejected" are set only by
// the Admin, so the organization can't change those three.
// "Completed" is never chosen here: it is set by itself when the organization accepts the final report
// of every team of the project (Teams page).
$adminOnlyStatusLabels = [
    'reviewing' => 'Reviewing (waiting for Admin approval)',
    'hold'      => 'Hold',
    'rejected'  => 'Rejected',
];

// Statuses the organization can choose, depending on the CURRENT status
$statusFlow = [
    'draft'      => ['draft' => 'Draft', 'reviewing' => 'Send to Admin for review'],
    'published'  => ['published' => 'Published', 'inprogress' => 'Active'],
    'inprogress' => ['inprogress' => 'Active'],
    'completed'  => ['completed' => 'Completed'],
];
$orgStatusOptions = $statusFlow[$project['status']] ?? [];

// The team is full when the assigned students >= students required
$teamIsFull = $assignedCount >= max(1, (int)$project['members']);

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
    $students_raw        = trim($_POST['students_required'] ?? '');
    $preferred_year      = trim($_POST['preferred_year'] ?? 'Any Year');
    $deadline            = trim($_POST['deadline'] ?? '');
    $visibility          = trim($_POST['visibility'] ?? 'Public');
    $status              = trim($_POST['status'] ?? $project['status']);

    // Reviewing / Hold / Rejected can't be changed by the organization, and only
    // the next statuses allowed by $statusFlow are accepted.
    if (isset($adminOnlyStatusLabels[$project['status']]) || !isset($orgStatusOptions[$status])) {
        $status = $project['status'];
    }

    // ---- Validation (rules are in project_validation.php) ----
    // A project that stays a Draft needs only the title; any other status needs every * field.
    $errors = validateProjectForm([
        'title' => $title, 'category' => $category, 'keywords' => $keywords,
        'description' => $description, 'learning_objectives' => $learning_objectives,
        'expected_outcomes' => $expected_outcomes, 'difficulty' => $difficulty,
        'duration_weeks' => $duration_weeks, 'students_required' => $students_raw,
        'preferred_year' => $preferred_year, 'deadline' => $deadline, 'visibility' => $visibility,
    ], $status === 'draft', [
        'categories'   => array_values($categories),
        'old_deadline' => $originalDeadline,
        'min_students' => $assignedCount,
    ]);

    $skills            = cleanSkills($keywords);
    $keywords          = implode(', ', $skills);
    $students_required = $students_raw !== '' ? (int)$students_raw : 1;
    if (!empty($errors)) {
        $flash = ['type' => 'error', 'message' => 'Please fix the highlighted fields.'];
    }

    // ---- Setting the project to Active (organization decides, manually) ----
    //  Active = the team is working and NO more students can join.
    //  - the team needs at least 1 student
    //  - team not full yet -> the organization starts early with the current team,
    //    so Students Required becomes the real team size (then "Active = team full"
    //    is true everywhere: Manage Projects, Teams, Feedback, Reports)
    $startedEarly = false;
    if ($flash === null && $status === 'inprogress' && $project['status'] !== 'inprogress') {
        if ($assignedCount < 1) {
            $flash = ['type' => 'error', 'message' => 'Add at least one student to the team before setting the project to Active.'];
            $status = $project['status'];
        } elseif ($assignedCount < max(1, $students_required)) {
            $students_required = $assignedCount;
            $startedEarly = true;
        }
    }

    // An Active project whose Students Required is raised above the team size
    // goes back to Published, so students can see it and join again.
    $backToPublished = false;
    if ($flash === null && $status === 'inprogress' && $assignedCount < max(1, $students_required)) {
        $status = 'published';
        $backToPublished = true;
    }

    $durationWeeks = $duration_weeks !== '' ? (int)$duration_weeks : null;
    $categoryId    = $category !== '' ? orgCategoryId($conn, $category) : null;
    $deadlineDb    = $deadline !== '' ? $deadline : null;   // DATE column

    if ($flash !== null) {
        // error already set above (validation, or no students for Active)
    } else {
        try {
            $sql = "UPDATE projects SET
                        title = ?, category_id = ?, description = ?,
                        learning_objectives = ?, expected_outcomes = ?, difficulty = ?,
                        duration_weeks = ?, members = ?, preferred_year = ?, deadline = ?,
                        visibility = ?, status = ?
                    WHERE id = ? AND organization_email = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sissssiissssis",
                $title, $categoryId, $description,
                $learning_objectives, $expected_outcomes, $difficulty,
                $durationWeeks, $students_required, $preferred_year, $deadlineDb,
                $visibility, $status,
                $projectId, $organization_email
            );

            if ($stmt->execute()) {
                orgSaveProjectSkills($conn, $projectId, $skills);   // one row per skill in project_skills

                // Draft sent to the Admin -> notify every Admin (same as Post Project)
                if ($project['status'] === 'draft' && $status === 'reviewing') {
                    $nTitle   = 'New Project Waiting for Approval';
                    $nMessage = ($user['username'] ?? $organization_email)
                              . ' posted a new project "' . $title . '" (Project #PR'
                              . str_pad($projectId, 3, '0', STR_PAD_LEFT)
                              . '). Open Project Management to review and accept it.';

                    $nq = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status)
                                          SELECT Email, ?, ?, 'admin', 'Unread' FROM admin");
                    $nq->bind_param("ss", $nTitle, $nMessage);
                    $nq->execute();

                    // ...and a copy for the organization itself, so it shows in its Notifications page
                    $oTitle   = 'Project Sent for Review';
                    $oMessage = 'Your project "' . $title . '" was sent to the Admin. Students will see it after the Admin accepts it.';
                    $oq = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status)
                                          VALUES (?, ?, ?, 'project', 'Unread')");
                    $oq->bind_param("sss", $organization_email, $oTitle, $oMessage);
                    $oq->execute();

                    header("Location: manage_projects.php?submitted=1");
                    exit;
                }

                header("Location: manage_projects.php?updated=1"
                     . ($backToPublished ? "&back_to_published=1" : "")
                     . ($startedEarly ? "&started_early=" . $assignedCount : ""));
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
        'duration' => $duration_weeks, 'members' => $students_raw,
        'preferred_year' => $preferred_year, 'deadline' => $deadline,
        'visibility' => $visibility,
    ]);
    // the status the organization chose stays selected in the dropdown
    // ($project['status'] keeps the saved one, it decides which options and notes are shown)
    $selectedStatus = $status;
}
$selectedStatus = $selectedStatus ?? $project['status'];

// Older projects store the deadline as "Oct 09, 2026" -> the date box needs "2026-10-09"
$deadlineTs    = !empty($project['deadline']) ? strtotime($project['deadline']) : false;
$deadlineValue = $deadlineTs ? date('Y-m-d', $deadlineTs) : '';

// Extract just the leading number out of "12 Weeks" for the number input
preg_match('/\d+/', $project['duration'] ?? '', $m);
$durationWeeksValue = $m[0] ?? '';