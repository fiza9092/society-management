<?php
require_once 'includes/config.php';

$page_title = 'Forgot Password';
$step = isset($_GET['step']) ? $_GET['step'] : 'request';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 'request') {
        $email = sanitize($_POST['email']);
        $user_type = sanitize($_POST['user_type']);
        
        $table = ($user_type === 'admin') ? 'admins' : 'residents';
        $stmt = $conn->prepare("SELECT id, full_name FROM $table WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $update = $conn->prepare("UPDATE $table SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $update->bind_param('ssi', $token, $expires, $row['id']);
            $update->execute();
            
            // Send email
            $reset_link = SITE_URL . "/reset-password.php?token=$token&type=$user_type";
            $subject = "Password Reset Request - " . SITE_NAME;
            $message = "
            <html>
            <body>
                <h2>Password Reset Request</h2>
                <p>Dear " . htmlspecialchars($row['full_name']) . ",</p>
                <p>You requested to reset your password. Click the link below to proceed:</p>
                <p><a href='$reset_link'>$reset_link</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request this, please ignore this email.</p>
                <br>
                <p>Regards,<br>" . SITE_NAME . " Team</p>
            </body>
            </html>
            ";
            
            if (sendEmail($email, $subject, $message)) {
                $success = "Password reset link has been sent to your email.";
                $step = 'sent';
            } else {
                $error = "Failed to send email. Please try again.";
            }
        } else {
            $error = "Email not found in our records.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Forgot Password</h2>
                <p class="text-muted">Reset your password securely</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($step === 'request'): ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Select Account Type</label>
                        <select name="user_type" class="form-select" required>
                            <option value="resident">Resident</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter your registered email" required>
                        <small class="text-muted">We'll send a password reset link to this email</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-paper-plane"></i> Send Reset Link
                    </button>
                    
                    <div class="text-center">
                        <a href="index.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left"></i> Back to Home
                        </a>
                    </div>
                </form>
            <?php endif; ?>
            
            <?php if ($step === 'sent'): ?>
                <div class="text-center">
                    <i class="fas fa-envelope text-success" style="font-size: 64px;"></i>
                    <p class="mt-3">Check your email for the password reset link.</p>
                    <a href="index.php" class="btn btn-primary">Go to Home</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>