<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

$page_title = 'Admin Dashboard';
$stats = getDashboardStats('admin');

// Get recent activities
$recent_complaints = $conn->query("SELECT c.*, r.full_name, r.apartment_number 
                                   FROM complaints c 
                                   JOIN residents r ON c.resident_id = r.id 
                                   ORDER BY c.created_at DESC LIMIT 5");

$recent_bills = $conn->query("SELECT b.*, r.full_name, r.apartment_number 
                             FROM bills b 
                             JOIN residents r ON b.resident_id = r.id 
                             ORDER BY b.created_at DESC LIMIT 5");

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Admin Dashboard</h2>
    <div>
        <span class="badge bg-primary">Last Login: <?php echo date('d M Y H:i', strtotime($_SESSION['last_login'] ?? 'now')); ?></span>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="dashboard-card">
            <div class="card-icon bg-primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="card-value"><?php echo $stats['total_residents']; ?></div>
            <div class="card-label">Total Residents</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="dashboard-card">
            <div class="card-icon bg-success">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="card-value">₹<?php echo number_format($stats['total_payments'], 2); ?></div>
            <div class="card-label">Total Collections</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="dashboard-card">
            <div class="card-icon bg-warning">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="card-value"><?php echo $stats['pending_complaints']; ?></div>
            <div class="card-label">Pending Complaints</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="dashboard-card">
            <div class="card-icon bg-danger">
                <i class="fas fa-clock"></i>
            </div>
            <div class="card-value"><?php echo $stats['pending_bills']; ?></div>
            <div class="card-label">Pending Bills</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Complaints -->
    <div class="col-lg-6 mb-4">
        <div class="table-container">
            <h5 class="mb-3">Recent Complaints</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Resident</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($complaint = $recent_complaints->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($complaint['full_name']); ?><br>
                                <small>Flat: <?php echo $complaint['apartment_number']; ?></small>
                            </td>
                            <td><?php echo htmlspecialchars(substr($complaint['title'], 0, 30)) . '...'; ?></td>
                            <td><span class="badge badge-<?php echo $complaint['status']; ?>"><?php echo $complaint['status']; ?></span></td>
                            <td>
                                <a href="view_complaint.php?id=<?php echo $complaint['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Bills -->
    <div class="col-lg-6 mb-4">
        <div class="table-container">
            <h5 class="mb-3">Recent Bills</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Bill No.</th>
                            <th>Resident</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($bill = $recent_bills->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $bill['bill_number']; ?></td>
                            <td><?php echo htmlspecialchars($bill['full_name']); ?><br>
                                <small>Flat: <?php echo $bill['apartment_number']; ?></small>
                            </td>
                            <td>₹<?php echo number_format($bill['total_amount'], 2); ?></td>
                            <td><span class="badge badge-<?php echo $bill['status']; ?>"><?php echo $bill['status']; ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>