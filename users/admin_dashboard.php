<?php
session_start();
require_once '../database.php';

// Ensure only admin users can access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_user') {
    $user_id = $_POST['user_id'];

    // Perform deletion
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        echo "User deleted successfully.";
    } else {
        echo "Failed to delete user.";
    }
    exit(); // Prevent page from refreshing after the AJAX request
}

// Fetch all users
$search_term = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = trim($_GET['search']);
    $sql = "SELECT * FROM users WHERE username LIKE ? OR email LIKE ?";
    $stmt = $conn->prepare($sql);
    $search_term_wildcard = "%" . $search_term . "%";
    $stmt->bind_param("ss", $search_term_wildcard, $search_term_wildcard);
} else {
    $sql = "SELECT * FROM users";
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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Add other styles as needed -->
</head>

<style>
        body {
            background-image: url('../Image/background.png'); /* Path to your background image */
            background-size: cover;  /* Ensure the background covers the entire viewport */
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed; /* Fix the background image while scrolling */
            min-height: 100vh;  /* Ensure the body covers the full height of the viewport */
            display: flex;
            flex-direction: column; /* Make sure the content can grow and push footer down */
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
            margin-top:10px;
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
    <?php include '../header.php'; ?>

    <div class="container my-5">
        <h1 style="color:lightyellow;">Admin Dashboard</h1>

        <!-- Search Bar -->
        <form method="GET" class="search-bar-container">
            <input type="text" name="search" placeholder="Search by Username or Email" value="<?php echo htmlspecialchars($search_term); ?>">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
            <button type="button" onclick="window.location.href='admin_dashboard.php';"><i class="fas fa-sync-alt"></i> Refresh</button>
        </form>

        <!-- User Management Section -->
        <div class="card">
            <h2>User List</h2>

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

            <!-- Table to display all users -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>User Id</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        $i = 1;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr id='user_row_" . $row['user_id'] . "'>";
                            echo "<td>" . $i++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                            echo "<td>";
                            echo "<button type='button' class='btn btn-warning btn-sm' onclick='editUser(" . $row['user_id'] . ")'>Edit</button>";
                            echo"<br>";
                            echo "<button type='button' class='btn btn-danger btn-sm' onclick='deleteUser(" . $row['user_id'] . ")'>Delete</button>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No users found.</td></tr>";
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
        function editUser(userId) {
            // Redirect to the edit_user.php page with the user ID as a parameter
            window.location.href = 'edit_user.php?user_id=' + userId;
        }
        
        function deleteUser(userId) {
            if (confirm("Are you sure you want to delete this user?")) {
                // Create an AJAX request
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "", true); // Empty URL means it stays on the same page
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                // Send the user ID via POST
                xhr.send("action=delete_user&user_id=" + userId);

                // Handle the response after deletion
                xhr.onload = function() {
                    if (xhr.status == 200) {
                        // If successful, remove the user row from the table
                        var row = document.getElementById("user_row_" + userId);
                        row.parentNode.removeChild(row);
                        alert("User deleted successfully!");
                    } else {
                        alert("Failed to delete user.");
                    }
                };
            }
        }
    </script>
</body>
</html>
