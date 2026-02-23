<?php
require_once 'includes/config.php';

$page_title = 'Reset Password';
$error = '';
$success = '';

$token = isset($_GET['token']) ? sanitize($_GET['token']) : '';
$user_type = isset($_GET['type']) ? sanitize($_GET['type']) : '';

if (!$token || !$user_type) {
    header("Location: index.php");
    exit();
}

$table = ($user_type === 'admin') ? 'admins' : 'residents';

// Verify token
$stmt = $conn->prepare("SELECT id, email FROM $table WHERE reset_token = ? AND reset_expires > NOW()");
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $error = "Invalid or expired reset link.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $error = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match("/[a-z]/", $password)) {
        $error = "Password must contain at least one lowercase letter.";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $error = "Password must contain at least one number.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $user = $result->fetch_assoc();
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $update = $conn->prepare("UPDATE $table SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $update->bind_param('si', $hashed_password, $user['id']);
        
        if ($update->execute()) {
            $success = "Password has been reset successfully. You can now login with your new password.";
        } else {
            $error = "Failed to reset password. Please try again.";
        }
        $update->close();
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Reset Password</h2>
                <p class="text-muted">Enter your new password</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <div class="text-center mt-3">
                    <a href="<?php echo $user_type; ?>/login.php" class="btn btn-primary">Go to Login</a>
                </div>
            <?php else: ?>
                <form method="POST" id="resetForm">
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" 
                               placeholder="Enter new password" required 
                               minlength="8" id="password">
                        <small class="text-muted">Minimum 8 characters with uppercase, lowercase & number</small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" 
                               placeholder="Confirm new password" required id="confirm_password">
                    </div>
                    
                    <div class="password-strength mb-3">
                        <div class="progress" style="height: 5px;">
                            <div id="strength-bar" class="progress-bar" style="width: 0%;"></div>
                        </div>
                        <small id="strength-text" class="text-muted"></small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-sync-alt"></i> Reset Password
                    </button>
                    
                    <div class="text-center">
                        <a href="index.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left"></i> Back to Home
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strength-bar');
            const strengthText = document.getElementById('strength-text');
            
            let strength = 0;
            
            if (password.length >= 8) strength += 25;
            if (password.match(/[A-Z]/)) strength += 25;
            if (password.match(/[a-z]/)) strength += 25;
            if (password.match(/[0-9]/)) strength += 25;
            
            strengthBar.style.width = strength + '%';
            
            if (strength <= 25) {
                strengthBar.className = 'progress-bar bg-danger';
                strengthText.textContent = 'Weak';
            } else if (strength <= 50) {
                strengthBar.className = 'progress-bar bg-warning';
                strengthText.textContent = 'Fair';
            } else if (strength <= 75) {
                strengthBar.className = 'progress-bar bg-info';
                strengthText.textContent = 'Good';
            } else {
                strengthBar.className = 'progress-bar bg-success';
                strengthText.textContent = 'Strong';
            }
        });
        
        // Confirm password match
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>