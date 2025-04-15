<?php
session_start();

// Include database and models
require_once '../database.php';
require_once 'models/Competition.php';
require_once 'models/Recipe.php';
require_once 'models/Vote.php';

// Include controllers
require_once 'controllers/CompetitionController.php';

// Basic routing
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Check if user is logged in
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$logged_in = isset($_SESSION['user_id']);

// Include header
include 'views/partials/header.php';

// Handle routes
if ($page == 'competitions') {
    $competition_controller = new CompetitionController($conn);
    $competition_controller->handle_request($action);
} else {
    include 'views/home.php';
}

// Include footer
include 'views/partials/footer.php';
?>
