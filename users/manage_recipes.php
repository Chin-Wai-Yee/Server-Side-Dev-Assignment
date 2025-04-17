<?php
session_start();
require_once '../database.php';

// Ensure only admin users can access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Handle recipe deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_recipe') {
    $recipe_id = $_POST['recipe_id'];

    // Perform deletion
    $sql = "DELETE FROM recipes WHERE recipe_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $recipe_id);
    
    if ($stmt->execute()) {
        echo "Recipe deleted successfully.";
    } else {
        echo "Failed to delete recipe.";
    }
    exit(); // Prevent page from refreshing after the AJAX request
}

// Fetch all recipes
$search_term = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = trim($_GET['search']);
    $sql = "SELECT * FROM recipes WHERE title LIKE ? OR cuisine_type LIKE ?";
    $stmt = $conn->prepare($sql);
    $search_term_wildcard = "%" . $search_term . "%";
    $stmt->bind_param("ss", $search_term_wildcard, $search_term_wildcard);
} else {
    $sql = "SELECT * FROM recipes";
    $stmt = $conn->prepare($sql);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Recipes</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<style>
    /* Maintain the styles from the user list page */
    body {
        background-image: url('../Image/background.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .card {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .search-bar-container {
        display: flex;
        margin-bottom: 30px;
    }

    .search-bar-container input {
        padding: 10px;
        width: 70%;
        margin-top: 10px;
    }

    .search-bar-container button {
        padding: 10px;
        background-color: #8E2C2B;
        color: white;
        cursor: pointer;
    }

    .search-bar-container button:hover {
        background-color: #8E2C2B;
    }

    .btn-refresh {
        background-color: #8E2C2B;
    }

    .btn-refresh:hover {
        background-color: #8E2C2B;
    }

    .table {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
    }

    .table th,
    .table td {
        padding: 12px;
        text-align: left;
        border: 1px solid #ddd;
    }

    .table th {
        background-color: #f8f9fa;
    }

    .table td {
        background-color: #fff;
    }

    .table tr:hover {
        background-color: #f1f1f1;
    }

    .btn {
        padding: 5px 10px;
        border-radius: 4px;
        text-decoration: none;
    }

    .btn-warning {
        background-color: #ffc107;
        color: white;
    }

    .btn-warning:hover {
        background-color: #e0a800;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }
</style>

<body>
    <!-- Include header -->
    <?php include '../admin_header.php'; ?>

    <div class="container my-5">
        <h1 style="color:lightyellow;">Manage Recipes</h1>

        <!-- Search Bar -->
        <form method="GET" class="search-bar-container">
            <input type="text" name="search" placeholder="Search by Title or Cuisine" value="<?php echo htmlspecialchars($search_term); ?>">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
            <button type="button" onclick="window.location.href='manage_recipes.php';"><i class="fas fa-sync-alt"></i> Refresh</button>
        </form>

        <!-- Recipe Management Section -->
        <div class="card">
            <h2>Recipe List</h2>

            <!-- Display success or error messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success']; ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error']; ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Table to display all recipes -->
            <table class="table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Recipe Id</th>
                        <th>Title</th>
                        <th>Cuisine Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        $i = 1;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr id='recipe_row_" . $row['recipe_id'] . "'>";
                            echo "<td>" . $i++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['recipe_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['cuisine_type']) . "</td>";
                            echo "<td>";
                            echo "<button type='button' class='btn btn-warning btn-sm' onclick='editRecipe(" . $row['recipe_id'] . ")'>Edit</button><br>";
                            echo "<button type='button' class='btn btn-danger btn-sm' onclick='deleteRecipe(" . $row['recipe_id'] . ")'>Delete</button>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No recipes found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Include footer -->
    <?php include '../footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function editRecipe(recipeId) {
            window.location.href = '../users/edit_recipes.php?recipe_id=' + recipeId;
        }

        function deleteRecipe(recipeId) {
            if (confirm("Are you sure you want to delete this recipe?")) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.send("action=delete_recipe&recipe_id=" + recipeId);

                xhr.onload = function() {
                    if (xhr.status == 200) {
                        var row = document.getElementById("recipe_row_" + recipeId);
                        row.parentNode.removeChild(row);
                        alert("Recipe deleted successfully!");
                    } else {
                        alert("Failed to delete recipe.");
                    }
                };
            }
        }
    </script>
</body>
</html>
