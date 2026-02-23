<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

$page_title = 'Edit Bill';
$error = '';
$id = (int)$_GET['id'];

// Get bill details
$stmt = $conn->prepare("SELECT b.*, r.full_name, r.apartment_number FROM bills b JOIN residents r ON b.resident_id = r.id WHERE b.id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Bill not found";
    redirect('/admin/bills.php');
}

$bill = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $due_date = $_POST['due_date'];
    $status = sanitize($_POST['status']);
    $description = sanitize($_POST['description']);

    $update = $conn->prepare("UPDATE bills SET amount = ?, due_date = ?, status = ?, description = ? WHERE id = ?");
    $update->bind_param('dsssi', $amount, $due_date, $status, $description, $id);
    
    if ($update->execute()) {
        $_SESSION['success'] = "Bill updated successfully";
        redirect('/admin/bills.php');
    } else {
        $error = "Error updating bill: " . $conn->error;
    }
    $update->close();
}

include '../includes/header.php';
?>

<div class="container-fluid px-4">
    <h2 class="mb-4">Edit Bill #<?php echo $bill['bill_number']; ?></h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <strong>Resident:</strong> <?php echo htmlspecialchars($bill['full_name']); ?> (Flat: <?php echo $bill['apartment_number']; ?>)
                </div>
                <div class="col-md-6">
                    <strong>Bill Type:</strong> <?php echo ucfirst($bill['bill_type']); ?>
                </div>
            </div>
            
            <form method="POST" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Amount (₹) *</label>
                    <input type="number" step="0.01" name="amount" class="form-control" 
                           value="<?php echo $bill['amount']; ?>" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Due Date *</label>
                    <input type="date" name="due_date" class="form-control" 
                           value="<?php echo $bill['due_date']; ?>" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="pending" <?php echo $bill['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="paid" <?php echo $bill['status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="overdue" <?php echo $bill['status'] == 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                    </select>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($bill['description']); ?></textarea>
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Bill
                    </button>
                    <a href="bills.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>