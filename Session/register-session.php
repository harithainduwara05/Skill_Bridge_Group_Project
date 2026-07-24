<?php
// Session/session.php
// Starts the session and provides small helpers for flash messages

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}