<?php
/*
| Organization backend - Edit Project: loads the project and saves the changes
| Used by: Functions/Organization/edit_project.php
| (the page already loaded Config/db.php + Session/Session.php and checked the role)
*/

$user = current_user();
$organization_email = $user['email'];

// ---- Load the project (must belong to this organization) ----
$projectId = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ? AND organization_email = ?");
$stmt->bind_param("is", $projectId, $organization_email);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

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

$flash = null;

// Statuses the organization is allowed to choose.
// "Hold" and "Rejected" can only be set by the Admin.
$orgStatusOptions = ['reviewing' => 'Reviewing', 'inprogress' => 'Active', 'completed' => 'Completed'];
$adminOnlyStatusLabels = ['hold' => 'Hold', 'rejected' => 'Rejected'];

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

    // If the Admin has put this project On Hold / Rejected, the organization
    // cannot change it. Otherwise only the organization's own statuses are accepted.
    if (isset($adminOnlyStatusLabels[$project['status']]) || !isset($orgStatusOptions[$status])) {
        $status = $project['status'];
    }

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

// Older projects store the deadline as "Oct 09, 2026" -> the date box needs "2026-10-09"
$deadlineTs    = !empty($project['deadline']) ? strtotime($project['deadline']) : false;
$deadlineValue = $deadlineTs ? date('Y-m-d', $deadlineTs) : '';

// Extract just the leading number out of "12 Weeks" for the number input
preg_match('/\d+/', $project['duration'] ?? '', $m);
$durationWeeksValue = $m[0] ?? '';