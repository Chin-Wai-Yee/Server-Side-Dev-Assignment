<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    // User is logged in, redirect to profile page
    header('Location: profile.php');
} else {
    // User is not logged in, redirect to login page
    header('Location: login.php');
}
exit;
?>