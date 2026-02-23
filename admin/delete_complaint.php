<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

$id = (int)$_GET['id'];

// Check if complaint exists
$check = $conn->query("SELECT id FROM complaints WHERE id = $id");
if ($check->num_rows === 0) {
    $_SESSION['error'] = "Complaint not found";
    redirect('/admin/complaints.php');
}

// Delete complaint
$stmt = $conn->prepare("DELETE FROM complaints WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Complaint deleted successfully";
} else {
    $_SESSION['error'] = "Error deleting complaint: " . $conn->error;
}

$stmt->close();
redirect('/admin/complaints.php');
?>