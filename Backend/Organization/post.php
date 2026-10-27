<?php
/*
| Organization backend - Post Project: saves the new project + its supporting documents
| Used by: Functions/Organization/post.php
| (the page already loaded Config/db.php + Session/Session.php and checked the role)
*/

require_once __DIR__ . '/project_validation.php';
require_once __DIR__ . '/org_db.php';

$user = current_user();
$categories = orgCategories($conn);   // [category_id => name] for the Category dropdown

$flash  = null;
$errors = [];   // field name => error message (shown under each field)
$old    = [];   // the values the organization typed, put back in the form after an error

// ---- Supporting documents: allowed types + size limit ----
const DOC_MAX_BYTES = 10 * 1024 * 1024; // 10MB per file
const DOC_ALLOWED_EXT = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'csv', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'zip'];

/**
 * Save every file in $_FILES['attachments'] for the given project.
 * Files with a wrong type or bigger than 10MB are skipped.
 */
function saveProjectDocuments(mysqli $conn, int $projectId): int
{
    if (empty($_FILES['attachments']) || !is_array($_FILES['attachments']['name'])) {
        return 0;
    }

    $uploadDir = __DIR__ . '/../../Assets/Uploads/project_docs/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $ins = $conn->prepare("INSERT INTO project_documents
                           (project_id, original_name, file_path, file_size, mime_type)
                           VALUES (?,?,?,?,?)");

    $failed = 0;
    $files  = $_FILES['attachments'];

    foreach ($files['name'] as $i => $originalName) {
        if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;

        $ext  = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $size = (int)$files['size'][$i];

        if ($files['error'][$i] !== UPLOAD_ERR_OK
            || !in_array($ext, DOC_ALLOWED_EXT, true)
            || $size <= 0 || $size > DOC_MAX_BYTES) {
            $failed++;
            continue;
        }

        // Unique, safe file name on disk; the original name is kept in the DB
        $storedName = 'project_' . $projectId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        if (!move_uploaded_file($files['tmp_name'][$i], $uploadDir . $storedName)) {
            $failed++;
            continue;
        }

        $relativePath = 'Assets/Uploads/project_docs/' . $storedName;
        $mime         = mime_content_type($uploadDir . $storedName) ?: 'application/octet-stream';
        $cleanName    = mb_substr(basename($originalName), 0, 255);

        $ins->bind_param("issis", $projectId, $cleanName, $relativePath, $size, $mime);
        $ins->execute();
    }

    return $failed;
}

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
    $action              = trim($_POST['action'] ?? 'publish'); // draft | publish

    $isDraft = ($action === 'draft');
    $status  = $isDraft ? 'draft' : 'reviewing';

    // ---- Validation (rules are in project_validation.php) ----
    $errors = validateProjectForm([
        'title' => $title, 'category' => $category, 'keywords' => $keywords,
        'description' => $description, 'learning_objectives' => $learning_objectives,
        'expected_outcomes' => $expected_outcomes, 'difficulty' => $difficulty,
        'duration_weeks' => $duration_weeks, 'students_required' => $students_raw,
        'preferred_year' => $preferred_year, 'deadline' => $deadline, 'visibility' => $visibility,
    ], $isDraft, ['categories' => array_values($categories)]);

    // clean skills list: ['React', 'Python'] (no empty / duplicate skills)
    $skills            = cleanSkills($keywords);
    $keywords          = implode(', ', $skills);
    $students_required = $students_raw !== '' ? (int)$students_raw : 1;   // draft without a number -> 1
    $durationWeeks     = $duration_weeks !== '' ? (int)$duration_weeks : null;
    $categoryId        = $category !== '' ? orgCategoryId($conn, $category) : null;
    $deadlineDb        = $deadline !== '' ? $deadline : null;             // DATE column

    // the typed values go back into the form if the project is not saved
    $old = [
        'title' => $title, 'category' => $category, 'keywords' => $keywords,
        'description' => $description, 'learning_objectives' => $learning_objectives,
        'expected_outcomes' => $expected_outcomes, 'difficulty' => $difficulty,
        'duration_weeks' => $duration_weeks, 'students_required' => $students_raw,
        'preferred_year' => $preferred_year, 'deadline' => $deadline, 'visibility' => $visibility,
    ];

    if (!empty($errors)) {
        $msg = $isDraft ? 'Please fix the highlighted fields to save the draft.'
                        : 'Please fix the highlighted fields before publishing.';
        // the browser can't keep chosen files after the page reloads
        if (!empty($_FILES['attachments']['name'][0])) $msg .= ' Please attach your files again.';
        $flash = ['type' => 'error', 'message' => $msg];

    } else {
        try {
            $company            = $user['username'] ?? '';
            $organization_email = $user['email'];

            $sql = "INSERT INTO projects
                    (organization_email, category_id, title, description, learning_objectives, expected_outcomes,
                     difficulty, duration_weeks, members, preferred_year, deadline, visibility, status)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sisssssiissss",
                $organization_email, $categoryId, $title, $description, $learning_objectives, $expected_outcomes,
                $difficulty, $durationWeeks, $students_required, $preferred_year, $deadlineDb, $visibility, $status
            );

            if ($stmt->execute()) {
                $projectId = (int)$conn->insert_id;
                orgSaveProjectSkills($conn, $projectId, $skills);   // one row per skill in project_skills
                saveProjectDocuments($conn, $projectId);

                // A published project goes to the Admin first (status = 'reviewing').
                // Students can't see it until the Admin accepts it.
                // -> send a notification to every Admin.
                if ($status === 'reviewing') {
                    $nTitle   = 'New Project Waiting for Approval';
                    $nMessage = ($company !== '' ? $company : $organization_email)
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
                }

                header("Location: manage_projects.php?" . ($status === 'draft' ? 'drafted=1' : 'posted=1'));
                exit;
            } else {
                $flash = ['type' => 'error', 'message' => 'Failed to save project.'];
            }
        } catch (Exception $e) {
            $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}