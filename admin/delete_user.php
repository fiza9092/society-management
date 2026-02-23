<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

$id = (int)$_GET['id'];

// Don't allow deleting own account
if ($id == $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account";
    redirect('/admin/users.php');
}

// Check if resident has any related records
$check_bills = $conn->query("SELECT id FROM bills WHERE resident_id = $id LIMIT 1");
$check_complaints = $conn->query("SELECT id FROM complaints WHERE resident_id = $id LIMIT 1");
$check_payments = $conn->query("SELECT id FROM payments WHERE resident_id = $id LIMIT 1");

if ($check_bills->num_rows > 0 || $check_complaints->num_rows > 0 || $check_payments->num_rows > 0) {
    $_SESSION['error'] = "Cannot delete resident with existing bills, complaints or payments. Mark as inactive instead.";
    redirect('/admin/users.php');
}

// Delete resident
$stmt = $conn->prepare("DELETE FROM residents WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Resident deleted successfully";
} else {
    $_SESSION['error'] = "Error deleting resident: " . $conn->error;
}

$stmt->close();
redirect('/admin/users.php');
?>