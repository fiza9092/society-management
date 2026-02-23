<?php
// Helper functions

function sanitize($input) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($input)));
}

function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    global $conn;
    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'];
    
    $table = ($user_type === 'admin') ? 'admins' : 'residents';
    $stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function displayMessage() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
}

function generateBillNumber() {
    return 'BILL-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function calculateLateFee($due_date, $amount) {
    $today = new DateTime();
    $due = new DateTime($due_date);
    
    if ($today > $due) {
        $days_late = $today->diff($due)->days;
        $late_fee = $amount * 0.02 * ceil($days_late / 30); // 2% per month
        return min($late_fee, $amount * 0.1); // Max 10% late fee
    }
    return 0;
}

function logActivity($user_id, $user_type, $action, $description) {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, user_type, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isssss', $user_id, $user_type, $action, $description, $ip, $user_agent);
    $stmt->execute();
    $stmt->close();
}

function sendEmail($to, $subject, $message) {
    // Using PHP's mail function (simplified)
    $headers = "From: " . SMTP_FROM . "\r\n";
    $headers .= "Reply-To: " . SMTP_FROM . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

function getDashboardStats($user_type, $user_id = null) {
    global $conn;
    $stats = [];
    
    if ($user_type === 'admin') {
        $stats['total_residents'] = $conn->query("SELECT COUNT(*) FROM residents")->fetch_row()[0];
        $stats['total_notices'] = $conn->query("SELECT COUNT(*) FROM notices")->fetch_row()[0];
        $stats['pending_complaints'] = $conn->query("SELECT COUNT(*) FROM complaints WHERE status = 'pending'")->fetch_row()[0];
        $stats['total_bills'] = $conn->query("SELECT COUNT(*) FROM bills")->fetch_row()[0];
        $stats['pending_bills'] = $conn->query("SELECT COUNT(*) FROM bills WHERE status = 'pending'")->fetch_row()[0];
        $stats['total_payments'] = $conn->query("SELECT SUM(amount) FROM payments")->fetch_row()[0] ?? 0;
    } else {
        $stats['my_bills'] = $conn->query("SELECT COUNT(*) FROM bills WHERE resident_id = $user_id")->fetch_row()[0];
        $stats['pending_bills'] = $conn->query("SELECT COUNT(*) FROM bills WHERE resident_id = $user_id AND status = 'pending'")->fetch_row()[0];
        $stats['my_complaints'] = $conn->query("SELECT COUNT(*) FROM complaints WHERE resident_id = $user_id")->fetch_row()[0];
        $stats['open_complaints'] = $conn->query("SELECT COUNT(*) FROM complaints WHERE resident_id = $user_id AND status IN ('pending', 'in_progress')")->fetch_row()[0];
        $stats['total_paid'] = $conn->query("SELECT SUM(amount) FROM payments WHERE resident_id = $user_id")->fetch_row()[0] ?? 0;
    }
    
    return $stats;
}
?>