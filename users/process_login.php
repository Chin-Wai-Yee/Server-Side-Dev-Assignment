<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Include necessary files
require_once '../database.php';
require_once 'User.php';

// Get form data and sanitize
$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$password = $_POST['password']; // Don't trim password as spaces might be part of it

// Validate input
if (empty($email) || empty($password)) {
    $_SESSION['error'] = "All fields are required";
    header("Location: login.php");
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format";
    header("Location: login.php");
    exit;
}

// Create user object - it will use the mysqli $conn from database.php
$user = new User();
$user->email = $email;
$user->password = $password;

// Try to login and handle the result
try {
    if ($user->login()) {
        // Set user session data
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Redirect to home page
        header('Location: ../index.php');
        exit;
    } else {
        $_SESSION['error'] = 'Invalid email or password';
        header('Location: login.php');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Login failed. Please try again later.';
    header('Location: login.php');
    exit;
}
?>