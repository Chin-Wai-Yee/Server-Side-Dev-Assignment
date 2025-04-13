<?php
class VoteController {
    private $conn;
    private $vote;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->vote = new Vote($conn);
    }

    public function handle_request($action) {
        switch ($action) {
            case 'cast':
                $this->cast_vote();
                break;
            case 'results':
                $this->view_results();
                break;
            default:
                $this->view_results();
                break;
        }
    }

    private function cast_vote() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "You must be logged in to vote";
            header('Location: index.php?page=competitions');
            exit;
        }

        // Get competition recipe ID
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_SESSION['error'] = "Invalid competition recipe ID";
            header('Location: index.php?page=competitions');
            exit;
        }

        $comp_recipe_id = $_GET['id'];
        
        // Get competition ID
        if (!isset($_GET['competition_id']) || !is_numeric($_GET['competition_id'])) {
            $_SESSION['error'] = "Invalid competition ID";
            header('Location: index.php?page=competitions');
            exit;
        }

        $competition_id = $_GET['competition_id'];
        
        // Get competition status
        $competition = new Competition($this->conn);
        $comp_details = $competition->get_by_id($competition_id);

        if (!$comp_details) {
            $_SESSION['error'] = "Competition not found";
            header('Location: index.php?page=competitions');
            exit;
        }

        // Check if competition is in voting phase
        if ($comp_details['status'] !== 'voting') {
            $_SESSION['error'] = "Voting is not currently open for this competition";
            header('Location: index.php?page=competitions&action=view&id=' . $competition_id);
            exit;
        }

        // Check if user already voted for this recipe
        $this->vote->recipe_id = $comp_recipe_id;
        $this->vote->user_id = $_SESSION['user_id'];
        
        if ($this->vote->already_voted()) {
            $_SESSION['error'] = "You have already voted for this recipe";
            header('Location: index.php?page=competitions&action=view&id=' . $competition_id);
            exit;
        }

        // Cast the vote
        if ($this->vote->create()) {
            $_SESSION['success'] = "Vote cast successfully";
        } else {
            $_SESSION['error'] = "Failed to cast vote";
        }
        
        header('Location: index.php?page=competitions&action=view&id=' . $competition_id);
        exit;
    }

    private function view_results() {
        // Get competition ID
        if (!isset($_GET['competition_id']) || !is_numeric($_GET['competition_id'])) {
            $_SESSION['error'] = "Invalid competition ID";
            header('Location: index.php?page=competitions');
            exit;
        }

        $competition_id = $_GET['competition_id'];
        $competition = new Competition($this->conn);
        $comp_details = $competition->get_by_id($competition_id);

        if (!$comp_details) {
            $_SESSION['error'] = "Competition not found";
            header('Location: index.php?page=competitions');
            exit;
        }

        // Only show results if competition is closed or in voting phase
        if ($comp_details['status'] !== 'closed' && $comp_details['status'] !== 'voting') {
            $_SESSION['error'] = "Results are not available yet";
            header('Location: index.php?page=competitions&action=view&id=' . $competition_id);
            exit;
        }

        // Get voting results
        $top_recipes = $this->vote->get_top_voted($competition_id);
        include 'views/votes/results.php';
    }
}
?>
