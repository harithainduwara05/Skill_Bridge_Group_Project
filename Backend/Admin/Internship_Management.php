<?php

require_once __DIR__ . '/loader.php';

$internshipDB = new InternshipManagement($conn);
$adminDB      = $internshipDB;

$flash = null;

// Handle POST / AJAX Actions
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = $_POST['action'] ?? $_POST['ajax_action'] ?? '';
    $isAjax = isset($_POST['ajax_action']) || (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1');

    if ($action === 'suspend' || $action === 'suspend_internship') {
        $internshipId = $_POST['internship_id'] ?? $_POST['id'] ?? '';
        $category     = trim($_POST['category'] ?? '');
        $reason       = trim($_POST['reason'] ?? '');
        $fullReason   = !empty($category) ? "{$category}: {$reason}" : $reason;

        $ok      = $internshipDB->updateInternshipStatus($internshipId, 'Suspended', $fullReason);
        $msg     = $ok ? "Internship suspended successfully." : "Failed to suspend internship.";
        $success = (bool)$ok;
    } elseif ($action === 'reactivate' || $action === 'reactivate_internship') {
        $internshipId = $_POST['internship_id'] ?? $_POST['id'] ?? '';
        $ok           = $internshipDB->updateInternshipStatus($internshipId, 'Active');
        $msg          = $ok ? "Internship reactivated successfully!" : "Failed to reactivate internship.";
        $success      = (bool)$ok;
    } elseif ($action === 'terminate' || $action === 'terminate_internship') {
        $internshipId = $_POST['internship_id'] ?? $_POST['id'] ?? '';
        $reason       = trim($_POST['reason'] ?? 'Terminated by Admin due to policy violation.');
        $ok           = $internshipDB->updateInternshipStatus($internshipId, 'Terminated', $reason);
        $msg          = $ok ? "Internship permanently terminated." : "Failed to terminate internship.";
        $success      = (bool)$ok;
    }

    if (isset($success)) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success, 'message' => $msg]);
            exit();
        }
        $flash = ['type' => $success ? 'success' : 'error', 'message' => $msg];
    }
}

// Card Statistics via AdminDB base functions & InternshipManagement
$totalInternships     = $internshipDB->getCount('internships');
$activeInternships    = $internshipDB->getActiveInternshipCount();
$suspendedInternships = $internshipDB->getSuspendedInternshipCount();
$totalApplications    = $internshipDB->getCount('internship_applications');

// Fetch Data for Table and Filters
$internshipsList = $internshipDB->getAllInternships();
$industriesList  = $internshipDB->getDistinctIndustries();
?>
