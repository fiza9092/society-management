<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

$page_title = 'Manage Notices';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM notices WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Notice deleted successfully";
    }
    $stmt->close();
    redirect('/admin/notices.php');
}

// Get all notices
$notices = $conn->query("SELECT n.*, a.full_name as posted_by_name 
                         FROM notices n 
                         LEFT JOIN admins a ON n.posted_by = a.id 
                         ORDER BY n.priority DESC, n.created_at DESC");

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Notices</h2>
    <a href="add_notice.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Notice
    </a>
</div>

<div class="row">
    <?php if ($notices->num_rows > 0): ?>
        <?php while($notice = $notices->fetch_assoc()): ?>
            <div class="col-md-6 mb-4">
                <div class="notice-card" style="border-left-color: <?php 
                    echo $notice['priority'] == 'urgent' ? '#dc3545' : 
                        ($notice['priority'] == 'high' ? '#fd7e14' : 
                        ($notice['priority'] == 'medium' ? '#0d6efd' : '#6c757d')); 
                ?>;">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></h5>
                        <span class="badge bg-<?php 
                            echo $notice['priority'] == 'urgent' ? 'danger' : 
                                ($notice['priority'] == 'high' ? 'warning' : 
                                ($notice['priority'] == 'medium' ? 'info' : 'secondary')); 
                        ?>">
                            <?php echo ucfirst($notice['priority']); ?>
                        </span>
                    </div>
                    
                    <span class="badge bg-secondary mb-2"><?php echo ucfirst($notice['category']); ?></span>
                    
                    <p class="mt-2"><?php echo nl2br(htmlspecialchars(substr($notice['content'], 0, 150))); ?>...</p>
                    
                    <div class="notice-meta d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($notice['posted_by_name'] ?: 'Admin'); ?><br>
                            <i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($notice['created_at'])); ?>
                        </div>
                        <div>
                            <a href="edit_notice.php?id=<?php echo $notice['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?php echo $notice['id']; ?>" class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    
                    <?php if($notice['expires_at']): ?>
                        <div class="mt-2 text-muted small">
                            <i class="fas fa-clock"></i> Expires: <?php echo date('d M Y', strtotime($notice['expires_at'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">No notices found</div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>