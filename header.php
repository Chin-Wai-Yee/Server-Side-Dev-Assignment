<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set the base path for XAMPP
$base_path = '/Server-Side-Dev-Assignment-main';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Planner</title>
    <link rel="stylesheet" href="<?= $base_path ?>/styles.css">
</head>
<body>
    <header>
        <?php
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
        ?>
        <nav>
            <ul>
                <li> <a href="<?= $base_path ?>/index.php">Home</a> </li>
                <div class="dropdown">
                    <li> <a href="<?= $base_path ?>/recipes">Recipes</a> </li>
                    <div class="dropdown-content">
                        <a href="<?= $base_path ?>/recipes/add_recipe.php">Create Recipe</a>
                        <a href="<?= $base_path ?>/recipes/edit_recipe.php?recipe_id=2">Edit Recipe</a>
                        <a href="<?= $base_path ?>/recipes/delete_recipe.php">Delete Recipe</a>
                        <a href="<?= $base_path ?>/recipes/recipe.php#search-bar">Search Recipe</a>
                    </div>
                </div>
                <li> <a href="<?= $base_path ?>/meal_planner_page.php">Meal Planning</a> </li>
                <li> <a href="<?= $base_path ?>/community/discussions/list.php">Community</a> </li>
                
                <div class="dropdown">
                    <li> <a href="<?= $base_path ?>/cooking_competition">Cooking Competition</a> </li>
                </div>

                <!-- Check if the user is logged in -->
                <div class="dropdown">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li> <a href="<?= $base_path ?>/users"><i class="fas fa-user"></i></a> </li>
                        <div class="dropdown-content">
                            <a href="<?= $base_path ?>/users">Profile</a>
                            <a href="<?= $base_path ?>/users/logout.php">Logout</a>
                        </div>
                    <?php else: ?>
                        <li> <a href="<?= $base_path ?>/users"><i class="fas fa-sign-in-alt"></i> Login</a> </li>
                        <div class="dropdown-content">
                            <a href="<?= $base_path ?>/users">Profile</a>
                            <a href="<?= $base_path ?>/users/login.php">Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            </ul>
        </nav>
    </header>
    <main>