<?php
/**
 * SkillBridge - University Registry Backend Controller
 * Handles CRUD actions, validation, stats calculation, and pagination for university.php
 */

require_once __DIR__ . '/../../Config/db.php';
require_once __DIR__ . '/AdminBackend.php';

if (!isset($adminDB) || !($adminDB instanceof AdminDB)) {
    $adminDB = new AdminDB($conn);
}

$flash = null;

// Handle POST actions (Add, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';

    if ($action === 'add') {
        $university = trim($_POST['university'] ?? '');
        $faculty = trim($_POST['faculty'] ?? '');
        $domain = trim($_POST['domain'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $status = trim($_POST['status'] ?? '');
        try {
            if ($adminDB->domainExists($domain)) {
                $flash = ['type' => 'error', 'message' => 'Email Domain already exists'];
            } elseif (!empty($university) && !empty($faculty) && !empty($domain) && !empty($location) && !empty($status)) {
                $adminDB->addUniversity($university, $faculty, $domain, $status, $location);
                $flash = ['type' => 'success', 'message' => 'University added successfully'];
            } else {
                $flash = ['type' => 'error', 'message' => 'All fields are required'];
            }
        } catch (mysqli_sql_exception $e) {
            $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    } elseif ($action === 'edit') {
        $origDomain = trim($_POST['original_domain'] ?? '');
        $university = trim($_POST['university'] ?? '');
        $faculty = trim($_POST['faculty'] ?? '');
        $domain = trim($_POST['domain'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $status = trim($_POST['status'] ?? '');
        try {
            $existingUni = $adminDB->getUniversityByDomain($origDomain);
            if ($existingUni && strcasecmp($existingUni['Status'] ?? '', 'Inactive') === 0) {
                $flash = ['type' => 'error', 'message' => 'This university is currently Inactive and its status or details cannot be modified.'];
            } else {
                $adminDB->updateUniversity($university, $faculty, $domain, $status, $location, $origDomain);
                $flash = ['type' => 'success', 'message' => 'University updated successfully'];
            }
        } catch (mysqli_sql_exception $e) {
            $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    } elseif ($action === 'delete') {
        $domainToDelete = trim($_POST['delete_domain'] ?? '');
        $stuCount = $adminDB->getStudentCountByDomain($domainToDelete);
        if ($stuCount > 0) {
            $flash = [
                'type' => 'error',
                'message' => "Deletion Blocked: This institution currently has {$stuCount} enrolled student account(s) with domain @{$domainToDelete}. Deleting it is restricted to preserve student credentials. Please update its status to 'Hold' or 'Inactive' instead."
            ];
        } else {
            try {
                $adminDB->deleteUniversity($domainToDelete);
                $flash = ['type' => 'success', 'message' => 'University deleted successfully'];
            } catch (mysqli_sql_exception $e) {
                $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }
}

// KPI Statistics
$totUni          = $adminDB->getCount("universityemails");
$activeDomains   = $adminDB->getCountWhere("universityemails", "status", "Active");
$holdDomains     = $adminDB->getCountWhere("universityemails", "status", "Hold");
$totalStudents   = $adminDB->getCount("student");

// Filter, Search, and Pagination
$selectedStatus = $_GET['status'] ?? 'all';
$searchQuery = trim($_GET['search'] ?? '');

$uniResult = $adminDB->getAllUniversities($selectedStatus, $searchQuery);
$allUniversities = [];
if ($uniResult) {
    while ($r = $uniResult->fetch_assoc()) {
        $allUniversities[] = $r;
    }
}
$totFilteredUni = count($allUniversities);

// Pagination Setup (5 records per page)
$recordsPerPage = 5;
$totalPages = ceil($totFilteredUni / $recordsPerPage);
$currentPage = isset($_GET['page']) ? max(1, min(max(1, $totalPages), (int) $_GET['page'])) : 1;
$offset = ($currentPage - 1) * $recordsPerPage;
$pageUniversities = array_slice($allUniversities, $offset, $recordsPerPage);
