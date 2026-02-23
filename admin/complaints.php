<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

$page_title = 'Manage Complaints';

// Handle filters
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

$query = "SELECT c.*, r.full_name, r.apartment_number, r.phone 
          FROM complaints c 
          JOIN residents r ON c.resident_id = r.id 
          WHERE 1=1";

if ($status) {
    $query .= " AND c.status = '$status'";
}
if ($category) {
    $query .= " AND c.category = '$category'";
}
$query .= " ORDER BY 
            CASE c.priority 
                WHEN 'urgent' THEN 1 
                WHEN 'high' THEN 2 
                WHEN 'medium' THEN 3 
                WHEN 'low' THEN 4 
            END, c.created_at DESC";

$complaints = $conn->query($query);

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Complaints</h2>
</div>

<!-- Stats Cards -->
<?php
$stats = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent
    FROM complaints")->fetch_assoc();
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="dashboard-card">
            <div class="card-icon bg-info">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="card-value"><?php echo $stats['total']; ?></div>
            <div class="card-label">Total Complaints</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card">
            <div class="card-icon bg-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="card-value"><?php echo $stats['pending']; ?></div>
            <div class="card-label">Pending</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card">
            <div class="card-icon bg-primary">
                <i class="fas fa-spinner"></i>
            </div>
            <div class="card-value"><?php echo $stats['in_progress']; ?></div>
            <div class="card-label">In Progress</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card">
            <div class="card-icon bg-danger">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="card-value"><?php echo $stats['urgent']; ?></div>
            <div class="card-label">Urgent</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="table-container mb-4">
    <form method="GET" class="row g-3">
        <div class="col-md-4">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="in_progress" <?php echo $status == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                <option value="resolved" <?php echo $status == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                <option value="rejected" <?php echo $status == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
        </div>
        <div class="col-md-4">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <option value="maintenance" <?php echo $category == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                <option value="noise" <?php echo $category == 'noise' ? 'selected' : ''; ?>>Noise</option>
                <option value="cleanliness" <?php echo $category == 'cleanliness' ? 'selected' : ''; ?>>Cleanliness</option>
                <option value="security" <?php echo $category == 'security' ? 'selected' : ''; ?>>Security</option>
                <option value="parking" <?php echo $category == 'parking' ? 'selected' : ''; ?>>Parking</option>
                <option value="other" <?php echo $category == 'other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
        </div>
    </form>
</div>

<!-- Complaints Table -->
<div class="table-container">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Resident</th>
                    <th>Flat</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($complaints->num_rows > 0): ?>
                    <?php while($row = $complaints->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo $row['apartment_number']; ?></td>
                        <td><?php echo htmlspecialchars(substr($row['title'], 0, 30)) . '...'; ?></td>
                        <td><?php echo ucfirst($row['category']); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $row['priority'] == 'urgent' ? 'danger' : 
                                    ($row['priority'] == 'high' ? 'warning' : 
                                    ($row['priority'] == 'medium' ? 'info' : 'secondary')); 
                            ?>">
                                <?php echo ucfirst($row['priority']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $row['status'] == 'resolved' ? 'success' : 
                                    ($row['status'] == 'pending' ? 'warning' : 
                                    ($row['status'] == 'in_progress' ? 'info' : 'danger')); 
                            ?>">
                                <?php echo str_replace('_', ' ', ucfirst($row['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="view_complaint.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="delete_complaint.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center py-4">No complaints found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>