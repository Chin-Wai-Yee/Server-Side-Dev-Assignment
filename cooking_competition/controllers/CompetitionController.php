<?php
class CompetitionController {
    private $conn;
    private $competition;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->competition = new Competition($conn);
    }

    public function handle_request($action) {
        switch ($action) {
            case 'create':
                $this->create_competition();
                break;
            case 'view':
                $this->view_competition();
                break;
            case 'edit':
                $this->edit_competition();
                break;
            case 'delete':
                $this->delete_competition();
                break;
            case 'submit_recipe':
                $this->submit_recipe();
                break;
            case 'withdraw':
                $this->withdraw_recipe();
                break;
            case 'vote':
                $this->handle_vote();
                break;
            case 'search':
                $this->search_competitions();
                break;
            default:
                $this->list_competitions();
                break;
        }
    }

    private function list_competitions() {
        $status = $_GET['status'] ?? 'all';
        
        $this->competition->update_status();
        if ($status) {
            if ($status === 'active') {
                $competitions = $this->competition->get_by_status('active');
            } elseif ($status === 'voting') {
                $competitions = $this->competition->get_by_status('voting');
            } elseif ($status === 'completed') {
                $competitions = $this->competition->get_by_status('completed');
            } elseif ($status === 'upcoming') {
                $competitions = $this->competition->get_by_status('upcoming');
            } else {
                $competitions = $this->competition->get_all();
            }
        } else {
            $competitions = $this->competition->get_all();
        }
        
        // Make logged_in status available to the view
        global $logged_in;

        include 'views/competitions/index.php';
    }

    private function create_competition() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "You must be logged in to create competitions";
            header('Location: index.php?page=competitions');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->competition->title = $_POST['title'] ?? '';
            $this->competition->description = $_POST['description'] ?? '';
            
            // Handle image upload
            $image_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                // Check if the upload directory exists, create if not
                $upload_dir = 'database/competition_images/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Process the uploaded file
                $file_name = $_FILES['image']['name'];
                $file_tmp = $_FILES['image']['tmp_name'];
                $file_size = $_FILES['image']['size'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // File validation
                $extensions = array("jpeg", "jpg", "png", "gif", "webp");
                
                if (in_array($file_ext, $extensions) && $file_size < 2097152) { // 2MB max
                    $new_file_name = uniqid('comp_') . '.' . $file_ext;
                    $upload_path = $upload_dir . $new_file_name;
                    
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $image_path = $upload_path;
                    } else {
                        $_SESSION['error'] = "Error uploading image";
                        include 'views/competitions/create.php';
                        return;
                    }
                } else {
                    if (!in_array($file_ext, $extensions)) {
                        $_SESSION['error'] = "Invalid file extension. Allowed types: jpeg, jpg, png, gif, webp";
                    } else {
                        $_SESSION['error'] = "File size exceeds 2MB limit";
                    }
                    include 'views/competitions/create.php';
                    return;
                }
            }
            
            $this->competition->image = $image_path;
            
            // Combine date and time inputs
            $start_date = $_POST['start_date'] ?? '';
            $start_time = $_POST['start_time'] ?? '00:00';
            $this->competition->start_date = $start_date . ' ' . $start_time;
            
            $end_date = $_POST['end_date'] ?? '';
            $end_time = $_POST['end_time'] ?? '23:59';
            $this->competition->end_date = $end_date . ' ' . $end_time;
            
            $voting_end_date = $_POST['voting_end_date'] ?? '';
            $voting_end_time = $_POST['voting_end_time'] ?? '23:59';
            $this->competition->voting_end_date = $voting_end_date . ' ' . $voting_end_time;

            $this->competition->created_by = $_SESSION['user_id'];

            // Validate dates: start_date < end_date < voting_end_date
            $start_timestamp = strtotime($this->competition->start_date);
            $end_timestamp = strtotime($this->competition->end_date);
            $voting_end_timestamp = strtotime($this->competition->voting_end_date);
            
            if ($start_timestamp >= $end_timestamp) {
                $_SESSION['error'] = "Start date must be before end date";
                include 'views/competitions/create.php';
                return;
            }
            
            if ($end_timestamp >= $voting_end_timestamp) {
                $_SESSION['error'] = "End date must be before voting end date";
                include 'views/competitions/create.php';
                return;
            }

            if ($this->competition->create()) {
                $_SESSION['success'] = "Competition created successfully";
                header('Location: index.php?page=competitions');
                return;
            } else {
                $_SESSION['error'] = "Failed to create competition";
            }
        }

        include 'views/competitions/create.php';
    }

    private function view_competition() {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_SESSION['error'] = "Invalid competition ID";
            header('Location: index.php?page=competitions');
            exit;
        }

        $competition = $this->competition->get_by_id($_GET['id']);
        if (!$competition) {
            $_SESSION['error'] = "Competition not found";
            header('Location: index.php?page=competitions');
            exit;
        }

        // Load recipes for this competition
        $recipes = $this->competition->get_recipes($_GET['id']);
        
        // Get winner information if competition is completed
        $winner = null;
        if ($competition['status'] == 'completed') {
            // Get the recipe with the most votes
            $vote = new Vote($this->conn);
            $winner = $vote->get_competition_winner($competition['id']);
        }
        
        // Get the global logged_in variable
        global $logged_in;

        include 'views/competitions/view.php';
    }

    private function edit_competition() {
        // Allow only admins or the creator to edit competitions
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "You don't have permission to edit competitions";
            header('Location: index.php?page=competitions');
            return;
        }

        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_SESSION['error'] = "Invalid competition ID";
            header('Location: index.php?page=competitions');
            exit;
        }

        $competition = $this->competition->get_by_id($_GET['id']);
        if (!$competition || ($competition['created_by'] != $_SESSION['user_id'] && $_SESSION['role'] != 'admin')) {
            $_SESSION['error'] = "You don't have permission to edit this competition";
            header('Location: index.php?page=competitions');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            
            // Handle image upload
            $image_path = $competition['image']; // Keep existing image by default
            error_log("Existing image path: " . $image_path); // Debugging line
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                error_log("Image upload detected"); // Debugging line
                // Check if the upload directory exists, create if not
                $upload_dir = 'database/competition_images/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Process the uploaded file
                $file_name = $_FILES['image']['name'];
                $file_tmp = $_FILES['image']['tmp_name'];
                $file_size = $_FILES['image']['size'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // File validation
                $extensions = array("jpeg", "jpg", "png", "gif", "webp");
                
                if (in_array($file_ext, $extensions) && $file_size < 2097152) { // 2MB max
                    $new_file_name = uniqid('comp_') . '.' . $file_ext;
                    $upload_path = $upload_dir . $new_file_name;
                    
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        // If there's an existing image, remove it 
                        if (!empty($competition['image']) && file_exists($competition['image'])) {
                            unlink($competition['image']);
                        }
                        $image_path = $upload_path;
                    } else {
                        $_SESSION['error'] = "Error uploading image";
                        include 'views/competitions/edit.php';
                        return;
                    }
                } else {
                    if (!in_array($file_ext, $extensions)) {
                        $_SESSION['error'] = "Invalid file extension. Allowed types: jpeg, jpg, png, gif, webp";
                    } else {
                        $_SESSION['error'] = "File size exceeds 2MB limit";
                    }
                    include 'views/competitions/edit.php';
                    return;
                }
            }
            
            // Combine date and time inputs
            $start_date = $_POST['start_date'] ?? '';
            $start_time = $_POST['start_time'] ?? '00:00';
            $start_date_time = $start_date . ' ' . $start_time;
            
            $end_date = $_POST['end_date'] ?? '';
            $end_time = $_POST['end_time'] ?? '23:59';
            $end_date_time = $end_date . ' ' . $end_time;
            
            $voting_end_date = $_POST['voting_end_date'] ?? '';
            $voting_end_time = $_POST['voting_end_time'] ?? '23:59';
            $voting_end_date_time = $voting_end_date . ' ' . $voting_end_time;
            
            $status = $_POST['status'] ?? $competition['status']; // Use existing status if not provided

            // Validate dates: start_date < end_date < voting_end_date
            $start_timestamp = strtotime($start_date_time);
            $end_timestamp = strtotime($end_date_time);
            $voting_end_timestamp = strtotime($voting_end_date_time);
            
            if ($start_timestamp >= $end_timestamp) {
                $_SESSION['error'] = "Start date must be before end date";
                include 'views/competitions/edit.php';
                return;
            }
            
            if ($end_timestamp >= $voting_end_timestamp) {
                $_SESSION['error'] = "End date must be before voting end date";
                include 'views/competitions/edit.php';
                return;
            }

            error_log("Image path: " . $image_path); // Debugging line

            if ($this->competition->update($_GET['id'], $title, $description, $start_date_time, $end_date_time, $voting_end_date_time, $status, $image_path)) {
                $_SESSION['success'] = "Competition updated successfully";
                header('Location: index.php?page=competitions');
                return;
            } else {
                $_SESSION['error'] = "Failed to update competition";
            }
        }

        include 'views/competitions/edit.php';
    }

    private function delete_competition() {
        // Allow only admins or the creator to delete competitions
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "You don't have permission to delete competitions";
            header('Location: index.php?page=competitions');
            return;
        }

        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_SESSION['error'] = "Invalid competition ID";
            header('Location: index.php?page=competitions');
            exit;
        }

        $competition = $this->competition->get_by_id($_GET['id']);
        if (!$competition || ($competition['created_by'] != $_SESSION['user_id'] && $_SESSION['role'] != 'admin')) {
            $_SESSION['error'] = "You don't have permission to delete this competition";
            header('Location: index.php?page=competitions');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->competition->delete($_GET['id'])) {
                $_SESSION['success'] = "Competition deleted successfully";
            } else {
                $_SESSION['error'] = "Failed to delete competition";
            }
            header('Location: index.php?page=competitions');
            exit;
        }

        include 'views/competitions/delete.php';
    }

    private function submit_recipe() {
        // This function submits a recipe to a competition
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "You must be logged in to submit a recipe";
            header('Location: index.php?page=auth&action=login');
            exit;
        }

        // Check for valid competition ID
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_SESSION['error'] = "Invalid competition ID";
            header('Location: index.php?page=competitions');
            exit;
        }

        $competition_id = $_GET['id'];
        $competition = $this->competition->get_by_id($competition_id);
        
        if (!$competition) {
            $_SESSION['error'] = "Competition not found";
            header('Location: index.php?page=competitions');
            exit;
        }

        // Check if competition is open for submissions
        if ($competition['status'] != 'active') {
            $_SESSION['error'] = "This competition is not currently accepting submissions";
            header('Location: index.php?page=competitions&action=view&id=' . $competition_id);
            exit;
        }

        // Check if user has already submitted a recipe
        $recipe = new Recipe($this->conn);
        if ($recipe->already_submitted($competition_id, $_SESSION['user_id'])) {
            $_SESSION['error'] = "You have already submitted a recipe to this competition";
            header('Location: index.php?page=competitions&action=view&id=' . $competition_id);
            exit;
        }

        // Get user's recipes that aren't already in competitions
        $user_recipes = $recipe->get_by_user($_SESSION['user_id']);

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['recipe_id'])) {
            $recipe_id = $_POST['recipe_id'];
            
            if ($recipe->submit_to_competition($competition_id, $recipe_id)) {
                $_SESSION['success'] = "Recipe submitted successfully to the competition";
                header('Location: index.php?page=competitions&action=view&id=' . $competition_id);
                exit;
            } else {
                $_SESSION['error'] = "Failed to submit recipe to competition";
            }
        }

        header('Location: index.php?page=competitions&action=view&id=' . $competition_id);
    }

    private function withdraw_recipe() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "You must be logged in to withdraw a recipe";
            header('Location: index.php?page=auth&action=login');
            exit;
        }

        // Check for valid competition ID
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_SESSION['error'] = "Invalid competition ID";
            header('Location: index.php?page=competitions');
            exit;
        }

        $competition_id = $_GET['id'];
        $competition = $this->competition->get_by_id($competition_id);
        
        if (!$competition) {
            $_SESSION['error'] = "Competition not found";
            header('Location: index.php?page=competitions');
            exit;
        }

        // Check if competition is still in submission phase
        if ($competition['status'] != 'active') {
            $_SESSION['error'] = "You can only withdraw recipes from active competitions";
            header('Location: index.php?page=competitions&action=view&id=' . $competition_id);
            exit;
        }

        // Remove the recipe from the competition
        $recipe = new Recipe($this->conn);
        if ($recipe->withdraw_from_competition($competition_id, $_SESSION['user_id'])) {
            $_SESSION['success'] = "Your recipe has been withdrawn from the competition";
        } else {
            $_SESSION['error'] = "Failed to withdraw your recipe from the competition";
        }
        
        header('Location: index.php?page=competitions&action=view&id=' . $competition_id);
        exit;
    }
    
    private function search_competitions() {
        $search_term = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? 'all';
        
        $this->competition->update_status();
        
        if (empty($search_term)) {
            // If no search term, redirect back to the listing
            header('Location: index.php?page=competitions');
            exit;
        }
        
        $competitions = $this->competition->search($search_term, $status);
        
        // Make logged_in status available to the view
        global $logged_in;
        
        include 'views/competitions/index.php';
    }
}
?>
