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
    <title>User Profile - Recipe Culinary</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="styles.css"/>
</head>
<body>
    <?php include_once '../header.php'; ?>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h3>Your Profile</h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <?= $_SESSION['success']; ?>
                                <?php unset($_SESSION['success']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error']; ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h4>Personal Information</h4>
                                <hr>
                                <p><strong>Username:</strong> <?= htmlspecialchars($user->username) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($user->email) ?></p>
                                
                                <?php if($user->bio): ?>
                                    <h4 class="mt-4">About Me</h4>
                                    <hr>
                                    <p><?= nl2br(htmlspecialchars($user->bio)) ?></p>
                                <?php endif; ?>
                                
                                <div class="mt-4">
                                    <a href="edit_profile.php" class="btn btn-primary">
                                        <i class="fas fa-user-edit"></i> Edit Profile
                                    </a>
                                </div>

                                <div class="mt-4">
                                    <a href="logout.php" class="btn btn-danger">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h3>My Recipes</h3>
                    </div>
                    <div class="card-body">
                        <p>You haven't shared any recipes yet.</p>
                        <a href="../recipes/add_recipe.php" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add Your First Recipe
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3>Account Stats</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Recipes Shared
                                <span class="badge bg-primary rounded-pill">0</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Comments
                                <span class="badge bg-primary rounded-pill">0</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Favorites
                                <span class="badge bg-primary rounded-pill">0</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once '../footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>