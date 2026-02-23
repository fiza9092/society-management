<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    redirect('/' . $_SESSION['user_type'] . '/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero-section {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            text-align: center;
        }
        
        .feature-box {
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s;
        }
        
        .feature-box:hover {
            transform: translateY(-10px);
        }
        
        .feature-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="container">
            <h1 class="display-3 fw-bold mb-4"><?php echo SITE_NAME; ?></h1>
            <p class="lead mb-5">Streamline your society management with our comprehensive solution</p>
            
            <div class="row mb-5">
                <div class="col-md-4 mb-4">
                    <div class="feature-box">
                        <div class="feature-icon"><i class="fas fa-users"></i></div>
                        <h3>Resident Management</h3>
                        <p>Easily manage all residents and their information</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-box">
                        <div class="feature-icon"><i class="fas fa-file-invoice"></i></div>
                        <h3>Bill Management</h3>
                        <p>Create and track bills with automatic late fee calculation</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-box">
                        <div class="feature-icon"><i class="fas fa-exclamation-circle"></i></div>
                        <h3>Complaint System</h3>
                        <p>Residents can submit and track complaints easily</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-5">
                <a href="admin/login.php" class="btn btn-light btn-lg me-3 px-5">Admin Login</a>
                <a href="resident/login.php" class="btn btn-outline-light btn-lg px-5">Resident Login</a>
            </div>
        </div>
    </div>
</body>
</html>