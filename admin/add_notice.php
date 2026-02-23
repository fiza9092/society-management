<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

$page_title = 'Add Notice';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $content = sanitize($_POST['content']);
    $category = sanitize($_POST['category']);
    $priority = sanitize($_POST['priority']);
    $expires_at = $_POST['expires_at'] ?: null;
    
    $stmt = $conn->prepare("INSERT INTO notices (title, content, category, priority, expires_at, posted_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssi', $title, $content, $category, $priority, $expires_at, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Notice added successfully";
        redirect('/admin/notices.php');
    } else {
        $error = "Error adding notice: " . $conn->error;
    }
    $stmt->close();
}

include '../includes/header.php';
?>

<div class="container-fluid px-4">
    <h2 class="mb-4">Add New Notice</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-select" required>
                        <option value="general">General</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="event">Event</option>
                        <option value="emergency">Emergency</option>
                        <option value="holiday">Holiday</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Priority *</label>
                    <select name="priority" class="form-select" required>
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Expires On (Optional)</label>
                    <input type="date" name="expires_at" class="form-control" min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="col-12">
                    <label class="form-label">Content *</label>
                    <textarea name="content" class="form-control" rows="8" required></textarea>
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Publish Notice
                    </button>
                    <a href="notices.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>