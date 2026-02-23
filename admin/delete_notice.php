<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

$id = (int)$_GET['id'];

// Check if notice exists
$check = $conn->query("SELECT id FROM notices WHERE id = $id");
if ($check->num_rows === 0) {
    $_SESSION['error'] = "Notice not found";
    redirect('/admin/notices.php');
}

// Delete notice
$stmt = $conn->prepare("DELETE FROM notices WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Notice deleted successfully";
} else {
    $_SESSION['error'] = "Error deleting notice: " . $conn->error;
}

$stmt->close();
redirect('/admin/notices.php');
?>