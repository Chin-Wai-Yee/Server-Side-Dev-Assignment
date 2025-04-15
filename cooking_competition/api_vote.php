<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set header to JSON response
header('Content-Type: application/json');

// Include only necessary files
require_once '../database.php';
require_once 'models/Vote.php';

// Initialize vote model
$vote = new Vote($conn);

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);
if (!$logged_in) {
    echo json_encode(['status' => 'error', 'message' => 'User must be logged in']);
    exit;
}

// Process API action
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Set user ID from session
$vote->user_id = $_SESSION['user_id'];

// Handle different actions
switch ($action) {
    case 'create':
        if (isset($_POST['recipe_id'])) {
            $vote->recipe_id = $_POST['recipe_id'];
            if ($vote->create()) {
                echo json_encode(['status' => 'success', 'message' => 'Vote created']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create vote']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Missing recipe_id']);
        }
        break;
        
    case 'remove':
        if (isset($_POST['recipe_id'])) {
            $vote->recipe_id = $_POST['recipe_id'];
            if ($vote->remove()) {
                echo json_encode(['status' => 'success', 'message' => 'Vote removed']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to remove vote']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Missing recipe_id']);
        }
        break;
        
    case 'check':
        if (isset($_GET['recipe_id'])) {
            $vote->recipe_id = $_GET['recipe_id'];
            $already_voted = $vote->already_voted();
            echo json_encode(['status' => 'success', 'voted' => $already_voted]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Missing recipe_id']);
        }
        break;
        
    case 'user_votes':
        $votes = $vote->get_user_votes();
        echo json_encode(['status' => 'success', 'votes' => $votes]);
        break;
        
    case 'count':
        if (isset($_GET['recipe_id'])) {
            $count = $vote->count_votes($_GET['recipe_id']);
            echo json_encode(['status' => 'success', 'count' => $count]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Missing recipe_id']);
        }
        break;
        
    case 'top_voted':
        if (isset($_GET['competition_id'])) {
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 3;
            $top_recipes = $vote->get_top_voted($_GET['competition_id'], $limit);
            echo json_encode(['status' => 'success', 'recipes' => $top_recipes]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Missing competition_id']);
        }
        break;
        
    case 'toggle':
        if (isset($_POST['recipe_id'])) {
            $vote->recipe_id = $_POST['recipe_id'];
            $already_voted = $vote->already_voted();
            
            $result = false;
            if ($already_voted) {
                // Remove vote if already voted
                $result = $vote->remove();
                $message = 'Vote removed';
                $voted = false;
            } else {
                // Add vote if not voted
                $result = $vote->create();
                $message = 'Vote added';
                $voted = true;
            }
            
            if ($result) {
                // Get updated vote count
                $count = $vote->count_votes($vote->recipe_id);
                echo json_encode([
                    'status' => 'success', 
                    'message' => $message, 
                    'voted' => $voted,
                    'count' => $count
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to toggle vote']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Missing recipe_id']);
        }
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>