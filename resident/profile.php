<?php
require_once '../includes/config.php';
require_once '../includes/resident_auth.php';

$page_title = 'My Profile';
$resident_id = $_SESSION['user_id'];

// Get resident details
$resident = $conn->query("SELECT * FROM residents WHERE id = $resident_id")->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $alternate_phone = sanitize($_POST['alternate_phone']);
        $address = sanitize($_POST['address']);
        $emergency_contact_name = sanitize($_POST['emergency_contact_name']);
        $emergency_contact_phone = sanitize($_POST['emergency_contact_phone']);
        
        $update = $conn->prepare("UPDATE residents SET email = ?, phone = ?, alternate_phone = ?, address = ?, emergency_contact_name = ?, emergency_contact_phone = ? WHERE id = ?");
        $update->bind_param('ssssssi', $email, $phone, $alternate_phone, $address, $emergency_contact_name, $emergency_contact_phone, $resident_id);
        
        if ($update->execute()) {
            $_SESSION['success'] = "Profile updated successfully";
            redirect('/resident/profile.php');
        } else {
            $error = "Error updating profile";
        }
        $update->close();
    }
    
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        
        if (!password_verify($current, $resident['password'])) {
            $password_error = "Current password is incorrect";
        } elseif (strlen($new) < 8) {
            $password_error = "Password must be at least 8 characters";
        } elseif ($new !== $confirm) {
            $password_error = "New passwords do not match";
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE residents SET password = ? WHERE id = ?");
            $update->bind_param('si', $hashed, $resident_id);
            
            if ($update->execute()) {
                $_SESSION['success'] = "Password changed successfully";
                redirect('/resident/profile.php');
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
        <!-- Profile Card -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if($resident['profile_pic']): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/<?php echo $resident['profile_pic']; ?>" 
                                 class="rounded-circle img-fluid" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                 style="width: 150px; height: 150px; font-size: 48px;">
                                <?php echo strtoupper(substr($resident['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <h4><?php echo htmlspecialchars($resident['full_name']); ?></h4>
                    <p class="text-muted">Flat: <?php echo $resident['apartment_number']; ?></p>
                    
                    <hr>
                    
                    <div class="text-start">
                        <p><i class="fas fa-user me-2"></i> Username: <?php echo $resident['username']; ?></p>
                        <p><i class="fas fa-envelope me-2"></i> Email: <?php echo $resident['email']; ?></p>
                        <p><i class="fas fa-phone me-2"></i> Phone: <?php echo $resident['phone'] ?: 'Not provided'; ?></p>
                        <p><i class="fas fa-calendar me-2"></i> Member Since: <?php echo date('d M Y', strtotime($resident['created_at'])); ?></p>
                        <p><i class="fas fa-home me-2"></i> Status: 
                            <span class="badge bg-<?php echo $resident['status'] == 'active' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($resident['status']); ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Edit Profile Form -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Edit Profile Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($resident['full_name']); ?>" disabled>
                                <small class="text-muted">Contact admin to change name</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Apartment Number</label>
                                <input type="text" class="form-control" value="<?php echo $resident['apartment_number']; ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($resident['email']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($resident['phone']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Alternate Phone</label>
                                <input type="tel" name="alternate_phone" class="form-control" value="<?php echo htmlspecialchars($resident['alternate_phone']); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($resident['address']); ?></textarea>
                            </div>
                        </div>
                        
                        <h6 class="mt-3 mb-3">Emergency Contact</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Emergency Contact Name</label>
                                <input type="text" name="emergency_contact_name" class="form-control" value="<?php echo htmlspecialchars($resident['emergency_contact_name']); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Emergency Contact Phone</label>
                                <input type="tel" name="emergency_contact_phone" class="form-control" value="<?php echo htmlspecialchars($resident['emergency_contact_phone']); ?>">
                            </div>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Change Password -->
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
                            <small class="text-muted">Minimum 8 characters with at least one uppercase, one lowercase and one number</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required 
                                   id="confirm_password">
                        </div>
                        
                        <div class="password-strength mb-3">
                            <div class="progress" style="height: 5px;">
                                <div id="strength-bar" class="progress-bar" style="width: 0%;"></div>
                            </div>
                            <small id="strength-text" class="text-muted"></small>
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
// Password strength checker
document.getElementById('new_password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');
    
    let strength = 0;
    
    if (password.length >= 8) strength += 25;
    if (password.match(/[A-Z]/)) strength += 25;
    if (password.match(/[a-z]/)) strength += 25;
    if (password.match(/[0-9]/)) strength += 25;
    
    strengthBar.style.width = strength + '%';
    
    if (strength <= 25) {
        strengthBar.className = 'progress-bar bg-danger';
        strengthText.textContent = 'Weak';
    } else if (strength <= 50) {
        strengthBar.className = 'progress-bar bg-warning';
        strengthText.textContent = 'Fair';
    } else if (strength <= 75) {
        strengthBar.className = 'progress-bar bg-info';
        strengthText.textContent = 'Good';
    } else {
        strengthBar.className = 'progress-bar bg-success';
        strengthText.textContent = 'Strong';
    }
});

// Confirm password match
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