<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';

$page_title = 'My Profile';
$user_id = $_SESSION['user_id'];

// Get user details
$stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        
        $update = $conn->prepare("UPDATE admins SET full_name = ?, email = ? WHERE id = ?");
        $update->bind_param('ssi', $full_name, $email, $user_id);
        
        if ($update->execute()) {
            $_SESSION['success'] = "Profile updated successfully";
            $_SESSION['user_name'] = $full_name;
            redirect('/admin/profile.php');
        } else {
            $error = "Error updating profile";
        }
        $update->close();
    }
    
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        
        // Verify current password
        if (!password_verify($current, $user['password'])) {
            $password_error = "Current password is incorrect";
        } elseif (strlen($new) < 8) {
            $password_error = "Password must be at least 8 characters";
        } elseif ($new !== $confirm) {
            $password_error = "New passwords do not match";
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $update->bind_param('si', $hashed, $user_id);
            
            if ($update->execute()) {
                $_SESSION['success'] = "Password changed successfully";
                redirect('/admin/profile.php');
            } else {
                $password_error = "Error changing password";
            }
            $update->close();
        }
    }
}

include '../includes/header.php';
?>

<div class="container-fluid px-4">
    <h2 class="mb-4">My Profile</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if($user['profile_pic']): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/<?php echo $user['profile_pic']; ?>" 
                                 class="rounded-circle img-fluid" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                 style="width: 150px; height: 150px; font-size: 48px;">
                                <?php echo strtoupper(substr($user['full_name'] ?: $user['username'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <h4><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></h4>
                    <p class="text-muted"><?php echo ucfirst($user['role']); ?></p>
                    
                    <hr>
                    
                    <div class="text-start">
                        <p><i class="fas fa-user me-2"></i> Username: <?php echo $user['username']; ?></p>
                        <p><i class="fas fa-envelope me-2"></i> Email: <?php echo $user['email']; ?></p>
                        <p><i class="fas fa-calendar me-2"></i> Member Since: <?php echo date('d M Y', strtotime($user['created_at'])); ?></p>
                        <p><i class="fas fa-clock me-2"></i> Last Login: <?php echo $user['last_login'] ? date('d M Y H:i', strtotime($user['last_login'])) : 'Never'; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Edit Profile</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($password_error)): ?>
                        <div class="alert alert-danger"><?php echo $password_error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required 
                                   minlength="8" id="new_password">
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required 
                                   id="confirm_password">
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-warning" id="password_btn">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('confirm_password').addEventListener('keyup', function() {
    var new_pass = document.getElementById('new_password').value;
    var confirm_pass = this.value;
    var btn = document.getElementById('password_btn');
    
    if (new_pass === confirm_pass && new_pass.length >= 8) {
        btn.disabled = false;
        this.style.borderColor = '#28a745';
    } else {
        btn.disabled = true;
        this.style.borderColor = '#dc3545';
    }
});
</script>

<?php include '../includes/footer.php'; ?>