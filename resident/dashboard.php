<?php
require_once '../includes/config.php';
require_once '../includes/resident_auth.php';

$page_title = 'Resident Dashboard';
$resident_id = $_SESSION['user_id'];

// Get resident details
$resident = $conn->query("SELECT * FROM residents WHERE id = $resident_id")->fetch_assoc();

// Get stats
$stats = [
    'total_bills' => $conn->query("SELECT COUNT(*) FROM bills WHERE resident_id = $resident_id")->fetch_row()[0],
    'pending_bills' => $conn->query("SELECT COUNT(*) FROM bills WHERE resident_id = $resident_id AND status = 'pending'")->fetch_row()[0],
    'paid_bills' => $conn->query("SELECT COUNT(*) FROM bills WHERE resident_id = $resident_id AND status = 'paid'")->fetch_row()[0],
    'total_complaints' => $conn->query("SELECT COUNT(*) FROM complaints WHERE resident_id = $resident_id")->fetch_row()[0],
    'open_complaints' => $conn->query("SELECT COUNT(*) FROM complaints WHERE resident_id = $resident_id AND status IN ('pending', 'in_progress')")->fetch_row()[0],
    'total_paid' => $conn->query("SELECT SUM(amount) FROM payments WHERE resident_id = $resident_id")->fetch_row()[0] ?? 0
];

// Get recent notices
$notices = $conn->query("SELECT * FROM notices WHERE (expires_at IS NULL OR expires_at > NOW()) ORDER BY priority DESC, created_at DESC LIMIT 5");

// Get recent bills
$bills = $conn->query("SELECT * FROM bills WHERE resident_id = $resident_id ORDER BY created_at DESC LIMIT 5");

include '../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Welcome back, <?php echo htmlspecialchars($resident['full_name']); ?>!</h2>
        <div>
            <span class="badge bg-primary">Flat: <?php echo $resident['apartment_number']; ?></span>
            <span class="badge bg-info">Last Login: <?php echo date('d M Y H:i', strtotime($_SESSION['last_login'] ?? 'now')); ?></span>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="dashboard-card">
                <div class="card-icon bg-primary">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="card-value"><?php echo $stats['total_bills']; ?></div>
                <div class="card-label">Total Bills</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="dashboard-card">
                <div class="card-icon bg-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="card-value"><?php echo $stats['pending_bills']; ?></div>
                <div class="card-label">Pending Bills</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="dashboard-card">
                <div class="card-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="card-value">₹<?php echo number_format($stats['total_paid'], 2); ?></div>
                <div class="card-label">Total Paid</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="dashboard-card">
                <div class="card-icon bg-danger">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="card-value"><?php echo $stats['open_complaints']; ?></div>
                <div class="card-label">Open Complaints</div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Quick Actions -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="bills.php" class="btn btn-primary">
                            <i class="fas fa-file-invoice"></i> View My Bills
                        </a>
                        <a href="complaints.php" class="btn btn-warning">
                            <i class="fas fa-exclamation-circle"></i> Submit Complaint
                        </a>
                        <a href="notices.php" class="btn btn-info">
                            <i class="fas fa-bullhorn"></i> View Notices
                        </a>
                        <a href="profile.php" class="btn btn-secondary">
                            <i class="fas fa-user"></i> Update Profile
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Payment Summary -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Payment Summary</h5>
                </div>
                <div class="card-body">
                    <canvas id="paymentChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Bills -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Bills</h5>
                    <a href="bills.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Bill No.</th>
                                    <th>Month/Year</th>
                                    <th>Amount</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($bills->num_rows > 0): ?>
                                    <?php while($bill = $bills->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $bill['bill_number']; ?></td>
                                        <td><?php echo date('F Y', mktime(0,0,0,$bill['month'],1,$bill['year'])); ?></td>
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
                                            <?php if($bill['status'] == 'pending'): ?>
                                                <a href="pay_bill.php?id=<?php echo $bill['id']; ?>" class="btn btn-sm btn-success">
                                                    Pay Now
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No bills found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Recent Notices -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Notices</h5>
                    <a href="notices.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if ($notices->num_rows > 0): ?>
                        <?php while($notice = $notices->fetch_assoc()): ?>
                            <div class="notice-card mb-3">
                                <div class="d-flex justify-content-between">
                                    <h6 class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></h6>
                                    <span class="badge bg-<?php 
                                        echo $notice['priority'] == 'urgent' ? 'danger' : 
                                            ($notice['priority'] == 'high' ? 'warning' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst($notice['priority']); ?>
                                    </span>
                                </div>
                                <p class="small"><?php echo htmlspecialchars(substr($notice['content'], 0, 100)); ?>...</p>
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($notice['created_at'])); ?>
                                </small>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">No notices available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Payment Chart
var ctx = document.getElementById('paymentChart').getContext('2d');
var chart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Paid (₹<?php echo number_format($stats['total_paid'], 2); ?>)', 
                 'Pending (₹<?php echo number_format($stats['pending_bills'] * 2500, 2); ?>)'],
        datasets: [{
            data: [<?php echo $stats['total_paid']; ?>, <?php echo $stats['pending_bills'] * 2500; ?>],
            backgroundColor: ['#28a745', '#ffc107'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        cutout: '70%',
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>