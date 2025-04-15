<?php
session_start();
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../includes/csrf.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$comment_id = (int)$_GET['id'];

// Get existing comment
$stmt = $conn->prepare("SELECT * FROM comments WHERE comment_id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$comment = $stmt->get_result()->fetch_assoc();

if (!$comment || $comment['user_id'] != $_SESSION['user_id']) {
    die("Invalid comment or permissions");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token($_POST['csrf_token']);
    
    $content = htmlspecialchars($_POST['content']);
    
    $update_stmt = $conn->prepare("UPDATE comments SET content = ? WHERE comment_id = ?");
    $update_stmt->bind_param("si", $content, $comment_id);
    $update_stmt->execute();
    
    header("Location: ../discussions/view.php?id={$comment['discussion_id']}");
    exit();
}

include __DIR__ . '/../../header.php';
?>

<form method="post" class="comment-edit-form">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    <textarea name="content"><?= htmlspecialchars($comment['content']) ?></textarea>
    <button type="submit">Update Comment</button>
</form>

<?php include __DIR__ . '/../../footer.php'; ?>