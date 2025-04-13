<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

require_once '../database.php';
require_once 'User.php';

$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if (empty($username) || empty($email) || empty($password)) {
    $_SESSION['error'] = "All fields are required";
    header("Location: register.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format";
    header("Location: register.php");
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = "Passwords do not match";
    header("Location: register.php");
    exit;
}

$user = new User();

if ($user->getUserByUsername($username) || $user->getUserByEmail($email)) {
    $_SESSION['error'] = "Username or email already exists";
    header("Location: register.php");
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

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
