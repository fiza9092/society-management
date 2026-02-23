<?php
require_once '../includes/config.php';
require_once '../includes/resident_auth.php';

$page_title = 'New Complaint';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $category = sanitize($_POST['category']);
    $priority = sanitize($_POST['priority']);
    
    // Generate complaint number
    $complaint_number = 'CMP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $stmt = $conn->prepare("INSERT INTO complaints (complaint_number, resident_id, title, description, category, priority) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sissss', $complaint_number, $_SESSION['user_id'], $title, $description, $category, $priority);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Complaint submitted successfully. Reference #: " . $complaint_number;
        redirect('/resident/complaints.php');
    } else {
        $error = "Error submitting complaint: " . $conn->error;
    }
    $stmt->close();
}

include '../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Submit New Complaint</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" required maxlength="200">
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Category *</label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="noise">Noise Complaint</option>
                                    <option value="cleanliness">Cleanliness</option>
                                    <option value="security">Security</option>
                                    <option value="parking">Parking</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Priority *</label>
                                <select name="priority" class="form-select" required>
                                    <option value="">Select Priority</option>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea name="description" class="form-control" rows="6" required></textarea>
                            <small class="text-muted">Please provide detailed description of your complaint</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Attachments (Optional)</label>
                            <input type="file" name="attachments" class="form-control" multiple accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">You can upload images or documents (Max 5MB each)</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Complaint
                        </button>
                        <a href="complaints.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
            
            <!-- Guidelines -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Guidelines for Submitting Complaints</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Provide clear and detailed description of the issue</li>
                        <li>Include apartment number and location if applicable</li>
                        <li>Upload photos if they help explain the problem</li>
                        <li>Urgent complaints (security, emergency) will be prioritized</li>
                        <li>You can track complaint status in your dashboard</li>
                        <li>Complaints are typically resolved within 3-5 working days</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>