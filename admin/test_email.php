<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test'])) {
    $to = $_SESSION['user_email'] ?? ADMIN_EMAIL;
    $subject = "Test Email from " . SITE_NAME;
    $message = "<h3>Test Email</h3><p>This is a test email from your Society Management System.</p><p>If you received this, your email settings are working correctly!</p>";
    
    $headers = "From: " . SMTP_FROM . "\r\n";
    $headers .= "Reply-To: " . SMTP_FROM . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    if (mail($to, $subject, $message, $headers)) {
        echo "success";
    } else {
        echo "failed";
    }
}
?>