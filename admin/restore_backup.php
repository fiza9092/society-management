<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

if ($_SESSION['user_role'] !== 'super_admin') {
    die("Access denied");
}

$file = $_GET['file'] ?? '';
$backup_path = '../backups/' . basename($file);

if (!file_exists($backup_path)) {
    die("Backup file not found");
}

// Read backup file
$sql = file_get_contents($backup_path);

// Execute restore
if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    
    $_SESSION['success'] = "Database restored successfully from: " . $file;
} else {
    $_SESSION['error'] = "Restore failed: " . $conn->error;
}

redirect('/admin/settings.php#backup');
?>