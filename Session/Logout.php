<?php
require_once('../Session/session.php');

try {
    // 1. Clear all session data
    $_SESSION = [];

    // 2. Delete the session cookie from browser
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 3600,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // 3. Delete all other cookies
    foreach ($_COOKIE as $name => $value) {
        setcookie($name, '', time() - 3600, '/');
    }

    // 4. Destroy the session
    session_destroy();

    // 5. Redirect to login
    header("Location: ../Auth/login.php");
    exit;

} catch (Exception $e) {
    session_destroy();
    header("Location: ../Auth/login.php");
    exit;
}
?>