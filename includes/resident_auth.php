<?php
// Resident Authentication Check

// Define SITE_URL if not defined (for Railway)
if (!defined('SITE_URL')) {
    // Auto-detect URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('SITE_URL', $protocol . $host);
}

// Check if user is logged in as resident
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'resident') {
    $_SESSION['error'] = "Please login to access your dashboard";
    $login_url = SITE_URL . "/resident/login.php";
    header("Location: $login_url");
    exit();
}

// Get resident details
$resident_id = $_SESSION['user_id'];
$resident_name = $_SESSION['user_name'] ?? 'Resident';

// Check if resident is active
if (isset($conn) && $conn) {
    $check = $conn->query("SELECT status FROM residents WHERE id = $resident_id");
    if ($check && $check->num_rows > 0) {
        $resident = $check->fetch_assoc();
        if ($resident['status'] !== 'active') {
            session_destroy();
            $_SESSION['error'] = "Your account has been deactivated. Please contact admin";
            $login_url = SITE_URL . "/resident/login.php";
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
    $login_url = SITE_URL . "/resident/login.php";
    header("Location: $login_url");
    exit();
}
?>