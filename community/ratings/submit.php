<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token($_POST['csrf_token']);
    
    $recipe_id = (int)$_POST['recipe_id'];
    $rating = (int)$_POST['rating'];
    $user_id = (int)$_SESSION['user_id'];

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        die("Invalid rating value");
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO recipe_ratings 
                              (user_id, recipe_id, rating)
                              VALUES (?, ?, ?)
                              ON DUPLICATE KEY UPDATE rating = ?");
        $stmt->execute([$user_id, $recipe_id, $rating, $rating]);
        
        // Update recipe average rating
        $stmt = $pdo->prepare("UPDATE recipes SET 
                              average_rating = (
                                  SELECT AVG(rating) 
                                  FROM recipe_ratings 
                                  WHERE recipe_id = ?
                              )
                              WHERE recipe_id = ?");
        $stmt->execute([$recipe_id, $recipe_id]);
        
        header("Location: /recipes/view.php?id=$recipe_id");
        exit();
    } catch (PDOException $e) {
        die("Error submitting rating: " . $e->getMessage());
    }
}