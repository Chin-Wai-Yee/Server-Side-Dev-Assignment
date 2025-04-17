<?php
session_start();
require_once '../database.php';

// Ensure only admin users can access this page
if ($_SESSION['role'] !== 'admin') {
    // If the user is not an admin, redirect them to the index page
    header("Location: ../index.php");
    exit();
}

// Check if the user ID is provided
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch the user data from the database
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $_SESSION['error'] = "User not found.";
        header("Location: admin_dashboard.php");
        exit();
    }
} else {
    $_SESSION['error'] = "User ID not specified.";
    header("Location: admin_dashboard.php");
    exit();
}

// Handle form submission for updating user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    // If a password is provided, hash it, otherwise keep the old password
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET username = ?, email = ?, role = ?, password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $username, $email, $role, $hashed_password, $user_id);
    } else {
        // Update user details without changing the password
        $sql = "UPDATE users SET username = ?, email = ?, role = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $username, $email, $role, $user_id);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = "User updated successfully.";
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update user.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Dashboard</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
body {
    background-image: url('../Image/background.png'); /* Path to your background image */
    background-size: cover;  /* Ensure the background covers the entire viewport */
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed; /* Fix the background image while scrolling */
    min-height: 100vh;  /* Ensure the body covers the full height of the viewport */
    display: flex;
    flex-direction: column;
    justify-content: flex-start; /* Align content to the top */
    margin: 0;
    color: white; /* Optional: Set text color for better readability */
}

.wrapper {
    flex-grow: 1; /* This allows the content to take up the remaining height */
}

.container {
    max-width: 800px; /* Limit the max width */
    margin: 0 auto; /* Center the container horizontally */
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: center; /* Center the content vertically */
    align-items: center; /* Center the content horizontally */
}

.card {
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    width: 100%; /* Ensure the card takes full width of the container */
    max-width: 600px; /* Limit the card's max width */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.form-group {
    margin-bottom: 15px;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center; /* Center the form fields horizontally */
}

.form-control {
    width: 80%; /* Form fields will take 80% width of the card */
    padding: 10px;
    margin-top: 10px;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.btn {
    padding: 10px 15px;
    background-color: #8E2C2B;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    width: 80%; /* Button takes 80% of the width */
    margin-left:50px;
}

.btn:hover {
    background-color: #8E2C2B;
}

.alert {
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.alert-success {
    background-color: #28a745;
    color: white;
}

.alert-danger {
    background-color: #dc3545;
    color: white;
}

    </style>
</head>
<body>

<?php 
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): 
    include '../admin_header.php'; 
else: 
    include '../header.php'; 
endif; 
?>

    <div class="container my-5">
        <h1 style="color:lightyellow;">Edit User</h1>

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

        <!-- User Edit Form -->
         <div class="card">
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" style="width:80%;" id="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="role">Role:</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="user" <?php echo ($user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password (Leave blank to keep current password):</label>
                <input type="password" name="password" id="password" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Update User</button>
        </form>
        </div>
        
    </div>

    <?php include '../footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>