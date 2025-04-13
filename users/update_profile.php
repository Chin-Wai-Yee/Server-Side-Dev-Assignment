<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'require_login.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

// Include necessary files
require_once '../database.php';
require_once 'User.php';

// User object is already set up to use mysqli connection
$user = new User();
$user->user_id = $_SESSION['user_id'];

// Load current user data
$user->read_single();

// Get form data
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$bio = trim($_POST['bio']);

// Check if username or email is changing
if($username != $user->username) {
    // Check if new username already exists
    $temp_user = new User();
    if($temp_user->getUserByUsername($username)) {
        $_SESSION['error'] = "Username already exists";
        header('Location: profile.php');
        exit;
    }
}

if($email != $user->email) {
    // Check if new email already exists
    $temp_user = new User();
    if($temp_user->getUserByEmail($email)) {
        $_SESSION['error'] = "Email already exists";
        header('Location: profile.php');
        exit;
    }
}

// Update profile information
if($user->update_profile($username, $email, $bio)) {
    // Update session username if changed
    if($username != $_SESSION['username']) {
        $_SESSION['username'] = $username;
    }
    
    // Handle profile image upload if present
    if(!empty($_FILES['profile_image']['name'])) {
        $result = handle_image_upload($user);
        if($result !== true) {
            $_SESSION['error'] = $result; // $result contains error message
        }
    }
    
    // Handle password change if requested
    if(!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
        $result = handle_password_change($user);
        if($result !== true) {
            $_SESSION['error'] = $result; // $result contains error message
        }
    }
    
    if(!isset($_SESSION['error'])) {
        $_SESSION['success'] = "Profile updated successfully";
    }
} else {
    $_SESSION['error'] = "Failed to update profile";
}

header('Location: profile.php');
exit;

// Helper function to handle image upload
function handle_image_upload($user) {
    // Get file info
    $file_name = $_FILES['profile_image']['name'];
    $file_tmp = $_FILES['profile_image']['tmp_name'];
    $file_size = $_FILES['profile_image']['size'];
    $file_error = $_FILES['profile_image']['error'];
    
    // Get file extension
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Allowed file types
    $allowed = array('jpg', 'jpeg', 'png', 'gif');
    
    if(!in_array($file_ext, $allowed)) {
        return "Invalid file type. Allowed types: jpg, jpeg, png, gif";
    }
    
    if($file_error !== 0) {
        return "Error uploading file. Error code: $file_error";
    }
    
    if($file_size > 2097152) { // 2MB max
        return "File is too large. Maximum size is 2MB";
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = '../assets/uploads/profile_images/';
    if(!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $new_filename = uniqid('profile_') . '.' . $file_ext;
    $upload_path = $upload_dir . $new_filename;
    
    if(move_uploaded_file($file_tmp, $upload_path)) {
        // Update database with new profile image path
        $image_path = 'assets/uploads/profile_images/' . $new_filename;
        if($user->update_profile_image($image_path)) {
            return true;
        } else {
            return "Failed to update profile image in database";
        }
    } else {
        return "Failed to upload image";
    }
}

// Helper function to handle password change
function handle_password_change($user) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password
    if($new_password !== $confirm_password) {
        return "New passwords do not match";
    }
    
    if(strlen($new_password) < 6) {
        return "Password must be at least 6 characters";
    }
    
    // Verify current password
    if(!$user->verify_password($current_password)) {
        return "Current password is incorrect";
    }
    
    // Update password
    if($user->update_password($new_password)) {
        return true;
    } else {
        return "Failed to update password";
    }
}
?>