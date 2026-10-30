<?php
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Backend/Admin/Services/AdminDB.php';
require_once __DIR__ . '/../Backend/Admin/Services/ProjectManagement.php';
$projectManagement = new ProjectManagement($conn);
$projectsList = $projectManagement->getAllProjects();

ob_start();
include __DIR__ . '/../Functions/Admin/Components/Projects/ProjectTable.php';
$html = ob_get_clean();

if (strpos($html, 'PR017') !== false) {
    echo "PR017 found in ProjectTable HTML!\n";
    preg_match('/<tr[^>]*data-id="#PR017"[^>]*>/s', $html, $matches);
    if (!empty($matches)) {
        echo "TR Tag:\n" . substr($matches[0], 0, 500) . "...\n";
    }
} else {
    echo "PR017 NOT found in ProjectTable HTML!\n";
}
