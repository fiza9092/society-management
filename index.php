<?php
require_once 'includes/config.php';

// Simple check - if logged in, go to dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    $dashboard_path = $_SESSION['user_type'] . '/dashboard.php';
    header("Location: $dashboard_path");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Society Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: white;
        }
        .btn-light {
            padding: 12px 30px;
            border-radius: 25px;
            margin: 10px;
        }
    </style>
</head>
<body>
    <div class="container text-center">
        <h1 class="display-3 mb-4">Society Management System</h1>
        <p class="lead mb-5">Streamline your society management with our comprehensive solution</p>
        
        <div class="mt-5">
            <a href="admin/login.php" class="btn btn-light btn-lg">Admin Login</a>
            <a href="resident/login.php" class="btn btn-outline-light btn-lg">Resident Login</a>
        </div>
    </div>
</body>
</html>