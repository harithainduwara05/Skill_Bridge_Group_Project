<?php
header('Content-Type: application/json');
require_once('../Session/session.php');
try{
    session_destroy();
    ob_clean();
    echo json_encode(['status' => 'success', 'message' => 'Logged out successfully.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred during logout.']);
}
exit;
?>