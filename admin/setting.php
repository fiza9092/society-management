<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

$page_title = 'System Settings';

// Only super admin can access settings
if ($_SESSION['user_role'] !== 'super_admin') {
    $_SESSION['error'] = "Access denied. Super admin only.";
    redirect('/admin/dashboard.php');
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_general'])) {
        $site_name = sanitize($_POST['site_name']);
        $admin_email = sanitize($_POST['admin_email']);
        $maintenance_fee = floatval($_POST['maintenance_fee']);
        $late_fee_percent = floatval($_POST['late_fee_percent']);
        
        // Update settings file or database
        $settings = [
            'site_name' => $site_name,
            'admin_email' => $admin_email,
            'maintenance_fee' => $maintenance_fee,
            'late_fee_percent' => $late_fee_percent
        ];
        
        file_put_contents('../includes/settings.json', json_encode($settings));
        $_SESSION['success'] = "Settings updated successfully";
        redirect('/admin/settings.php');
    }
    
    if (isset($_POST['backup_database'])) {
        // Create database backup
        $backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $command = "mysqldump --user=" . DB_USER . " --password=" . DB_PASS . " --host=" . DB_HOST . " " . DB_NAME . " > ../backups/" . $backup_file;
        system($command);
        
        $_SESSION['success'] = "Database backup created: " . $backup_file;
        redirect('/admin/settings.php');
    }
}

// Load current settings
$settings = [];
if (file_exists('../includes/settings.json')) {
    $settings = json_decode(file_get_contents('../includes/settings.json'), true);
}

include '../includes/header.php';
?>

<div class="container-fluid px-4">
    <h2 class="mb-4">System Settings</h2>
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="list-group">
                <a href="#general" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                    <i class="fas fa-cog"></i> General Settings
                </a>
                <a href="#fees" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-rupee-sign"></i> Fee Settings
                </a>
                <a href="#email" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-envelope"></i> Email Settings
                </a>
                <a href="#backup" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-database"></i> Backup & Restore
                </a>
                <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-shield-alt"></i> Security
                </a>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="tab-content">
                <!-- General Settings -->
                <div class="tab-pane active" id="general">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">General Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" name="site_name" class="form-control" 
                                           value="<?php echo $settings['site_name'] ?? SITE_NAME; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Admin Email</label>
                                    <input type="email" name="admin_email" class="form-control" 
                                           value="<?php echo $settings['admin_email'] ?? ADMIN_EMAIL; ?>" required>
                                </div>
                                
                                <button type="submit" name="update_general" class="btn btn-primary">
                                    Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Fee Settings -->
                <div class="tab-pane" id="fees">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Fee Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Monthly Maintenance Fee (₹)</label>
                                    <input type="number" step="0.01" name="maintenance_fee" class="form-control" 
                                           value="<?php echo $settings['maintenance_fee'] ?? 2500; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Late Fee (% per month)</label>
                                    <input type="number" step="0.1" name="late_fee_percent" class="form-control" 
                                           value="<?php echo $settings['late_fee_percent'] ?? 2; ?>" required>
                                    <small class="text-muted">Maximum 10% cap applied automatically</small>
                                </div>
                                
                                <button type="submit" name="update_general" class="btn btn-primary">
                                    Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Email Settings -->
                <div class="tab-pane" id="email">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Email Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">SMTP Host</label>
                                    <input type="text" name="smtp_host" class="form-control" 
                                           value="<?php echo SMTP_HOST; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">SMTP Port</label>
                                    <input type="number" name="smtp_port" class="form-control" 
                                           value="<?php echo SMTP_PORT; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">SMTP Username</label>
                                    <input type="email" name="smtp_user" class="form-control" 
                                           value="<?php echo SMTP_USER; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">SMTP Password</label>
                                    <input type="password" name="smtp_pass" class="form-control" 
                                           value="<?php echo SMTP_PASS; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">From Email</label>
                                    <input type="email" name="smtp_from" class="form-control" 
                                           value="<?php echo SMTP_FROM; ?>">
                                </div>
                                
                                <button type="button" class="btn btn-info" onclick="testEmail()">
                                    Test Email Configuration
                                </button>
                                <button type="submit" name="update_general" class="btn btn-primary">
                                    Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Backup Settings -->
                <div class="tab-pane" id="backup">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Backup & Restore</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="mb-4">
                                <button type="submit" name="backup_database" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Create Database Backup
                                </button>
                            </form>
                            
                            <h6>Available Backups</h6>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Backup File</th>
                                            <th>Size</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $backups = glob('../backups/*.sql');
                                        foreach($backups as $backup):
                                            $size = filesize($backup) / 1024; // KB
                                        ?>
                                        <tr>
                                            <td><?php echo basename($backup); ?></td>
                                            <td><?php echo number_format($size, 2); ?> KB</td>
                                            <td><?php echo date('d M Y H:i', filemtime($backup)); ?></td>
                                            <td>
                                                <a href="../backups/<?php echo basename($backup); ?>" class="btn btn-sm btn-success" download>
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger" onclick="restoreBackup('<?php echo basename($backup); ?>')">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Security Settings -->
                <div class="tab-pane" id="security">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Security Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Max Login Attempts</label>
                                    <input type="number" name="max_attempts" class="form-control" 
                                           value="<?php echo MAX_LOGIN_ATTEMPTS; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Session Timeout (minutes)</label>
                                    <input type="number" name="session_timeout" class="form-control" 
                                           value="<?php echo LOGIN_TIMEOUT / 60; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="two_factor" id="two_factor">
                                        <label class="form-check-label" for="two_factor">
                                            Enable Two-Factor Authentication
                                        </label>
                                    </div>
                                </div>
                                
                                <button type="submit" name="update_general" class="btn btn-primary">
                                    Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testEmail() {
    $.ajax({
        url: 'test_email.php',
        method: 'POST',
        data: { test: true },
        success: function(response) {
            alert('Test email sent! Check your inbox.');
        },
        error: function() {
            alert('Failed to send test email. Check your settings.');
        }
    });
}

function restoreBackup(filename) {
    if (confirm('Restoring will overwrite current data. Continue?')) {
        window.location.href = 'restore_backup.php?file=' + filename;
    }
}
</script>

<?php include '../includes/footer.php'; ?>