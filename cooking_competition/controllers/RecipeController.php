<?php
class RecipeController {
    private $conn;
    private $recipe;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->recipe = new Recipe($conn);
    }

    public function handle_request($action) {
        switch ($action) {
            case 'index':
                $this->list_recipes();
                break;
            case 'submit':
                $this->submit_recipe();
                break;
            case 'view':
                $this->view_recipe();
                break;
            case 'edit':
                $this->edit_recipe();
                break;
            case 'delete':
                $this->delete_recipe();
                break;
            case 'my_recipes':
                $this->my_recipes();
                break;
            default:
                $this->list_recipes();
                break;
        }
    }

    private function list_recipes() {
        // Get competition ID if provided
        $competition_id = isset($_GET['competition_id']) ? $_GET['competition_id'] : null;
        
        if ($competition_id) {
            $competition = new Competition($this->conn);
            $comp_details = $competition->get_by_id($competition_id);
            $recipes = $this->recipe->get_by_competition($competition_id);
            include 'views/recipes/list_by_competition.php';
        } else {
            $recipes = $this->recipe->get_all();
            include 'views/recipes/list.php';
        }
    }

    private function submit_recipe() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "You must be logged in to submit a recipe";
            header('Location: index.php?page=auth&action=login');
            exit;
        }

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

        // Check if competition is open for submissions
        if ($comp_details['status'] != 'active') {
            $_SESSION['error'] = "This competition is not currently accepting submissions";
            header('Location: index.php?page=competitions&action=view&id=' . $competition_id);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = $_POST['title'] ?? '';
            $ingredients = $_POST['ingredients'] ?? '';
            $instructions = $_POST['instructions'] ?? '';
            $user_id = $_SESSION['user_id'];

            // Handle image upload
            $image = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($_FILES['image']['type'], $allowed_types)) {
                    $upload_dir = 'uploads/recipes/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $image = $upload_dir . time() . '_' . $_FILES['image']['name'];
                    move_uploaded_file($_FILES['image']['tmp_name'], $image);
                }
            }

            if ($this->recipe->create($title, $ingredients, $instructions, $image, $user_id, $competition_id)) {
                $_SESSION['success'] = "Recipe submitted successfully";
                header('Location: index.php?page=competitions&action=view&id=' . $competition_id);
                exit;
            } else {
                $_SESSION['error'] = "Failed to submit recipe";
            }
        }
        
        include 'views/recipes/submit.php';
    }

    private function view_recipe() {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_SESSION['error'] = "Invalid recipe ID";
            header('Location: index.php?page=competitions');
            exit;
        }

        $recipe = $this->recipe->get_by_id($_GET['id']);
        if (!$recipe) {
            $_SESSION['error'] = "Recipe not found";
            header('Location: index.php?page=competitions');
            exit;
        }

        include 'views/recipes/view.php';
    }

    private function edit_recipe() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "You must be logged in to edit a recipe";
            header('Location: index.php?page=auth&action=login');
            exit;
        }

        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_SESSION['error'] = "Invalid recipe ID";
            header('Location: index.php?page=recipes&action=my_recipes');
            exit;
        }

        $recipe = $this->recipe->get_by_id($_GET['id']);
        if (!$recipe) {
            $_SESSION['error'] = "Recipe not found";
            header('Location: index.php?page=recipes&action=my_recipes');
            exit;
        }

        // Check if user is the owner of the recipe
        if ($_SESSION['user_id'] != $recipe['user_id'] && $_SESSION['role'] != 'admin') {
            $_SESSION['error'] = "You don't have permission to edit this recipe";
            header('Location: index.php?page=recipes&action=view&id=' . $_GET['id']);
            exit;
        }

        // Get competition details
        $competition = new Competition($this->conn);
        $comp_details = $competition->get_by_id($recipe['competition_id']);

        // Check if competition still allows edits
        if ($comp_details['status'] != 'active' && $_SESSION['role'] != 'admin') {
            $_SESSION['error'] = "This competition is closed for submissions and edits";
            header('Location: index.php?page=recipes&action=view&id=' . $_GET['id']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = $_POST['title'] ?? '';
            $ingredients = $_POST['ingredients'] ?? '';
            $instructions = $_POST['instructions'] ?? '';

            // Handle image upload
            $image = $recipe['image'];
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($_FILES['image']['type'], $allowed_types)) {
                    $upload_dir = 'uploads/recipes/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Delete old image if exists
                    if ($recipe['image'] && file_exists($recipe['image'])) {
                        unlink($recipe['image']);
                    }
                    
                    $image = $upload_dir . time() . '_' . $_FILES['image']['name'];
                    move_uploaded_file($_FILES['image']['tmp_name'], $image);
                }
            }

            if ($this->recipe->update($_GET['id'], $title, $ingredients, $instructions, $image)) {
                $_SESSION['success'] = "Recipe updated successfully";
                header('Location: index.php?page=recipes&action=view&id=' . $_GET['id']);
                exit;
            } else {
                $_SESSION['error'] = "Failed to update recipe";
            }
        }
        
        include 'views/recipes/edit.php';
    }

    private function delete_recipe() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "You must be logged in to delete a recipe";
            header('Location: index.php?page=auth&action=login');
            exit;
        }

        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_SESSION['error'] = "Invalid recipe ID";
            header('Location: index.php?page=recipes&action=my_recipes');
            exit;
        }

        $recipe = $this->recipe->get_by_id($_GET['id']);
        if (!$recipe) {
            $_SESSION['error'] = "Recipe not found";
            header('Location: index.php?page=recipes&action=my_recipes');
            exit;
        }

        // Check if user is the owner of the recipe or admin
        if ($_SESSION['user_id'] != $recipe['user_id'] && $_SESSION['role'] != 'admin') {
            $_SESSION['error'] = "You don't have permission to delete this recipe";
            header('Location: index.php?page=recipes&action=view&id=' . $_GET['id']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->recipe->delete($_GET['id'])) {
                // Delete image file if exists
                if ($recipe['image'] && file_exists($recipe['image'])) {
                    unlink($recipe['image']);
                }
                
                $_SESSION['success'] = "Recipe deleted successfully";
                header('Location: index.php?page=recipes&action=my_recipes');
                exit;
            } else {
                $_SESSION['error'] = "Failed to delete recipe";
                header('Location: index.php?page=recipes&action=view&id=' . $_GET['id']);
                exit;
            }
        }
        
        include 'views/recipes/delete.php';
    }

    private function my_recipes() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "You must be logged in to view your recipes";
            header('Location: index.php?page=auth&action=login');
            exit;
        }

        $recipes = $this->recipe->get_by_user($_SESSION['user_id']);
        include 'views/recipes/my_recipes.php';
    }
}
?>
