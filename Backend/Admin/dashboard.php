<?php
/**
 * SkillBridge - Admin Dashboard Backend Controller
 * Handles backend data aggregation and actions for dashboard.php
 */

require_once __DIR__ . '/../../Config/db.php';
require_once __DIR__ . '/AdminBackend.php';

if (!isset($adminDB) || !($adminDB instanceof AdminDB)) {
    $adminDB = new AdminDB($conn);
}

// Handle Dismiss Complaint Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'dismiss') {
    $complaintId = intval($_POST['complaint_id'] ?? 0);
    if ($complaintId > 0) {
        $adminDB->dismissComplaint($complaintId);
    }
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit();
}

// Resolve Current Admin Info
$adminEmail = $user['email'] ?? $user['Email'] ?? '';
$adminProfile = (!empty($adminEmail)) ? $adminDB->getAdminProfile($adminEmail) : null;
$adminName = (!empty($user['username']) && $user['username'] !== 'User')
    ? $user['username']
    : ((!empty($name) && $name !== 'User')
        ? $name
        : ($adminProfile['name'] ?? 'Admin'));

if (isset($_SESSION['user']) && (empty($_SESSION['user']['username']) || $_SESSION['user']['username'] === 'User') && $adminName !== 'Admin') {
    $_SESSION['user']['username'] = $adminName;
    $user['username'] = $adminName;
}

// Dashboard Aggregates & Statistics
$totalUsers          = $adminDB->getCount("user");
$totUni              = $adminDB->getCount("universityemails");
$tot_Ongoin_projects = $adminDB->getOngoingProjectCount();
$tot_internship      = $adminDB->getAvailableInternshipCount();
$allusers            = $adminDB->getUsers();
$complains           = $adminDB->getUrgentComplaints(3);
$popularUni          = $adminDB->getPopularUni();
$totalAcPro          = $adminDB->getActiveProjectCount();
