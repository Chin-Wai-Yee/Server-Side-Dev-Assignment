<?php
ini_set('display_errors', 0);
error_reporting(0);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../includes/csrf.php';

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method Not Allowed', 405);
    }

    // Validate session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized', 401);
    }

    // Validate parameters exist
    if (!isset($_POST['discussion_id'], $_POST['vote_value'], $_POST['csrf_token'])) {
        throw new Exception('Missing parameters', 400);
    }

    // Validate CSRF token
    validate_csrf_token($_POST['csrf_token']);

    // Get and validate parameters
    $discussion_id = (int)$_POST['discussion_id'];
    $vote_value = (int)$_POST['vote_value'];
    $user_id = (int)$_SESSION['user_id'];

    if ($discussion_id < 1 || !in_array($vote_value, [-1, 1])) {
        throw new Exception('Invalid parameters', 400);
    }

    // Database operations
    $conn->begin_transaction();

    // Check existing vote
    $check_stmt = $conn->prepare("SELECT vote_value FROM discussions_vote 
                                WHERE user_id = ? AND discussion_id = ?");
    $check_stmt->bind_param("ii", $user_id, $discussion_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $current_vote = $result->fetch_assoc()['vote_value'];
        if ($current_vote === $vote_value) {
            // Remove vote
            $delete_stmt = $conn->prepare("DELETE FROM discussions_vote 
                                        WHERE user_id = ? AND discussion_id = ?");
            $delete_stmt->bind_param("ii", $user_id, $discussion_id);
            $delete_stmt->execute();
            $new_vote = 0;
        } else {
            // Update vote
            $update_stmt = $conn->prepare("UPDATE discussions_vote 
                                         SET vote_value = ?
                                         WHERE user_id = ? AND discussion_id = ?");
            $update_stmt->bind_param("iii", $vote_value, $user_id, $discussion_id);
            $update_stmt->execute();
            $new_vote = $vote_value;
        }
    } else {
        // Insert new vote
        $insert_stmt = $conn->prepare("INSERT INTO discussions_vote 
                                     (user_id, discussion_id, vote_value)
                                     VALUES (?, ?, ?)");
        $insert_stmt->bind_param("iii", $user_id, $discussion_id, $vote_value);
        $insert_stmt->execute();
        $new_vote = $vote_value;
    }

    // Get updated total votes
    $count_stmt = $conn->prepare("SELECT COALESCE(SUM(vote_value), 0) AS total 
                                FROM discussions_vote 
                                WHERE discussion_id = ?");
    $count_stmt->bind_param("i", $discussion_id);
    $count_stmt->execute();
    $total = (int)$count_stmt->get_result()->fetch_assoc()['total'];

    $conn->commit();

    echo json_encode([
        'success' => true,
        'total_votes' => $total,
        'user_vote' => $new_vote
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}