<?php
session_start();
require '../database.php';  // DB connection

// Handle search functionality
$search_term = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = trim($_GET['search']);
    // Query to show all recipes matching the search term
    $sql = "SELECT * FROM recipes WHERE title LIKE ?";
    $stmt = $conn->prepare($sql);
    $search_term = '%' . $search_term . '%';
    $stmt->bind_param("s", $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Query to show all recipes
    $sql = "SELECT * FROM recipes";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Management</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <?php include '../header.php'; ?>

    <div class="recipe-header" id="top">
        <h1 style="text-shadow:4px 4px 6px rgba(255, 255, 255, 0.4);">Recipe Management</h1>
        <h3 style="text-shadow:4px 4px 6px rgba(255, 255, 255, 0.4);">Welcome to the recipe page! Feel free to browse all recipes here. Let's search for your favorite recipe!</h3>

        <!-- SEARCH BAR -->
        <form method="GET" class="search-bar-container" onsubmit="return scrollToSearch();">
            <input type="text" name="search" style="margin-top:10px;" placeholder="Search recipe by title..." value="<?php echo htmlspecialchars($search_term); ?>">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
            <button type="button" onclick="window.location.href='../recipes/index.php#search-bar';"><i class="fas fa-sync-alt"></i> Refresh</button>
        </form>

        <!-- RECIPE GRID -->
        <div class="recipe-grid">
            <?php
            $logged_in_user_id = $_SESSION['user_id']; 
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<a href='recipe_detail.php?recipe_id=" . $row['recipe_id'] . "' class='recipe-box'>";
                    echo "<div class='recipe-title'>" . htmlspecialchars($row["title"]) . "</div>";
                    if (!empty($row["image_path"])) {
                        echo "<img src='" . htmlspecialchars($row["image_path"]) . "' alt='Recipe Image'>";
                    } else {
                        echo "<div class='recipe-image-placeholder'>No Image</div>";
                    }

                    echo "</a>";  // Closing recipe-box link
                }
            } else {
                echo "<p>No recipes found.</p>";
            }
            ?>
        </div>  <!-- Closing recipe-grid div -->

    </div>  <!-- Closing recipe-header div -->

    <script>
        function scrollToSearch() {
            const anchor = document.getElementById("search-bar");
            if (anchor) anchor.scrollIntoView({
                behavior: "smooth"
            });
            return true;
        }
    </script>

    <?php include '../footer.php'; ?>
</body>

</html>
