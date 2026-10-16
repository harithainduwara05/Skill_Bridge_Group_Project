<?php

require_once __DIR__ . '/loader.php';

$dashboardDB = new DashboardManagement($conn);
$adminDB = $dashboardDB;

// Any future backend queries, approvals, or stat updates for internships can be handled here
?>
