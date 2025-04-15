<?php
session_start();
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../includes/csrf.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to delete a discussion";
    header("Location: /recipe%20culinary/login.php");
    exit();
}

// Check if ID exists and is numeric
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid discussion ID";
    header("Location: /recipe%20culinary/community/discussions/");
    exit();
}

$discussion_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// First verify the user owns this discussion
$stmt = $conn->prepare("SELECT user_id FROM discussions WHERE discussion_id = ?");
$stmt->bind_param("i", $discussion_id);
$stmt->execute();
$result = $stmt->get_result();
$discussion = $result->fetch_assoc();

if (!$discussion) {
    $_SESSION['error'] = "Discussion not found";
    header("Location: /recipe%20culinary/community/discussions/");
    exit();
}

// Check if current user is the owner
if ($discussion['user_id'] != $user_id) {
    $_SESSION['error'] = "You can only delete your own discussions";
    header("Location: /recipe%20culinary/community/discussions/view.php?id=" . $discussion_id);
    exit();
}

// Delete related comments votes first
$stmt = $conn->prepare("DELETE cv FROM comments_vote cv 
                       INNER JOIN comments c ON cv.comment_id = c.comment_id 
                       WHERE c.discussion_id = ?");
$stmt->bind_param("i", $discussion_id);
$stmt->execute();

// Delete all comments for this discussion
$stmt = $conn->prepare("DELETE FROM comments WHERE discussion_id = ?");
$stmt->bind_param("i", $discussion_id);
$stmt->execute();

// Delete discussion votes
$stmt = $conn->prepare("DELETE FROM discussions_vote WHERE discussion_id = ?");
$stmt->bind_param("i", $discussion_id);
$stmt->execute();

// Finally delete the discussion itself
$stmt = $conn->prepare("DELETE FROM discussions WHERE discussion_id = ? AND user_id = ?");
$stmt->bind_param("ii", $discussion_id, $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $_SESSION['success'] = "Discussion deleted successfully";
} else {
    $_SESSION['error'] = "Failed to delete discussion";
}

// Redirect to discussions list
header("Location: /recipe%20culinary/community/discussions/list.php");
exit();