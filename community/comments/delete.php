<?php
session_start();
require_once __DIR__ . '/../../database.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$comment_id = (int)$_GET['id'];

// Verify comment ownership
$stmt = $conn->prepare("SELECT discussion_id FROM comments WHERE comment_id = ? AND user_id = ?");
$stmt->bind_param("ii", $comment_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Comment not found or permission denied");
}

$discussion_id = $result->fetch_assoc()['discussion_id'];

// Delete comment
$delete_stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
$delete_stmt->bind_param("i", $comment_id);
$delete_stmt->execute();

header("Location: ../discussions/view.php?id=$discussion_id");
exit();
?>