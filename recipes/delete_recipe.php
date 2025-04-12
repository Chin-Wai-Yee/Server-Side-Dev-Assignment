<?php
require '../database.php';

$feedback = '';
$feedback_class = '';

// Handle deletion if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipe_id'])) {
    $recipe_id = intval($_POST['recipe_id']);
    $query = "DELETE FROM recipes WHERE recipe_id = $recipe_id";

    if (mysqli_query($conn, $query)) {
        $feedback = "Recipe deleted successfully!";
        $feedback_class = "feedback-success";
    } else {
        $feedback = "Failed to delete the recipe.";
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

        <div class="addrecipe-container" >
            <form action="delete_recipe.php" method="POST" style="width: 100%;">
                <label for="recipe_id" style="font-size: 25px;">Please select a recipe to delete:</label>
                <select name="recipe_id" id="recipe_id" required style="width: 100%; padding: 10px; border-radius: 6px;">
                    <option value="">-- Select Recipe --</option>
                    <?php
                    $recipes = mysqli_query($conn, "SELECT recipe_id, title FROM recipes ORDER BY title ASC");
                    while ($row = mysqli_fetch_assoc($recipes)) {
                        echo '<option value="' . $row['recipe_id'] . '">' . htmlspecialchars($row['title']) . '</option>';
                    }
                    ?>
                </select>
                <br><br>
                <button type="submit" style="background-color: #8E2C2B; color: lightyellow;">Delete Recipe</button>
            </form>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>
</html>
