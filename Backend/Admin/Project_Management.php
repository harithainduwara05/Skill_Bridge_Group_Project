<?php


require_once __DIR__ . '/loader.php';

$projectManagement = new ProjectManagement($conn);
$adminDB           = $projectManagement;

$loggedInUser      = function_exists('current_user') ? current_user() : null;
$currentAdminEmail = strtolower($loggedInUser['Email'] ?? $loggedInUser['email'] ?? 'admin@skillbridge.lk');
$flash             = null;

// Handle POST / AJAX Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? $_POST['ajax_action'] ?? '';
    $isAjax = isset($_POST['ajax_action']) || (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1');

    if ($action === 'accept' || $action === 'approve' || $action === 'accept_project') {
        $projectId = $_POST['project_id'] ?? '';
        $ok        = $projectManagement->updateProjectStatus($projectId, 'Published');
        $msg       = $ok ? "Project approved and published successfully! Students can now view and apply." : "Failed to publish project.";
        $success   = (bool)$ok;
    } elseif ($action === 'hold' || $action === 'place_on_hold') {
        $projectId = $_POST['project_id'] ?? '';
        $reason    = trim($_POST['reason'] ?? $_POST['hold_reason'] ?? '');
        if (empty($reason)) {
            $ok      = false;
            $msg     = "A valid reason is required to place a project on hold.";
            $success = false;
        } else {
            $ok      = $projectManagement->updateProjectStatus($projectId, 'Hold', $reason, $currentAdminEmail);
            $msg     = $ok ? "Project placed on Hold. The organization has been notified with the reason." : "Failed to place on hold.";
            $success = (bool)$ok;
        }
    } elseif ($action === 'reactivate' || $action === 'reactivate_project') {
        $projectId = $_POST['project_id'] ?? '';
        $ok        = $projectManagement->updateProjectStatus($projectId, 'Published');
        $msg       = $ok ? "Project restored to Published status successfully!" : "Failed to restore project.";
        $success   = (bool)$ok;
    } elseif ($action === 'reject' || $action === 'reject_project') {
        $projectId  = $_POST['project_id'] ?? '';
        $category   = trim($_POST['category'] ?? '');
        $reason     = trim($_POST['reason'] ?? $_POST['reject_reason'] ?? '');
        $fullReason = !empty($category) ? "[{$category}] {$reason}" : $reason;
        $ok         = $projectManagement->updateProjectStatus($projectId, 'Rejected', $fullReason, $currentAdminEmail);
        $msg        = $ok ? "Project has been permanently rejected." : "Failed to reject project.";
        $success    = (bool)$ok;
    }

    if (isset($success)) {
        if ($isAjax) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => $success, 'message' => $msg]);
            exit();
        }
        $flash = ['type' => $success ? 'success' : 'error', 'message' => $msg];
    }
}

// Card Statistics via AdminDB base functions & ProjectManagement
$activeProjects    = $projectManagement->getActiveProjectCount();
$totalProjects     = $projectManagement->getCount('projects');
$reviewingProjects = $projectManagement->getReviewingProjectCount();
$completedProjects = $projectManagement->getCountWhere('projects', 'status', 'completed');

// Fetch Data for Projects Table and Filters
$projectsList      = $projectManagement->getAllProjects();
$categoriesList    = $projectManagement->getDistinctCategories();
?>