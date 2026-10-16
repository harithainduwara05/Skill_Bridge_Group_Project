<?php

require_once __DIR__ . '/loader.php';

$dashboardDB = new DashboardManagement($conn);
$complaintDB = new ComplaintManagement($conn);
$profileDB   = new AdminProfileManagement($conn);
$userDB      = new UserManagement($conn);
$uniDB       = new UniversityManagement($conn);

// Handle Dismiss Complaint Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'dismiss') {
    $complaintId = intval($_POST['complaint_id'] ?? 0);
    if ($complaintId > 0) {
        $complaintDB->dismissComplaint($complaintId);
    }
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit();
}

// Resolve Current Admin Info
$adminEmail = $user['email'] ?? $user['Email'] ?? '';
$adminProfile = (!empty($adminEmail)) ? $profileDB->getAdminProfile($adminEmail) : null;
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
$totalUsers          = $dashboardDB->getCount("user");
$totUni              = $dashboardDB->getCount("universityemails");
$tot_Ongoin_projects = $dashboardDB->getOngoingProjectCount();
$tot_internship      = $dashboardDB->getAvailableInternshipCount();
$totalAcPro          = $dashboardDB->getActiveProjectCount();

$allusers            = $userDB->getUsers();
$complains           = $complaintDB->getUrgentComplaints(3);
$popularUni          = $uniDB->getPopularUni();
$undismissedComplaintsCount = $complaintDB->getUndismissedComplaintCount();
?>
