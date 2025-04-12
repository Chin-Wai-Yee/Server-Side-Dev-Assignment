<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

// Include necessary files
require_once '../database.php';
require_once 'User.php';

// Get form data
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validate input
if (empty($username) || empty($email) || empty($password)) {
    $_SESSION['error'] = "All fields are required";
    header("Location: register.php");
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = "Passwords do not match";
    header("Location: register.php");
    exit;
}

// Create user object - will use the mysqli $conn from database.php
$user = new User();

// Check if username or email already exists
if ($user->getUserByUsername($username) || $user->getUserByEmail($email)) {
    $_SESSION['error'] = "Username or email already exists";
    header("Location: register.php");
    exit;
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Create user
if ($user->register($username, $email, $hashed_password)) {
    $_SESSION['success'] = "Registration successful. Please log in.";
    header("Location: login.php");
    exit;
} else {
    $_SESSION['error'] = "Registration failed";
    header("Location: register.php");
    exit;
}
?>