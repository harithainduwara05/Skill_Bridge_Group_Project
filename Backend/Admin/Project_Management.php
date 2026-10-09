<?php
require_once __DIR__ . '/loader.php';

$dashboardDB = new DashboardManagement($conn);
$adminDB = $dashboardDB;

// Get Cards details from projects table
$totalProject     = $dashboardDB->getCount('projects');
$activeProject    = $dashboardDB->getCountWhere('projects', 'status', 'inprogress') + $dashboardDB->getCountWhere('projects', 'status', 'Active');
$reviewingProject = $dashboardDB->getCountWhere('projects', 'status', 'reviewing') + $dashboardDB->getCountWhere('projects', 'status', 'Rewiewing');
$completeProject  = $dashboardDB->getCountWhere('projects', 'status', 'completed') + $dashboardDB->getCountWhere('projects', 'status', 'Complete');
?>
