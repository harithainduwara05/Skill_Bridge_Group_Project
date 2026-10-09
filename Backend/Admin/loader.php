<?php
/**
 * SkillBridge - Admin Services Loader
 * 
 * Centralized loader for the Admin Service Layer.
 * Organizes specialized domain services under the /Services directory.
 */

// Centralized database connection
require_once __DIR__ . '/../../Config/db.php';

require_once __DIR__ . '/Services/AdminDB.php';
require_once __DIR__ . '/Services/DashboardManagement.php';
require_once __DIR__ . '/Services/ComplaintManagement.php';
require_once __DIR__ . '/Services/UniversityManagement.php';
require_once __DIR__ . '/Services/UserManagement.php';
require_once __DIR__ . '/Services/AdminProfileManagement.php';
require_once __DIR__ . '/Services/NotificationManagement.php';

// Global base instance initialization if $conn is available
if (isset($conn) && (!isset($adminDB) || !($adminDB instanceof AdminDB))) {
    $adminDB = new AdminDB($conn);
}
?>
