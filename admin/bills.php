<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

$page_title = 'Manage Bills';

// Handle filters
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$query = "SELECT b.*, r.full_name, r.apartment_number 
          FROM bills b 
          JOIN residents r ON b.resident_id = r.id 
          WHERE b.month = $month AND b.year = $year";

if ($status) {
    $query .= " AND b.status = '$status'";
}
$query .= " ORDER BY r.apartment_number";

$bills = $conn->query($query);

// Get summary
$summary = $conn->query("SELECT 
    COUNT(*) as total_bills,
    SUM(CASE WHEN status = 'pending' THEN total_amount ELSE 0 END) as pending_amount,
    SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as collected_amount
    FROM bills WHERE month = $month AND year = $year")->fetch_assoc();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Bills</h2>
    <a href="add_bill.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Generate Bills
    </a>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="dashboard-card">
            <div class="card-icon bg-info">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="card-value"><?php echo $summary['total_bills'] ?? 0; ?></div>
            <div class="card-label">Total Bills</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card">
            <div class="card-icon bg-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="card-value">₹<?php echo number_format($summary['pending_amount'] ?? 0, 2); ?></div>
            <div class="card-label">Pending Amount</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card">
            <div class="card-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="card-value">₹<?php echo number_format($summary['collected_amount'] ?? 0, 2); ?></div>
            <div class="card-label">Collected Amount</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="table-container mb-4">
    <form method="GET" class="row g-3">
        <div class="col-md-3">
            <select class="form-select" name="month">
                <?php for($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo $m; ?>" <?php echo $month == $m ? 'selected' : ''; ?>>
                        <?php echo date('F', mktime(0,0,0,$m,1)); ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" name="year">
                <?php for($y = date('Y'); $y >= 2020; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                        <?php echo $y; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" name="status">
                <option value="">All Status</option>
                <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="paid" <?php echo $status == 'paid' ? 'selected' : ''; ?>>Paid</option>
                <option value="overdue" <?php echo $status == 'overdue' ? 'selected' : ''; ?>>Overdue</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
        </div>
    </form>
</div>

<!-- Bills Table -->
<div class="table-container">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Bill No.</th>
                    <th>Resident</th>
                    <th>Flat</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($bills && $bills->num_rows > 0): ?>
                    <?php while($bill = $bills->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $bill['bill_number']; ?></strong></td>
                        <td><?php echo htmlspecialchars($bill['full_name']); ?></td>
                        <td><?php echo $bill['apartment_number']; ?></td>
                        <td>₹<?php echo number_format($bill['total_amount'], 2); ?></td>
                        <td><?php echo date('d M Y', strtotime($bill['due_date'])); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $bill['status'] == 'paid' ? 'success' : 
                                    ($bill['status'] == 'pending' ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo ucfirst($bill['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_bill.php?id=<?php echo $bill['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if($bill['status'] == 'pending'): ?>
                            <a href="mark_paid.php?id=<?php echo $bill['id']; ?>" class="btn btn-sm btn-success" title="Mark as Paid">
                                <i class="fas fa-check"></i>
                            </a>
                            <?php endif; ?>
                            <a href="delete_bill.php?id=<?php echo $bill['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this bill?')"
                               title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">No bills found for selected period</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>