<?php
// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to access this page";
    header('Location: login.php');
    exit;
}
?>