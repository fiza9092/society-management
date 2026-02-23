<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

$page_title = 'View Complaint';
$id = (int)$_GET['id'];

// Get complaint details
$stmt = $conn->prepare("SELECT c.*, r.full_name, r.apartment_number, r.phone, r.email 
                       FROM complaints c 
                       JOIN residents r ON c.resident_id = r.id 
                       WHERE c.id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Complaint not found";
    redirect('/admin/complaints.php');
}

$complaint = $result->fetch_assoc();
$stmt->close();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = sanitize($_POST['status']);
    $admin_comments = sanitize($_POST['admin_comments']);
    
    $update = $conn->prepare("UPDATE complaints SET status = ?, admin_comments = ?, resolved_by = ?, resolved_date = NOW() WHERE id = ?");
    $update->bind_param('ssii', $status, $admin_comments, $_SESSION['user_id'], $id);
    
    if ($update->execute()) {
        $_SESSION['success'] = "Complaint updated successfully";
        $complaint['status'] = $status;
        $complaint['admin_comments'] = $admin_comments;
    }
    $update->close();
}

include '../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Complaint #<?php echo $complaint['id']; ?></h2>
        <a href="complaints.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Complaints
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Complaint Details</h5>
                </div>
                <div class="card-body">
                    <h4><?php echo htmlspecialchars($complaint['title']); ?></h4>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p><strong>Category:</strong> <?php echo ucfirst($complaint['category']); ?></p>
                            <p><strong>Priority:</strong> 
                                <span class="badge bg-<?php 
                                    echo $complaint['priority'] == 'urgent' ? 'danger' : 
                                        ($complaint['priority'] == 'high' ? 'warning' : 
                                        ($complaint['priority'] == 'medium' ? 'info' : 'secondary')); 
                                ?>">
                                    <?php echo ucfirst($complaint['priority']); ?>
                                </span>
                            </p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?php 
                                    echo $complaint['status'] == 'resolved' ? 'success' : 
                                        ($complaint['status'] == 'pending' ? 'warning' : 
                                        ($complaint['status'] == 'in_progress' ? 'info' : 'danger')); 
                                ?>">
                                    <?php echo str_replace('_', ' ', ucfirst($complaint['status'])); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Submitted on:</strong> <?php echo date('d M Y h:i A', strtotime($complaint['created_at'])); ?></p>
                            <p><strong>Last Updated:</strong> <?php echo date('d M Y h:i A', strtotime($complaint['updated_at'])); ?></p>
                            <?php if($complaint['resolved_date']): ?>
                                <p><strong>Resolved on:</strong> <?php echo date('d M Y h:i A', strtotime($complaint['resolved_date'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <h6>Description:</h6>
                        <div class="p-3 bg-light rounded">
                            <?php echo nl2br(htmlspecialchars($complaint['description'])); ?>
                        </div>
                    </div>
                    
                    <?php if($complaint['admin_comments']): ?>
                        <div class="mt-3">
                            <h6>Admin Comments:</h6>
                            <div class="p-3 bg-info text-white rounded">
                                <?php echo nl2br(htmlspecialchars($complaint['admin_comments'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Update Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Update Complaint</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" <?php echo $complaint['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="in_progress" <?php echo $complaint['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="resolved" <?php echo $complaint['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="rejected" <?php echo $complaint['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                <option value="closed" <?php echo $complaint['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Admin Comments</label>
                            <textarea name="admin_comments" class="form-control" rows="4"><?php echo htmlspecialchars($complaint['admin_comments']); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Complaint
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Resident Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Resident Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($complaint['full_name']); ?></p>
                    <p><strong>Flat:</strong> <?php echo $complaint['apartment_number']; ?></p>
                    <p><strong>Phone:</strong> <?php echo $complaint['phone'] ?: 'N/A'; ?></p>
                    <p><strong>Email:</strong> <?php echo $complaint['email'] ?: 'N/A'; ?></p>
                    
                    <hr>
                    
                    <a href="tel:<?php echo $complaint['phone']; ?>" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-phone"></i> Call Resident
                    </a>
                    <a href="mailto:<?php echo $complaint['email']; ?>" class="btn btn-info w-100">
                        <i class="fas fa-envelope"></i> Send Email
                    </a>
                </div>
            </div>
            
            <!-- Activity Log -->
            <?php
            $logs = $conn->query("SELECT * FROM activity_logs 
                                  WHERE description LIKE '%complaint #$id%' 
                                  ORDER BY created_at DESC LIMIT 5");
            if ($logs->num_rows > 0):
            ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <?php while($log = $logs->fetch_assoc()): ?>
                        <div class="mb-2">
                            <small class="text-muted"><?php echo date('d M H:i', strtotime($log['created_at'])); ?></small>
                            <p class="mb-0"><?php echo $log['description']; ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>