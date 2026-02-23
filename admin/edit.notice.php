<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

$page_title = 'Edit Notice';
$id = (int)$_GET['id'];

// Get notice details
$stmt = $conn->prepare("SELECT * FROM notices WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Notice not found";
    redirect('/admin/notices.php');
}

$notice = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $content = sanitize($_POST['content']);
    $category = sanitize($_POST['category']);
    $priority = sanitize($_POST['priority']);
    $expires_at = $_POST['expires_at'] ?: null;
    
    $update = $conn->prepare("UPDATE notices SET title = ?, content = ?, category = ?, priority = ?, expires_at = ? WHERE id = ?");
    $update->bind_param('sssssi', $title, $content, $category, $priority, $expires_at, $id);
    
    if ($update->execute()) {
        $_SESSION['success'] = "Notice updated successfully";
        redirect('/admin/notices.php');
    } else {
        $error = "Error updating notice: " . $conn->error;
    }
    $update->close();
}

include '../includes/header.php';
?>

<div class="container-fluid px-4">
    <h2 class="mb-4">Edit Notice</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($notice['title']); ?>" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-select" required>
                        <option value="general" <?php echo $notice['category'] == 'general' ? 'selected' : ''; ?>>General</option>
                        <option value="maintenance" <?php echo $notice['category'] == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                        <option value="event" <?php echo $notice['category'] == 'event' ? 'selected' : ''; ?>>Event</option>
                        <option value="emergency" <?php echo $notice['category'] == 'emergency' ? 'selected' : ''; ?>>Emergency</option>
                        <option value="holiday" <?php echo $notice['category'] == 'holiday' ? 'selected' : ''; ?>>Holiday</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Priority *</label>
                    <select name="priority" class="form-select" required>
                        <option value="low" <?php echo $notice['priority'] == 'low' ? 'selected' : ''; ?>>Low</option>
                        <option value="medium" <?php echo $notice['priority'] == 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="high" <?php echo $notice['priority'] == 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="urgent" <?php echo $notice['priority'] == 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Expires On (Optional)</label>
                    <input type="date" name="expires_at" class="form-control" 
                           value="<?php echo $notice['expires_at']; ?>" 
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="col-12">
                    <label class="form-label">Content *</label>
                    <textarea name="content" class="form-control" rows="8" required><?php echo htmlspecialchars($notice['content']); ?></textarea>
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Notice
                    </button>
                    <a href="notices.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>