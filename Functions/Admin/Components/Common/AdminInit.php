<?php
/**
 * Admin Initialization Component (AdminInit)
 *
 * Centralized bootstrap component for all Admin panel pages.
 * Performs session security checks, admin authorization, backend controller loading,
 * and renders the admin sidebar.
 *
 * Usage in any Admin page:
 *   $backendPage = 'Project_Management.php'; // Optional: defaults to basename($_SERVER['PHP_SELF'])
 *   require_once __DIR__ . '/Components/AdminInit.php';
 */

require_once __DIR__ . "/../../../../Session/Session.php";
require_login();
require_role('admin');
$user = current_user();

// Determine backend file to load: explicitly passed $backendPage or fallback to current script filename
$targetBackend = $backendPage ?? basename($_SERVER['PHP_SELF']);

if (!empty($targetBackend)) {
    if (substr($targetBackend, -4) !== '.php') {
        $targetBackend .= '.php';
    }
    $backendFilePath = __DIR__ . "/../../../../Backend/Admin/" . $targetBackend;
    if (file_exists($backendFilePath)) {
        require_once $backendFilePath;
    }
}

include __DIR__ . "/../../../../Includes/admin_sidebar.php";
?>
