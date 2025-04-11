<?php
// admin_dashboard.php
session_start();
require 'database.php'; // Your DB connection file

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle actions (delete, promote, demote)
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $action = $_GET['action'];

    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    } elseif ($action === 'promote') {
        $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    } elseif ($action === 'demote') {
        $stmt = $conn->prepare("UPDATE users SET role = 'user' WHERE user_id = ? AND user_id != ?");
        $stmt->bind_param("ii", $user_id, $_SESSION['user_id']); // prevent self-demotion
        $stmt->execute();
    }

    header("Location: admin_dashboard.php");
    exit();
}

// Fetch users
$result = $conn->query("SELECT user_id, username, email, role FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        table {
            width: 80%;
            border-collapse: collapse;
            margin: 20px auto;
        }
        th, td {
            padding: 10px;
            border: 1px solid #999;
            text-align: center;
        }
        a {
            padding: 6px 12px;
            margin: 0 4px;
            background-color: #8E2C2B;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        a:hover {
            background-color: black;
        }
        h1 {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Admin Dashboard - User Management</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['user_id']; ?></td>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo $row['role']; ?></td>
            <td>
                <?php if ($row['role'] === 'user'): ?>
                    <a href="?action=promote&user_id=<?php echo $row['user_id']; ?>">Promote</a>
                <?php elseif ($row['role'] === 'admin' && $row['user_id'] != $_SESSION['user_id']): ?>
                    <a href="?action=demote&user_id=<?php echo $row['user_id']; ?>">Demote</a>
                <?php endif; ?>
                <?php if ($row['user_id'] != $_SESSION['user_id']): ?>
                    <a href="?action=delete&user_id=<?php echo $row['user_id']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
