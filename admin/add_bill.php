<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

$page_title = 'Add New Bill';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resident_id = (int)$_POST['resident_id'];
    $amount = floatval($_POST['amount']);
    $month = (int)$_POST['month'];
    $year = (int)$_POST['year'];
    $due_date = $_POST['due_date'];
    $bill_type = sanitize($_POST['bill_type']);
    $description = sanitize($_POST['description']);

    // Validation
    if (!$resident_id || !$amount || !$month || !$year || !$due_date) {
        $error = "All fields are required";
    } else {
        // Generate bill number
        $bill_number = 'BILL-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($resident_id, 3, '0', STR_PAD_LEFT);
        
        $stmt = $conn->prepare("INSERT INTO bills (bill_number, resident_id, amount, month, year, due_date, bill_type, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('siddisss', $bill_number, $resident_id, $amount, $month, $year, $due_date, $bill_type, $description);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Bill added successfully";
            redirect('/admin/bills.php');
        } else {
            $error = "Error adding bill: " . $conn->error;
        }
        $stmt->close();
    }
}

// Get residents for dropdown
$residents = $conn->query("SELECT id, full_name, apartment_number FROM residents WHERE status = 'active' ORDER BY apartment_number");

include '../includes/header.php';
?>

<div class="container-fluid px-4">
    <h2 class="mb-4">Add New Bill</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Resident *</label>
                    <select name="resident_id" class="form-select" required>
                        <option value="">Select Resident</option>
                        <?php while($row = $residents->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>">
                                <?php echo $row['apartment_number'] . ' - ' . htmlspecialchars($row['full_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Bill Type *</label>
                    <select name="bill_type" class="form-select" required>
                        <option value="maintenance">Maintenance</option>
                        <option value="water">Water</option>
                        <option value="electricity">Electricity</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Amount (₹) *</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Month *</label>
                    <select name="month" class="form-select" required>
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>"><?php echo date('F', mktime(0,0,0,$m,1)); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Year *</label>
                    <select name="year" class="form-select" required>
                        <?php for($y = date('Y'); $y <= date('Y')+1; $y++): ?>
                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Due Date *</label>
                    <input type="date" name="due_date" class="form-control" required 
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Bill
                    </button>
                    <a href="bills.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>