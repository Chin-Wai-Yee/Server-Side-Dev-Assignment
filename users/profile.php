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
$user->user_id = $_SESSION['user_id'];  // Get the user ID from session

// Load user data
$user->read_single();

// Fetch user's recipes
$sql = "SELECT * FROM recipes WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$recipes_result = $stmt->get_result();

// Fetch user's comments count
$sql_comments = "SELECT COUNT(*) as comment_count FROM comments WHERE user_id = ?";
$stmt_comments = $conn->prepare($sql_comments);
$stmt_comments->bind_param("i", $_SESSION['user_id']);
$stmt_comments->execute();
$comments_result = $stmt_comments->get_result();
$comments_count = $comments_result->fetch_assoc()['comment_count'] ?? 0;
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
<?php 
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): 
    include '../admin_header.php'; 
else: 
    include '../header.php'; 
endif; 
?>

    <div class="container d-flex justify-content-center align-items-center" >
        <div class="row w-100">
            <div class="col-md-8 mx-auto">
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
                
                <?php if ($_SESSION['role'] === 'user'): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>My Recipes</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($recipes_result->num_rows > 0): ?>
                                <ul class="list-group">
                                    <?php while ($recipe = $recipes_result->fetch_assoc()): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <a href="../recipes/recipe_detail.php?recipe_id=<?= $recipe['recipe_id']; ?>">
                                                <?= htmlspecialchars($recipe['title']); ?>
                                            </a>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <p>You haven't shared any recipes yet.</p>
                                <a href="../recipes/add_recipe.php" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Add Your First Recipe
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include_once '../footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
