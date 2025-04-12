<?php
require '../database.php';

if (!isset($_GET['recipe_id'])) {
    echo "Invalid recipe ID";
    exit;
}

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
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
</head>
<body>
<?php include '../header.php'; ?>
    <div class="recipe-detail">
        <h1 style="text-align:center;"><?php echo htmlspecialchars($recipe['title']); ?></h1>
        
        <div class="recipe-layout">
            <!-- Image on the left -->
            <?php if (!empty($recipe["image_path"])): ?>
                <div class="recipe-image">
                    <img src="<?php echo htmlspecialchars($recipe['image_path']); ?>" alt="Recipe Image">
                </div>
            <?php endif; ?>

            <!-- Text content on the right -->
            <div class="recipe-content">
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
</body>
</html>
