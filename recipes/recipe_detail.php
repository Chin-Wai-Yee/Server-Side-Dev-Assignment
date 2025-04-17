<?php
session_start();
require '../database.php';  // DB connection
require '../users/require_login.php';

$id = intval($_GET['recipe_id']);
$sql = "SELECT * FROM recipes WHERE recipe_id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 1) {
    $recipe = $result->fetch_assoc();
} else {
    echo "Recipe not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($recipe['title']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../styles.css">
<style>
    h3 {
        font-size: 20px;
        margin-top: 20px;
        font-weight: bold;
    }
    </style>
</head>
<body>
<?php include '../header.php'; ?>
    <div class="recipe-detail">
        <h1 style="text-align:center;font-weight:bold;"><?php echo htmlspecialchars($recipe['title']); ?></h1>
        
        <div class="recipe-layout">
            <!-- Image on the left -->
            <?php if (!empty($recipe["image_path"])): ?>
                <div class="recipe-image">
                    <img src="<?php echo htmlspecialchars($recipe['image_path']); ?>" alt="Recipe Image">
                </div>
            <?php endif; ?>

            <!-- Text content on the right -->
            <div class="recipe-content" >
            <?php
            // Check if the logged-in user is the creator of the recipe
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $recipe['user_id']): ?>
                <div class="recipe-actions mb-3">
                    <a href="edit_recipe.php?recipe_id=<?php echo $recipe['recipe_id']; ?>" class="btn btn-primary btn-sm me-2">Edit</a>
                    <a href="delete_recipe.php?recipe_id=<?php echo $recipe['recipe_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this recipe?');">Delete</a>
                </div>
            <?php endif; ?>
                <h3>Cuisine Type:</h3>
                <p><?php echo htmlspecialchars($recipe['cuisine_type']); ?></p>

                <h3>Ingredients:</h3>
                <ul class="ingredient-list">
                    <?php 
                    $ingredients = explode("\n", $recipe['ingredients']);
                    foreach ($ingredients as $item) {
                        echo '<li>' . htmlspecialchars(trim($item)) . '</li>';
                    }
                    ?>
                </ul>

                
                <h3>Instructions:</h3>
                <p><?php echo nl2br(htmlspecialchars($recipe['instructions'])); ?></p>
            </div>
        </div>
    </div>

<?php include '../footer.php' ?>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js"></script>
</body>
</html>
