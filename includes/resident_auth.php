<?php
// Resident Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'resident') {
    $_SESSION['error'] = "Please login to access your dashboard";
    header("Location: " . SITE_URL . "/resident/login.php");
    exit();
}

// Get resident details
$resident_id = $_SESSION['user_id'];
$resident_name = $_SESSION['user_name'] ?? 'Resident';

// Check if resident is active
$check = $conn->query("SELECT status FROM residents WHERE id = $resident_id");
if ($check->num_rows > 0) {
    $resident = $check->fetch_assoc();
    if ($resident['status'] !== 'active') {
        session_destroy();
        $_SESSION['error'] = "Your account has been deactivated. Please contact admin";
        header("Location: " . SITE_URL . "/resident/login.php");
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
    header("Location: " . SITE_URL . "/resident/login.php");
    exit();
}
?>