<?php
require_once '../includes/config.php';
require_once '../includes/resident_auth.php';

$page_title = 'Society Notices';

// Get all active notices
$notices = $conn->query("SELECT n.*, a.full_name as posted_by_name 
                        FROM notices n 
                        LEFT JOIN admins a ON n.posted_by = a.id 
                        WHERE (n.expires_at IS NULL OR n.expires_at > NOW()) 
                        ORDER BY n.priority DESC, n.created_at DESC");

include '../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Society Notices</h2>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-4">
            <select class="form-select" id="categoryFilter">
                <option value="all">All Categories</option>
                <option value="general">General</option>
                <option value="maintenance">Maintenance</option>
                <option value="event">Event</option>
                <option value="emergency">Emergency</option>
                <option value="holiday">Holiday</option>
            </select>
        </div>
        <div class="col-md-4">
            <select class="form-select" id="priorityFilter">
                <option value="all">All Priorities</option>
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" id="searchInput" placeholder="Search notices...">
        </div>
    </div>
    
    <!-- Notices List -->
    <div class="row" id="noticesContainer">
        <?php if ($notices->num_rows > 0): ?>
            <?php while($notice = $notices->fetch_assoc()): ?>
                <div class="col-md-6 mb-4 notice-item" 
                     data-category="<?php echo $notice['category']; ?>" 
                     data-priority="<?php echo $notice['priority']; ?>"
                     data-title="<?php echo strtolower($notice['title']); ?>"
                     data-content="<?php echo strtolower($notice['content']); ?>">
                    
                    <div class="notice-card" style="border-left-color: <?php 
                        echo $notice['priority'] == 'urgent' ? '#dc3545' : 
                            ($notice['priority'] == 'high' ? '#fd7e14' : 
                            ($notice['priority'] == 'medium' ? '#0d6efd' : '#6c757d')); 
                    ?>; border-left-width: 8px;">
                        
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></h5>
                            <span class="badge bg-<?php 
                                echo $notice['priority'] == 'urgent' ? 'danger' : 
                                    ($notice['priority'] == 'high' ? 'warning' : 
                                    ($notice['priority'] == 'medium' ? 'info' : 'secondary')); 
                            ?>">
                                <?php echo ucfirst($notice['priority']); ?>
                            </span>
                        </div>
                        
                        <span class="badge bg-secondary mb-3"><?php echo ucfirst($notice['category']); ?></span>
                        
                        <div class="notice-content">
                            <?php echo nl2br(htmlspecialchars($notice['content'])); ?>
                        </div>
                        
                        <div class="notice-meta mt-3">
                            <small class="text-muted">
                                <i class="fas fa-user"></i> Posted by: <?php echo htmlspecialchars($notice['posted_by_name'] ?: 'Admin'); ?><br>
                                <i class="fas fa-calendar"></i> Posted on: <?php echo date('d M Y h:i A', strtotime($notice['created_at'])); ?>
                            </small>
                        </div>
                        
                        <?php if($notice['expires_at']): ?>
                            <div class="mt-2 text-muted small">
                                <i class="fas fa-clock"></i> Valid until: <?php echo date('d M Y', strtotime($notice['expires_at'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No notices available at the moment.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Filter notices
document.getElementById('categoryFilter').addEventListener('change', filterNotices);
document.getElementById('priorityFilter').addEventListener('change', filterNotices);
document.getElementById('searchInput').addEventListener('keyup', filterNotices);

function filterNotices() {
    var category = document.getElementById('categoryFilter').value;
    var priority = document.getElementById('priorityFilter').value;
    var search = document.getElementById('searchInput').value.toLowerCase();
    
    var notices = document.getElementsByClassName('notice-item');
    
    for (var i = 0; i < notices.length; i++) {
        var notice = notices[i];
        var noticeCategory = notice.getAttribute('data-category');
        var noticePriority = notice.getAttribute('data-priority');
        var noticeTitle = notice.getAttribute('data-title');
        var noticeContent = notice.getAttribute('data-content');
        
        var categoryMatch = (category === 'all' || noticeCategory === category);
        var priorityMatch = (priority === 'all' || noticePriority === priority);
        var searchMatch = (search === '' || noticeTitle.includes(search) || noticeContent.includes(search));
        
        if (categoryMatch && priorityMatch && searchMatch) {
            notice.style.display = 'block';
        } else {
            notice.style.display = 'none';
        }
    }
}
</script>

<?php include '../includes/footer.php'; ?>