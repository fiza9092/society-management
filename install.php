<?php
// First time installation script
session_start();

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        // Test database connection
        $host = $_POST['db_host'];
        $user = $_POST['db_user'];
        $pass = $_POST['db_pass'];
        $name = $_POST['db_name'];
        
        $conn = new mysqli($host, $user, $pass);
        if ($conn->connect_error) {
            $error = "Connection failed: " . $conn->connect_error;
        } else {
            // Create database if not exists
            $conn->query("CREATE DATABASE IF NOT EXISTS $name");
            $conn->select_db($name);
            
            // Save credentials to session
            $_SESSION['db_host'] = $host;
            $_SESSION['db_user'] = $user;
            $_SESSION['db_pass'] = $pass;
            $_SESSION['db_name'] = $name;
            
            header("Location: install.php?step=2");
            exit();
        }
    } elseif ($step === 2) {
        // Import database schema
        $host = $_SESSION['db_host'];
        $user = $_SESSION['db_user'];
        $pass = $_SESSION['db_pass'];
        $name = $_SESSION['db_name'];
        
        $conn = new mysqli($host, $user, $pass, $name);
        
        // Read and execute schema
        $schema = file_get_contents('database.sql');
        if ($conn->multi_query($schema)) {
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->next_result());
            
            header("Location: install.php?step=3");
            exit();
        } else {
            $error = "Error importing database: " . $conn->error;
        }
    } elseif ($step === 3) {
        // Create admin account
        $host = $_SESSION['db_host'];
        $user = $_SESSION['db_user'];
        $pass = $_SESSION['db_pass'];
        $name = $_SESSION['db_name'];
        
        $conn = new mysqli($host, $user, $pass, $name);
        
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $full_name = sanitize($_POST['full_name']);
        
        $stmt = $conn->prepare("INSERT INTO admins (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'super_admin')");
        $stmt->bind_param('ssss', $username, $email, $password, $full_name);
        
        if ($stmt->execute()) {
            // Create config file
            $config = "<?php
define('DB_HOST', '$host');
define('DB_USER', '$user');
define('DB_PASS', '$pass');
define('DB_NAME', '$name');
define('SITE_NAME', 'Society Management System');
define('SITE_URL', 'http://' . \$_SERVER['HTTP_HOST'] . '/society-management');
?>";

file_put_contents('includes/config.php', $config);
            
            $_SESSION['success'] = "Installation complete! You can now login.";
            header("Location: admin/login.php");
            exit();
        } else {
            $error = "Error creating admin: " . $conn->error;
        }
    }
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Society Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .installer-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .installer-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 1;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            position: relative;
            z-index: 2;
        }
        .step.active {
            background: #667eea;
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container installer-container">
        <div class="installer-card">
            <h2 class="text-center mb-4">Society Management System - Installation</h2>
            
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">1</div>
                <div class="step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">2</div>
                <div class="step <?php echo $step >= 3 ? 'active' : ''; ?> <?php echo $step > 3 ? 'completed' : ''; ?>">3</div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($step === 1): ?>
                <form method="POST">
                    <h5 class="mb-3">Step 1: Database Configuration</h5>
                    <div class="mb-3">
                        <label class="form-label">Database Host</label>
                        <input type="text" name="db_host" class="form-control" value="localhost" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Database Username</label>
                        <input type="text" name="db_user" class="form-control" value="root" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Database Password</label>
                        <input type="password" name="db_pass" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Database Name</label>
                        <input type="text" name="db_name" class="form-control" value="society_management" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Test Connection & Continue</button>
                </form>
                
            <?php elseif ($step === 2): ?>
                <div class="text-center">
                    <h5 class="mb-3">Step 2: Creating Database Tables</h5>
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Please wait while we set up your database...</p>
                    <meta http-equiv="refresh" content="2">
                </div>
                
            <?php elseif ($step === 3): ?>
                <form method="POST">
                    <h5 class="mb-3">Step 3: Create Admin Account</h5>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Complete Installation</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>