<?php
session_start();
require '../database.php';  // DB connection
require '../users/require_login.php';

$feedback = '';
$feedback_class = '';

if (!isset($_GET['recipe_id'])) {
    header("Location: recipe.php");
    exit;
}

$recipe_id = intval($_GET['recipe_id']);
$user_id = $_SESSION['user_id']; // Assuming user_id is stored in the session after login

// Check if the recipe exists and belongs to the logged-in user
$sql = "SELECT * FROM recipes WHERE recipe_id = $recipe_id AND user_id = $user_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) != 1) {
    $feedback = "No recipes found. You haven't created any recipes yet.";
    $feedback_class = "feedback-error";
    $recipe = null; // Set recipe to null to avoid errors in the form
} else {
    $recipe = mysqli_fetch_assoc($result);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $recipe) {
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
        WHERE recipe_id = $recipe_id AND user_id = $user_id";

    if (mysqli_query($conn, $update_query)) {
        $feedback = "Recipe updated successfully!";
        $feedback_class = "feedback-success";
        $result = mysqli_query($conn, "SELECT * FROM recipes WHERE recipe_id = $recipe_id AND user_id = $user_id");
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
    <h2 class="addrecipe-title" style="text-align:center; font-size:48px; font-family: 'Didot', serif; color: lightyellow; position: absolute; top: 120px; left: 50%; transform: translateX(-50%);">Edit Recipe</h2>

    <div class="addrecipe-container">
        <?php if ($feedback != ''): ?>
            <p class="feedback-message <?php echo $feedback_class; ?>"><?php echo $feedback; ?></p>
        <?php endif; ?>

        <?php if ($recipe): ?>
        <form action="edit_recipe.php?recipe_id=<?php echo $recipe_id; ?>" method="POST" enctype="multipart/form-data">
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

            <label for="instructions" class="full-width">Instructions</label>
            <textarea id="instructions" name="instructions" class="full-width" required><?php echo htmlspecialchars($recipe['instructions']); ?></textarea>

            <label for="image">Image Upload (Leave blank to keep current)</label>
            <input type="file" id="image" name="image">

            <button type="submit">Update Recipe</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php include '../footer.php'; ?>
</body>
</html>
