<?php
require_once '../includes/config.php';
require_once '../includes/resident_auth.php';

$page_title = 'My Bills';
$resident_id = $_SESSION['user_id'];

// Get all bills
$bills = $conn->query("SELECT * FROM bills WHERE resident_id = $resident_id ORDER BY year DESC, month DESC");

include '../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Bills</h2>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    
    <!-- Summary Cards -->
    <?php
    $summary = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN total_amount ELSE 0 END) as pending_amount,
        SUM(CASE WHEN status = 'overdue' THEN total_amount ELSE 0 END) as overdue_amount,
        SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as paid_amount
        FROM bills WHERE resident_id = $resident_id")->fetch_assoc();
    ?>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="card-icon bg-info">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="card-value"><?php echo $summary['total']; ?></div>
                <div class="card-label">Total Bills</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="card-icon bg-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="card-value">₹<?php echo number_format($summary['pending_amount'], 2); ?></div>
                <div class="card-label">Pending Amount</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="card-icon bg-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="card-value">₹<?php echo number_format($summary['overdue_amount'], 2); ?></div>
                <div class="card-label">Overdue Amount</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="card-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="card-value">₹<?php echo number_format($summary['paid_amount'], 2); ?></div>
                <div class="card-label">Paid Amount</div>
            </div>
        </div>
    </div>
    
    <!-- Bills Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Bill No.</th>
                        <th>Period</th>
                        <th>Bill Type</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Payment Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bills->num_rows > 0): ?>
                        <?php while($bill = $bills->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $bill['bill_number']; ?></strong></td>
                            <td><?php echo date('F Y', mktime(0,0,0,$bill['month'],1,$bill['year'])); ?></td>
                            <td><?php echo ucfirst($bill['bill_type']); ?></td>
                            <td>
                                ₹<?php echo number_format($bill['total_amount'], 2); ?>
                                <?php if($bill['late_fee'] > 0): ?>
                                    <br><small class="text-danger">(+₹<?php echo $bill['late_fee']; ?> late fee)</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo date('d M Y', strtotime($bill['due_date'])); ?>
                                <?php if(strtotime($bill['due_date']) < time() && $bill['status'] == 'pending'): ?>
                                    <br><small class="text-danger">Overdue</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $bill['status'] == 'paid' ? 'success' : 
                                        ($bill['status'] == 'pending' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php echo ucfirst($bill['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($bill['payment_date']): ?>
                                    <?php echo date('d M Y', strtotime($bill['payment_date'])); ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($bill['status'] == 'pending'): ?>
                                    <a href="pay_bill.php?id=<?php echo $bill['id']; ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-credit-card"></i> Pay Now
                                    </a>
                                <?php elseif($bill['status'] == 'paid'): ?>
                                    <button class="btn btn-sm btn-outline-success" disabled>
                                        <i class="fas fa-check"></i> Paid
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">No bills found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>