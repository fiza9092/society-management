<?php
require_once 'includes/config.php';

// Log activity if user was logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    logActivity($_SESSION['user_id'], $_SESSION['user_type'], 'logout', 'User logged out');
}

// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to home page
header("Location: " . SITE_URL . "/index.php");
exit();