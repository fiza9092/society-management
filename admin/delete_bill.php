<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

$id = (int)$_GET['id'];

// Check if bill has payments
$check = $conn->query("SELECT id FROM payments WHERE bill_id = $id");
if ($check->num_rows > 0) {
    $_SESSION['error'] = "Cannot delete bill with existing payments";
    redirect('/admin/bills.php');
}

$stmt = $conn->prepare("DELETE FROM bills WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Bill deleted successfully";
} else {
    $_SESSION['error'] = "Error deleting bill: " . $conn->error;
}

$stmt->close();
redirect('/admin/bills.php');
?>