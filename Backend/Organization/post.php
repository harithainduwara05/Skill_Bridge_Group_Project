<?php
/*
| Organization backend - Post Project: saves the new project + its supporting documents
| Used by: Functions/Organization/post.php
| (the page already loaded Config/db.php + Session/Session.php and checked the role)
*/

$user = current_user();

$flash = null;

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
    $students_required   = (int)($_POST['students_required'] ?? 1);
    $preferred_year      = trim($_POST['preferred_year'] ?? 'Any Year');
    $deadline            = trim($_POST['deadline'] ?? '');
    $visibility          = trim($_POST['visibility'] ?? 'Public');
    $action              = trim($_POST['action'] ?? 'publish'); // draft | publish

    $status        = ($action === 'draft') ? 'draft' : 'reviewing';
    $duration_text = $duration_weeks !== '' ? $duration_weeks . ' Weeks' : null;

    if (empty($title) || empty($category) || empty($description)) {
        $flash = ['type' => 'error', 'message' => 'Please fill Title, Category and Description.'];
    } else {
        try {
            $company            = $user['username'] ?? '';
            $organization_email = $user['email'];
            $tech_stack         = $keywords; // reused for landing-page display cards

            $sql = "INSERT INTO projects
                    (title, company, organization_email, category, keywords, description,
                     learning_objectives, expected_outcomes, difficulty, duration, members,
                     preferred_year, deadline, visibility, status, tech_stack)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssssssisssss",
                $title, $company, $organization_email, $category, $keywords,
                $description, $learning_objectives, $expected_outcomes, $difficulty,
                $duration_text, $students_required, $preferred_year, $deadline,
                $visibility, $status, $tech_stack
            );

            if ($stmt->execute()) {
                $projectId = (int)$conn->insert_id;
                saveProjectDocuments($conn, $projectId);

                header("Location: manage_projects.php?posted=1");
                exit;
            } else {
                $flash = ['type' => 'error', 'message' => 'Failed to save project.'];
            }
        } catch (Exception $e) {
            $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}