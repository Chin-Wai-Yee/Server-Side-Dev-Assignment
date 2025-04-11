<?php
require 'database.php';  // DB connection

$search_term = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = trim($_GET['search']);
    $sql = "SELECT * FROM recipes WHERE title LIKE '%$search_term%'";
} else {
    $sql = "SELECT * FROM recipes";
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recipe Management</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
</head>
<body>
    <?php include '../header.php'; ?>

    <div class="recipe-header" id="top">
        <h1 style="text-shadow:4px 4px 6px rgba(255, 255, 255, 0.4);">Recipe Management</h1>
        <h3 style="text-shadow:4px 4px 6px rgba(255, 255, 255, 0.4);">Welcome to the recipe page! Feel free to manage your recipe here. Let's create, edit, delete or search your recipe with us!</h3>

        <div class="button-container">
            <a href="add_recipe.php" class="action-button add-recipe">Add Recipe</a>
            <a href="edit_recipe.php?recipe_id=2" class="action-button edit-recipe">Edit Recipe</a>
            <a href="delete_recipe.php" class="action-button delete-recipe">Delete Recipe</a>
            <a href="#search-bar" class="action-button search-recipe">Search Recipe</a>
        </div>

        <h3 style="margin-top:30px;text-shadow:4px 4px 6px rgba(255, 255, 255, 0.4);">Scroll down to see your recipe!</h3>

        <h1 id="search-bar" style="margin-top:10%;text-shadow:4px 4px 6px rgba(255, 255, 255, 0.4);">Recipe List</h1>

        <!-- SEARCH BAR -->
        <form method="GET" class="search-bar-container" onsubmit="return scrollToSearch();">
            <input type="text" name="search" style="margin-top:10px;" placeholder="Search recipe by title..." value="<?php echo htmlspecialchars($search_term); ?>">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
            <button type="button" onclick="window.location.href='recipe.php#search-bar';"><i class="fas fa-sync-alt"></i> Refresh</button>
        </form>

        <!-- RECIPE GRID -->
        <div class="recipe-grid">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<a class='recipe-box' href='recipe_detail.php?recipe_id=" . $row['recipe_id'] . "'>";
                    echo "<div class='recipe-title'>" . htmlspecialchars($row["title"]) . "</div>";
                    if (!empty($row["image_path"])) {
                        echo "<img src='" . htmlspecialchars($row["image_path"]) . "' alt='Recipe Image'>";
                    } else {
                        echo "<div class='recipe-image-placeholder'>No Image</div>";
                    }
                    echo "</a>";
                }
            } else {
                echo "<p>No recipes found.</p>";
            }
            ?>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script>
        function scrollToSearch() {
            const anchor = document.getElementById("search-bar");
            if (anchor) anchor.scrollIntoView({ behavior: "smooth" });
            return true;
        }
    </script>
</body>
</html>
