<?php
// Admin Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    $_SESSION['error'] = "Please login to access the admin panel";
    header("Location: " . SITE_URL . "/admin/login.php");
    exit();
}

// Get admin details
$admin_id = $_SESSION['user_id'];
$admin_name = $_SESSION['user_name'] ?? 'Admin';

// Check if admin is active
$check = $conn->query("SELECT status FROM admins WHERE id = $admin_id");
if ($check->num_rows > 0) {
    $admin = $check->fetch_assoc();
    if ($admin['status'] !== 'active') {
        session_destroy();
        $_SESSION['error'] = "Your account has been deactivated";
        header("Location: " . SITE_URL . "/admin/login.php");
        exit();
    }
}

// Update last activity
$_SESSION['last_activity'] = time();

// Check session timeout (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    $_SESSION['error'] = "Session expired. Please login again";
    header("Location: " . SITE_URL . "/admin/login.php");
    exit();
}

// Set user role for permissions
$user_role = $_SESSION['user_role'] ?? 'admin';
?>