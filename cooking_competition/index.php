<?php
session_start();

// Include database and models
require_once '../database.php';
require_once '../users/User.php';
require_once 'models/Competition.php';
require_once 'models/Recipe.php';
require_once 'models/Vote.php';

// Include controllers
require_once 'controllers/CompetitionController.php';
require_once 'controllers/RecipeController.php';
require_once 'controllers/VoteController.php';

// Update competition statuses
$competition = new Competition($conn);
$competition->update_status();

// Basic routing
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Check if user is logged in
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$logged_in = false;
if (isset($_SESSION['user_id'])) {
    global $logged_in;
    $logged_in = true;
}

// Include header
include 'views/partials/header.php';

// Handle routes
switch ($page) {
    case 'competitions':
        $competition_controller = new CompetitionController($conn);
        $competition_controller->handle_request($action);
        break;
    case 'votes':
        $vote_controller = new VoteController($conn);
        $vote_controller->handle_request($action);
        break;
    default:
        // Home page
        include 'views/home.php';
        break;
}

// Include footer
include 'views/partials/footer.php';
?>
