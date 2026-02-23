<?php
// Admin Authentication Check

// Define SITE_URL if not defined (for Railway)
if (!defined('SITE_URL')) {
    // Auto-detect URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('SITE_URL', $protocol . $host);
}

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    $_SESSION['error'] = "Please login to access the admin panel";
    $login_url = SITE_URL . "/admin/login.php";
    header("Location: $login_url");
    exit();
}

// Get admin details
$admin_id = $_SESSION['user_id'];
$admin_name = $_SESSION['user_name'] ?? 'Admin';

// Check if admin is active
if (isset($conn) && $conn) {
    $check = $conn->query("SELECT status FROM admins WHERE id = $admin_id");
    if ($check && $check->num_rows > 0) {
        $admin = $check->fetch_assoc();
        if ($admin['status'] !== 'active') {
            session_destroy();
            $_SESSION['error'] = "Your account has been deactivated";
            $login_url = SITE_URL . "/admin/login.php";
            header("Location: $login_url");
            exit();
        }
    }
}

// Update last activity
$_SESSION['last_activity'] = time();

// Check session timeout (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    $_SESSION['error'] = "Session expired. Please login again";
    $login_url = SITE_URL . "/admin/login.php";
    header("Location: $login_url");
    exit();
}

// Set user role for permissions
$user_role = $_SESSION['user_role'] ?? 'admin';
?>