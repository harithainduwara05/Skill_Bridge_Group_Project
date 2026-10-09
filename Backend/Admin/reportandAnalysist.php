<?php

require_once __DIR__ . '/loader.php';

$dashboardDB = new DashboardManagement($conn);
$adminDB = $dashboardDB;

// Any reporting calculations, charts data feeds, or exports can be processed here
?>
