<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

$page_title = 'Manage Residents';

// Handle search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$query = "SELECT * FROM residents WHERE 1=1";
if ($search) {
    $query .= " AND (full_name LIKE '%$search%' OR apartment_number LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";
}
if ($status) {
    $query .= " AND status = '$status'";
}
$query .= " ORDER BY created_at DESC";

$residents = $conn->query($query);

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Residents</h2>
    <a href="add_user.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Resident
    </a>
</div>

<!-- Search and Filter -->
<div class="table-container mb-4">
    <form method="GET" class="row g-3">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" name="search" placeholder="Search by name, flat, email, phone..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
        </div>
        <div class="col-md-4">
            <select class="form-select" name="status">
                <option value="">All Status</option>
                <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                <option value="blocked" <?php echo $status == 'blocked' ? 'selected' : ''; ?>>Blocked</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>
</div>

<!-- Residents Table -->
<div class="table-container">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Resident</th>
                    <th>Flat No.</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($residents->num_rows > 0): ?>
                    <?php while($row = $residents->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if($row['profile_pic']): ?>
                                    <img src="<?php echo SITE_URL; ?>/uploads/<?php echo $row['profile_pic']; ?>" 
                                         class="rounded-circle me-2" width="40" height="40" alt="Profile">
                                <?php else: ?>
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" 
                                         style="width:40px; height:40px;">
                                        <?php echo strtoupper(substr($row['full_name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <strong><?php echo htmlspecialchars($row['full_name']); ?></strong><br>
                                    <small class="text-muted">@<?php echo $row['username']; ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo $row['apartment_number']; ?></td>
                        <td>
                            <i class="fas fa-envelope"></i> <?php echo $row['email']; ?><br>
                            <i class="fas fa-phone"></i> <?php echo $row['phone'] ?: 'N/A'; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $row['status'] == 'active' ? 'success' : 
                                    ($row['status'] == 'inactive' ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="view_user.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="delete_user.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this resident?')"
                               title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">No residents found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>