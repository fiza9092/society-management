<?php
require_once '../includes/config.php';

$page_title = 'Resident Login';

// Redirect if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'resident') {
    redirect('/resident/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Please enter both username/email and password";
    } else {
        // Check login attempts
        $ip = $_SERVER['REMOTE_ADDR'];
        $attempts = isset($_SESSION['login_attempts'][$ip]) ? $_SESSION['login_attempts'][$ip] : 0;
        
        if ($attempts >= 5) {
            $error = "Too many failed attempts. Please try again after 15 minutes.";
        } else {
            // Check if username or email
            $stmt = $conn->prepare("SELECT id, username, full_name, password, status FROM residents WHERE (username = ? OR email = ?)");
            $stmt->bind_param('ss', $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                if ($row['status'] !== 'active') {
                    $error = "Your account is not active. Please contact admin.";
                } elseif (password_verify($password, $row['password'])) {
                    // Login successful
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['user_type'] = 'resident';
                    $_SESSION['user_name'] = $row['full_name'];
                    $_SESSION['user_role'] = 'resident';
                    
                    // Update last login
                    $conn->query("UPDATE residents SET last_login = NOW() WHERE id = {$row['id']}");
                    
                    // Clear login attempts
                    unset($_SESSION['login_attempts'][$ip]);
                    
                    // Regenerate session ID
                    session_regenerate_id(true);
                    
                    // Log activity
                    logActivity($row['id'], 'resident', 'login', 'Resident logged in');
                    
                    redirect('/resident/dashboard.php');
                } else {
                    $error = "Invalid username/email or password";
                    $_SESSION['login_attempts'][$ip] = ($attempts + 1);
                }
            } else {
                $error = "Invalid username/email or password";
                $_SESSION['login_attempts'][$ip] = ($attempts + 1);
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 450px;
            margin: 0 auto;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h2 {
            color: #333;
            font-weight: 700;
        }
        .login-header p {
            color: #666;
            margin-top: 10px;
        }
        .btn-login {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            border: none;
            color: white;
            padding: 12px;
            font-weight: 600;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 233, 123, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="login-header">
                <h2>Resident Login</h2>
                <p>Welcome back! Please login to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username or Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Enter username or email" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="<?php echo SITE_URL; ?>/forgot-password.php?type=resident" class="text-decoration-none">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn btn-login w-100 mb-3">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                
                <div class="text-center">
                    <p class="mb-0">Don't have an account? <a href="#" class="text-decoration-none">Contact Admin</a></p>
                </div>
            </form>
            
            <hr class="my-4">
            
            <div class="text-center">
                <a href="<?php echo SITE_URL; ?>/index.php" class="text-decoration-none">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
    
    <script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        var password = document.querySelector('input[name="password"]');
        var icon = this.querySelector('i');
        
        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>