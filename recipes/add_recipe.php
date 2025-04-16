<?php
session_start();
require '../database.php';  // DB connection
require '../users/require_login.php';

$feedback = '';
$feedback_class = '';

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $feedback = "Recipe added successfully!";
        $feedback_class = "feedback-success";
    } elseif ($_GET['status'] === 'error') {
        $feedback = "Failed to add the recipe.";
        $feedback_class = "feedback-error";
    }
}

$user_id = $_SESSION['user_id'];

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

    $query = "INSERT INTO recipes (user_id, title, ingredients, instructions, cuisine_type, image_path) 
              VALUES ('$user_id', '$title', '$ingredients', '$instructions', '$cuisine_type', '$target')";
    $result = mysqli_query($conn, $query);

    if ($result && move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        header("Location: add_recipe.php?status=success");
        exit();
    } else {
        header("Location: add_recipe.php?status=error");
        exit();
    }
}



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Recipe</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
</head>
<body>
    <?php include '../header.php'; ?>

    <div class="addrecipe-header">
        <!-- Move title outside the container -->
        <h2 class="addrecipe-title">Add Recipe</h2>
        <?php if ($feedback != ''): ?>
                    <p class="feedback-message <?php echo $feedback_class; ?>"><?php echo $feedback; ?></p>
                <?php endif; ?>

        <div class="addrecipe-container">
            <form action="add_recipe.php" method="POST" enctype="multipart/form-data">
                <label for="title">Recipe Title</label>
                <input type="text" id="title" name="title" required>

                <label for="cuisine_type">Cuisine Type</label>
                <select id="cuisine_type" name="cuisine_type" required>
                    <option value="">-- Select --</option>
                    <option value="Italian">Italian</option>
                    <option value="Indian">Indian</option>
                    <option value="Chinese">Chinese</option>
                    <option value="Malay">Malay</option>
                    <option value="Western">Western</option>
                </select>

                <label for="ingredients" class="full-width">Ingredients</label>
                <textarea id="ingredients" name="ingredients" class="full-width" required></textarea>
                <h4 class="ingredients-warning">Notice: Don't leave a blank sentence before or after each ingredient. It will display in point form!</h4>


                <label for="instructions" class="full-width">Instructions</label>
                <textarea id="instructions" name="instructions" class="full-width" required></textarea>

                <label for="image">Image Upload</label>
                <input type="file" id="image" name="image" required>

                <button type="submit" onclick="submit">Add Recipe</button>

            </form>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>
</html>

