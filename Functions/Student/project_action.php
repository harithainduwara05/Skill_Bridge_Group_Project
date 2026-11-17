<?php
require_once __DIR__ . '/project_bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
if (!hash_equals($projectCsrf, (string) ($_POST['csrf'] ?? ''))) {
    http_response_code(403);
    exit('Invalid form token. Refresh and retry.');
}
$id = projectValidId($_POST['project_id'] ?? null);
$action = (string) ($_POST['action'] ?? '');
$return = (string) ($_POST['return_to'] ?? 'projects.php');
if (!in_array($return, ['projects.php', 'save_project.php', 'view_project.php'], true)) $return = 'projects.php';
if ($return === 'view_project.php') $return .= '?id=' . $id;
try {
    if (!$id) throw new InvalidArgumentException('Invalid project ID.');
    if ($action === 'toggle_save') {
        $saved = $projectRepo->toggleSaved($projectEmail, $id);
        projectSetToast($saved ? 'Project saved successfully.' : 'Project removed from saved items.');
    } elseif ($action === 'apply') {
        $skills = array_values(array_unique(array_filter(array_map('trim', explode(',', (string) ($_POST['skills'] ?? ''))))));
        if (count($skills) > 20) throw new InvalidArgumentException('Select at most 20 skills.');
        $data = [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'role' => trim((string) ($_POST['role'] ?? '')),
            'introduction' => trim((string) ($_POST['introduction'] ?? '')),
            'approach' => trim((string) ($_POST['approach'] ?? '')),
            'skills' => $skills
        ];
        if ($projectRepo->supportsDetailedProposal()) {
            if ($data['title'] === '' || $data['role'] === '' || $data['introduction'] === '' || $data['approach'] === '') {
                throw new InvalidArgumentException('Complete all required proposal fields.');
            }
            if (mb_strlen($data['title']) > 255 || mb_strlen($data['role']) > 100) throw new InvalidArgumentException('Proposal title or role is too long.');
        }
        $storedAttachment = null;
        if (isset($_FILES['portfolio_attachment']) && $_FILES['portfolio_attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
            if (!$projectRepo->supportsProposalAttachment()) {
                throw new InvalidArgumentException('Import the proposal attachment migration before uploading files.');
            }
            $file = $_FILES['portfolio_attachment'];
            if (!is_array($file) || is_array($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
                throw new InvalidArgumentException('File upload failed. Please select another file.');
            }
            if ($file['size'] < 1 || $file['size'] > 5 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'])) {
                throw new InvalidArgumentException('Attachment must be a valid file up to 5 MB.');
            }
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            $allowed = [
                'application/pdf' => 'pdf',
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];
            if (!isset($allowed[$mime])) {
                throw new InvalidArgumentException('Only PDF, JPG, PNG, and WebP files are allowed.');
            }
            $folder = __DIR__ . '/../../Assets/Uploads/Student/Proposals/';
            if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
                throw new RuntimeException('Could not create the attachment folder.');
            }
            $storedAttachment = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
            if (!move_uploaded_file($file['tmp_name'], $folder . $storedAttachment)) {
                throw new RuntimeException('Failed to save the uploaded attachment.');
            }
        }
        $data['attachment'] = $storedAttachment;
        try {
            $projectRepo->submitProposal($projectEmail, $id, $data);
        } catch (Throwable $submitError) {
            if ($storedAttachment !== null) {
                @unlink(__DIR__ . '/../../Assets/Uploads/Student/Proposals/' . $storedAttachment);
            }
            throw $submitError;
        }
        projectSetToast('Application submitted successfully.');
        $return = 'view_project.php?id=' . $id;
    } else {
        throw new InvalidArgumentException('Unsupported action.');
    }
} catch (Throwable $e) {
    error_log('Student project action: ' . $e->getMessage());
    projectSetToast($e instanceof InvalidArgumentException || $e instanceof RuntimeException ? $e->getMessage() : 'Unable to complete request. Check server error log.', true);
    if ($action === 'apply') $return = 'proposal.php?project_id=' . $id;
}
header('Location: ' . $return, true, 303);
exit;
