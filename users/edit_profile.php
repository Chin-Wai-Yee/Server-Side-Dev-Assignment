<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'require_login.php';

// Include necessary files
require_once '../database.php';
require_once 'User.php';

// Create user object
$user = new User();
$user->user_id = $_SESSION['user_id'];

// Load user data
$user->read_single();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Recipe Culinary</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="styles.css"/>
</head>
<body>
<?php 
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): 
    include '../admin_header.php'; 
else: 
    include '../header.php'; 
endif; 
?>

    <div class="container my-2 pb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Edit Profile</h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error']; ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="update_profile.php" method="POST">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user->username) ?>" placeholder="Username" required>
                                <label for="username">Username</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user->email) ?>" placeholder="Email" required>
                                <label for="email">Email</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="bio" name="bio" rows="4" placeholder="Bio"><?= htmlspecialchars($user->bio) ?></textarea>
                                <label for="bio">Bio</label>
                                <div class="form-text mt-1">Tell us about yourself, your culinary interests, and cooking style.</div>
                            </div>
                            
                            <h4 class="mt-4">Change Password</h4>
                            <hr>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Current Password">
                                <label for="current_password">Current Password</label>
                                <div class="form-text mt-1">Leave password fields blank if you don't want to change your password.</div>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New Password">
                                <label for="new_password">New Password</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm New Password">
                                <label for="confirm_password">Confirm New Password</label>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="profile.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Profile
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once '../footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>