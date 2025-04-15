<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

require_once '../database.php';
require_once 'User.php';

$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];

if (empty($email) || empty($password)) {
    $_SESSION['error'] = "All fields are required";
    header("Location: login.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format";
    header("Location: login.php");
    exit;
}

$user = new User();
$user->email = $email;
$user->password = $password;

try {
    if ($user->login()) {
        $_SESSION['user_id'] = $user->user_id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role'] = $user->role;
        
        // Regenerate session ID for security
        session_regenerate_id(true);

        // Redirect based on user role
        if ($_SESSION['role'] === 'admin') {
            // Redirect to admin dashboard
            header('Location: ../users/admin_dashboard.php');
        } else {
            // Redirect to user index page
            header('Location: ../index.php');
        }
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
