<?php
session_start();
require '../database.php';  // DB connection
require '../users/require_login.php';

$feedback = '';
$feedback_class = '';

// Validate recipe_id
if (!isset($_GET['recipe_id']) || !is_numeric($_GET['recipe_id'])) {
    header("Location: index.php");
    exit;
}

$recipe_id = intval($_GET['recipe_id']);
$user_id = $_SESSION['user_id']; // Assuming user_id is stored in the session after login

// Fetch the recipe details
$check_query = "SELECT * FROM recipes WHERE recipe_id = ? AND user_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $recipe_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $feedback = "You are not authorized to edit this recipe.";
    $feedback_class = "feedback-error";
    $recipe = null;
} else {
    $recipe = $result->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $recipe) {
    $title = $_POST['title'];
    $ingredients = $_POST['ingredients'];
    $instructions = $_POST['instructions'];
    $cuisine_type = $_POST['cuisine_type'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image']['name'];
        $target = "uploads/" . basename($image);

        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $update_image = ", image_path = ?";
    } else {
        $update_image = '';
    }

    // Update competition in the database
    if ($update_image) {
        $update_query = "UPDATE recipes SET 
            title = ?, 
            ingredients = ?, 
            instructions = ?, 
            cuisine_type = ?,
            image_path = ? 
            WHERE recipe_id = ? AND user_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssssi", $title, $ingredients, $instructions, $cuisine_type, $target, $recipe_id, $user_id);
    } else {
        $update_query = "UPDATE recipes SET 
            title = ?, 
            ingredients = ?, 
            instructions = ?, 
            cuisine_type = ? 
            WHERE recipe_id = ? AND user_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssssi", $title, $ingredients, $instructions, $cuisine_type, $recipe_id, $user_id);
    }

    if ($stmt->execute()) {
        $feedback = "Recipe updated successfully!";
        $feedback_class = "feedback-success";
    } else {
        $feedback = "Failed to update the recipe. Error: " . $conn->error;
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
    <h2 class="addrecipe-title" style="text-align:center; font-size:48px; font-family: 'Didot', serif; color: lightyellow; ">Edit Recipe</h2>
    <?php if ($feedback != ''): ?>
            <p class="feedback-message <?php echo $feedback_class; ?>"><?php echo $feedback; ?></p>
        <?php endif; ?>
    <div class="addrecipe-container">
        

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
