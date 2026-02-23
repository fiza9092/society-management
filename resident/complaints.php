<?php
require_once '../includes/config.php';
require_once '../includes/resident_auth.php';

$page_title = 'My Complaints';
$resident_id = $_SESSION['user_id'];

// Handle new complaint submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_complaint'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $category = sanitize($_POST['category']);
    $priority = sanitize($_POST['priority']);
    
    // Generate complaint number
    $complaint_number = 'CMP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $stmt = $conn->prepare("INSERT INTO complaints (complaint_number, resident_id, title, description, category, priority) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sissss', $complaint_number, $resident_id, $title, $description, $category, $priority);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Complaint submitted successfully. Reference #: " . $complaint_number;
        redirect('/resident/complaints.php');
    } else {
        $error = "Error submitting complaint: " . $conn->error;
    }
    $stmt->close();
}

// Get all complaints
$complaints = $conn->query("SELECT * FROM complaints WHERE resident_id = $resident_id ORDER BY created_at DESC");

include '../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Complaints</h2>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newComplaintModal">
                <i class="fas fa-plus"></i> New Complaint
            </button>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Complaints List -->
    <div class="row">
        <?php if ($complaints->num_rows > 0): ?>
            <?php while($complaint = $complaints->fetch_assoc()): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <strong>#<?php echo $complaint['complaint_number']; ?></strong>
                                <span class="badge bg-<?php 
                                    echo $complaint['priority'] == 'urgent' ? 'danger' : 
                                        ($complaint['priority'] == 'high' ? 'warning' : 
                                        ($complaint['priority'] == 'medium' ? 'info' : 'secondary')); 
                                ?> ms-2">
                                    <?php echo ucfirst($complaint['priority']); ?>
                                </span>
                            </div>
                            <span class="badge bg-<?php 
                                echo $complaint['status'] == 'resolved' ? 'success' : 
                                    ($complaint['status'] == 'pending' ? 'warning' : 
                                    ($complaint['status'] == 'in_progress' ? 'info' : 'danger')); 
                            ?>">
                                <?php echo str_replace('_', ' ', ucfirst($complaint['status'])); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($complaint['title']); ?></h6>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($complaint['description'], 0, 150))); ?>...</p>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="fas fa-tag"></i> <?php echo ucfirst($complaint['category']); ?>
                                    </small>
                                </div>
                                <div class="col-md-6 text-end">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($complaint['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                            
                            <?php if($complaint['admin_comments']): ?>
                                <div class="mt-3 p-2 bg-light rounded">
                                    <small><strong>Admin Response:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($complaint['admin_comments'])); ?></small>
                                </div>
                            <?php endif; ?>
                            
                            <?php if($complaint['status'] == 'resolved'): ?>
                                <div class="mt-2 text-success">
                                    <small><i class="fas fa-check-circle"></i> Resolved on <?php echo date('d M Y', strtotime($complaint['resolved_date'])); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No complaints found. Click "New Complaint" to submit one.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- New Complaint Modal -->
<div class="modal fade" id="newComplaintModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit New Complaint</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Category *</label>
                            <select name="category" class="form-select" required>
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
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit_complaint" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Complaint
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>