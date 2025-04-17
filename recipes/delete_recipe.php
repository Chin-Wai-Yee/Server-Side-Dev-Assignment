<?php
session_start();
require '../database.php';  // DB connection
require '../users/require_login.php';

$feedback = '';
$feedback_class = '';

// Handle deletion if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipe_id'])) {
    $recipe_id = intval($_POST['recipe_id']);
    $user_id = $_SESSION['user_id']; // Get the logged-in user's ID

    // Check if the recipe belongs to the logged-in user
    $check_query = "SELECT * FROM recipes WHERE recipe_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $recipe_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Recipe belongs to the user, proceed with deletion
        $delete_query = "DELETE FROM recipes WHERE recipe_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $recipe_id);

        if ($delete_stmt->execute()) {
            $feedback = "Recipe deleted successfully!";
            $feedback_class = "feedback-success";
        } else {
            $feedback = "Failed to delete the recipe.";
            $feedback_class = "feedback-error";
        }
    } else {
        // Recipe does not belong to the user
        $feedback = "You are not authorized to delete this recipe.";
        $feedback_class = "feedback-error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Recipe</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include '../header.php'; ?>

    <div class="addrecipe-header">
        <h2 class="addrecipe-title" style="text-align:center; font-size:48px; font-family: 'Didot', serif; color: lightyellow; position: absolute; top: 120px; left: 50%; transform: translateX(-50%);">Delete Recipe</h2>

        <?php if ($feedback != ''): ?>
            <p class="feedback-message <?php echo $feedback_class; ?>"><?php echo $feedback; ?></p>
        <?php endif; ?>

        <div class="addrecipe-container">
            <form action="delete_recipe.php" method="POST" style="width: 100%;">
                <label for="recipe_id" style="font-size: 25px;">Please select a recipe to delete:</label>
                <?php
                // Fetch recipes created by the logged-in user
                $user_id = $_SESSION['user_id'];
                $recipes = $conn->prepare("SELECT recipe_id, title FROM recipes WHERE user_id = ? ORDER BY title ASC");
                $recipes->bind_param("i", $user_id);
                $recipes->execute();
                $result = $recipes->get_result();

                if ($result->num_rows > 0): ?>
                    <select name="recipe_id" id="recipe_id" required style="width: 100%; padding: 10px; border-radius: 6px;">
                        <option value="">-- Select Recipe --</option>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <option value="<?= $row['recipe_id']; ?>"><?= htmlspecialchars($row['title']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <br><br>
                    <button type="submit" style="background-color: #8E2C2B; color: lightyellow;">Delete Recipe</button>
                <?php else: ?>
                    <p class="feedback-message feedback-error">No recipes found. You haven't created any recipes yet.</p>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>
</html>
