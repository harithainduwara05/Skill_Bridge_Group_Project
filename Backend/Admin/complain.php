<?php
/**
 * SkillBridge - Complaint Management Backend Controller
 * Handles status/resolution updates, AJAX dispatch, complaints aggregation, and pagination stats
 */

require_once __DIR__ . '/loader.php';

$complaintDB = new ComplaintManagement($conn);
$adminDB = $complaintDB; // Reference alias for child class

// Handle AJAX Status & Resolution Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action']) && $_POST['ajax_action'] === 'update_complaint_status') {
    header('Content-Type: application/json');
    $complaintId = intval($_POST['complaint_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? 'PENDING');
    $notes = trim($_POST['notes'] ?? '');

    if ($complaintId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid Complaint ID']);
        exit();
    }

    // Dismissal Reason is mandatory when dismissing a complaint
    if ($newStatus === 'DISMISSED' && (empty($notes) || mb_strlen($notes) < 5)) {
        echo json_encode(['success' => false, 'message' => 'A dismissal reason is mandatory. Please provide an explanation before dismissing this complaint.']);
        exit();
    }

    $updated = $complaintDB->updateComplaintStatus($complaintId, $newStatus, $notes);
    if ($updated) {
        $stats = $complaintDB->getComplaintStats();
        echo json_encode(['success' => true, 'message' => 'Status updated successfully', 'stats' => $stats]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update database record']);
    }
    exit();
}

// Fetch Complaint Statistics and All Complaint Records
$stats = $complaintDB->getComplaintStats();
$complaints = $complaintDB->getAllComplaintsDetailed();

// Pagination setup
$pageSize = 6;
$totalComplaintsCount = count($complaints);
$totalPages = max(1, ceil($totalComplaintsCount / $pageSize));
?>
