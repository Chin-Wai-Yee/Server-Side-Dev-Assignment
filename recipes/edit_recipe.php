<?php
require '../database.php';

$feedback = '';
$feedback_class = '';

if (!isset($_GET['recipe_id'])) {
    header("Location: recipe.php");
    exit;
}

$recipe_id = intval($_GET['recipe_id']);

$sql = "SELECT * FROM recipes WHERE recipe_id = $recipe_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) != 1) {
    echo "Recipe not found.";
    exit;
}

$recipe = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $ingredients = $_POST['ingredients'];
    $instructions = $_POST['instructions'];
    $cuisine_type = $_POST['cuisine_type'];

    $image = $_FILES['image']['name'];
    $target = "uploads/" . basename($image);

    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    if (!empty($image)) {
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $update_image = ", image_path = '$target'";
    } else {
        $update_image = '';
    }

    $update_query = "UPDATE recipes SET 
        title = '$title', 
        ingredients = '$ingredients', 
        instructions = '$instructions', 
        cuisine_type = '$cuisine_type' 
        $update_image 
        WHERE recipe_id = $recipe_id";

    if (mysqli_query($conn, $update_query)) {
        $feedback = "Recipe updated successfully!";
        $feedback_class = "feedback-success";
        $result = mysqli_query($conn, "SELECT * FROM recipes WHERE recipe_id = $recipe_id");
        $recipe = mysqli_fetch_assoc($result);
    } else {
        $feedback = "Failed to update the recipe.";
        $feedback_class = "feedback-error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Recipe</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
</head>
<body>
    <?php include '../header.php'; ?>

    <div class="addrecipe-header">
        <h2 class="addrecipe-title">Edit Recipe</h2>
        <?php if ($feedback != ''): ?>
            <p class="feedback-message <?php echo $feedback_class; ?>"><?php echo $feedback; ?></p>
        <?php endif; ?>

        <div class="addrecipe-container">
            <form action="edit_recipe.php?recipe_id=<?php echo $recipe_id; ?>" method="POST" enctype="multipart/form-data">
            <label for="recipe_id" style="font-size: 18px;">Choose the recipe you want to edit:</label>
            <select name="recipe_id" id="recipe_id" style="width: 100%; padding: 10px; border-radius: 6px; margin-bottom: 20px;" onchange="location.href='edit_recipe.php?recipe_id=' + this.value;">
            <option value="">-- Select Recipe --</option>
            <?php
                $allRecipes = mysqli_query($conn, "SELECT recipe_id, title FROM recipes ORDER BY title ASC");
                while ($row = mysqli_fetch_assoc($allRecipes)) {
                    $selected = ($row['recipe_id'] == $recipe_id) ? 'selected' : '';
                    echo "<option value=\"" . $row['recipe_id'] . "\" $selected>" . htmlspecialchars($row['title']) . "</option>";
                }
            ?>
            </select>

                <label for="title">Recipe Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($recipe['title']); ?>" required>

                <label for="cuisine_type">Cuisine Type</label>
                <select id="cuisine_type" name="cuisine_type" required>
                    <option value="">-- Select --</option>
                    <?php
                    $cuisines = ["Italian", "Indian", "Chinese", "Malay", "Western"];
                    foreach ($cuisines as $type) {
                        $selected = ($recipe['cuisine_type'] == $type) ? 'selected' : '';
                        echo "<option value=\"$type\" $selected>$type</option>";
                    }
                    ?>
                </select>

                <label for="ingredients" class="full-width">Ingredients</label>
                <textarea id="ingredients" name="ingredients" class="full-width" required><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea>
                <h4 class="ingredients-warning">Notice: Don't leave a blank sentence before or after each ingredient. It will display in point form!</h4>

                <label for="instructions" class="full-width">Instructions</label>
                <textarea id="instructions" name="instructions" class="full-width" required><?php echo htmlspecialchars($recipe['instructions']); ?></textarea>

                <label for="image">Image Upload (Leave blank to keep current)</label>
                <input type="file" id="image" name="image">

                <button type="submit">Update Recipe</button>
            </form>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>
</html>
