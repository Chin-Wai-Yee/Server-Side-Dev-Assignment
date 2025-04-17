<?php
session_start();
require '../database.php';  // DB connection
require '../users/require_login.php';

$feedback = '';
$feedback_class = '';

// Validate recipe ID
if (!isset($_GET['recipe_id']) || !is_numeric($_GET['recipe_id'])) {
    header("Location: recipe.php");
    exit;
}

$recipe_id = intval($_GET['recipe_id']);
$user_id = $_SESSION['user_id']; // User ID from session
$user_role = $_SESSION['role']; // User role from session

// Allow admin to edit any recipe, else only the recipe owner can edit
$sql = "SELECT * FROM recipes WHERE recipe_id = ? AND (user_id = ? OR ? = 'admin')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $recipe_id, $user_id, $user_role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Recipe not found or you do not have permission to edit this recipe.";
    exit;
}

$recipe = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = htmlspecialchars($_POST['title']);
    $ingredients = htmlspecialchars($_POST['ingredients']);
    $instructions = htmlspecialchars($_POST['instructions']);
    $cuisine_type = htmlspecialchars($_POST['cuisine_type']);

    $image = $_FILES['image']['name'];
    $target = "uploads/" . basename($image);

    // Ensure the uploads directory exists
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // Handle image upload
    if (!empty($image)) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $update_image = ", image_path = ?";
        } else {
            $feedback = "Failed to upload the image.";
            $feedback_class = "feedback-error";
            $update_image = '';
        }
    } else {
        $update_image = '';
    }

    // Update query
    $update_query = "UPDATE recipes SET 
        title = ?, 
        ingredients = ?, 
        instructions = ?, 
        cuisine_type = ? 
        $update_image 
        WHERE recipe_id = ?";
    $stmt = $conn->prepare($update_query);

    if (!empty($image)) {
        $stmt->bind_param("ssssi", $title, $ingredients, $instructions, $cuisine_type, $target, $recipe_id);
    } else {
        $stmt->bind_param("ssssi", $title, $ingredients, $instructions, $cuisine_type, $recipe_id);
    }

    if ($stmt->execute()) {
        $feedback = "Recipe updated successfully!";
        $feedback_class = "feedback-success";

        // Refresh the recipe data
        $stmt = $conn->prepare("SELECT * FROM recipes WHERE recipe_id = ?");
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $recipe = $stmt->get_result()->fetch_assoc();
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<?php 
    include '../admin_header.php'; 
?>

<div class="addrecipe-header">
    <h2 class="addrecipe-title">Edit Recipe</h2>
    <?php if ($feedback != ''): ?>
        <p class="feedback-message <?php echo $feedback_class; ?>"><?php echo $feedback; ?></p>
    <?php endif; ?>

    <div class="addrecipe-container">
        <form action="edit_recipes.php?recipe_id=<?php echo $recipe_id; ?>" method="POST" enctype="multipart/form-data">
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
    </div>
</div>

<?php include '../footer.php'; ?>
</body>
</html>
