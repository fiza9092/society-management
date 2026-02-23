<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

$page_title = 'Edit Resident';
$id = (int)$_GET['id'];

// Get resident details
$stmt = $conn->prepare("SELECT * FROM residents WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Resident not found";
    redirect('/admin/users.php');
}

$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $alternate_phone = sanitize($_POST['alternate_phone']);
    $address = sanitize($_POST['address']);
    $emergency_contact_name = sanitize($_POST['emergency_contact_name']);
    $emergency_contact_phone = sanitize($_POST['emergency_contact_phone']);
    $status = sanitize($_POST['status']);
    
    $update = $conn->prepare("UPDATE residents SET email = ?, phone = ?, alternate_phone = ?, address = ?, emergency_contact_name = ?, emergency_contact_phone = ?, status = ? WHERE id = ?");
    $update->bind_param('sssssssi', $email, $phone, $alternate_phone, $address, $emergency_contact_name, $emergency_contact_phone, $status, $id);
    
    if ($update->execute()) {
        $_SESSION['success'] = "Resident updated successfully";
        redirect('/admin/users.php');
    } else {
        $error = "Error updating resident: " . $conn->error;
    }
    $update->close();
}

include '../includes/header.php';
?>

<div class="container-fluid px-4">
    <h2 class="mb-4">Edit Resident</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" disabled>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" value="<?php echo $user['username']; ?>" disabled>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Apartment Number</label>
                    <input type="text" class="form-control" value="<?php echo $user['apartment_number']; ?>" disabled>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?php echo $user['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $user['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="blocked" <?php echo $user['status'] == 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Alternate Phone</label>
                    <input type="tel" name="alternate_phone" class="form-control" value="<?php echo htmlspecialchars($user['alternate_phone']); ?>">
                </div>
                
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Emergency Contact Name</label>
                    <input type="text" name="emergency_contact_name" class="form-control" value="<?php echo htmlspecialchars($user['emergency_contact_name']); ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Emergency Contact Phone</label>
                    <input type="tel" name="emergency_contact_phone" class="form-control" value="<?php echo htmlspecialchars($user['emergency_contact_phone']); ?>">
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Resident
                    </button>
                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>