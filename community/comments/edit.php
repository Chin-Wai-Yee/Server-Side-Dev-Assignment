<?php
ob_start();
session_start();
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../includes/csrf.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['ajax'])) {
        throw new Exception('Invalid request method');
    }

    // Check if csrf_token exists in the POST request
    if (!isset($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'error' => 'CSRF token is missing.']);
        exit();
    }

    validate_csrf_token($_POST['csrf_token']);

    $comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
    $content = trim($_POST['content'] ?? '');
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        throw new Exception('Unauthorized');
    }

    if ($comment_id === false || $comment_id < 1) {
        throw new Exception('Invalid comment ID');
    }

    if (empty($content)) {
        throw new Exception('Comment cannot be empty');
    }

    // Ownership check
    $stmt = $conn->prepare("SELECT user_id FROM comments WHERE comment_id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    
    if ($stmt->errno) {
        throw new Exception("Database error: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $comment = $result->fetch_assoc();

    if (!$comment || $comment['user_id'] !== $user_id) {
        throw new Exception('Comment not found or permission denied');
    }

    // Update comment
    $update_stmt = $conn->prepare("UPDATE comments SET content = ? WHERE comment_id = ?");
    $update_stmt->bind_param("si", $content, $comment_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Update failed: " . $update_stmt->error);
    }

    echo json_encode([
        'success' => true,
        'updated_content' => nl2br(htmlspecialchars($content))
    ]);

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    ob_end_flush();
    exit();
}