<?php
class Recipe {
    private $conn;
    private $table = 'recipes';
    private $comp_recipe_table = 'competition_recipes';
    
    // Recipe properties
    public $recipe_id;
    public $user_id;
    public $title;
    public $ingredients;
    public $instructions;
    public $cuisine_type;
    public $image_path;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Submit recipe to competition
    public function submit_to_competition($competition_id, $recipe_id) {
        // Clean data
        $competition_id = htmlspecialchars(strip_tags($competition_id));
        $recipe_id = htmlspecialchars(strip_tags($recipe_id));
        
        $query = "INSERT INTO {$this->comp_recipe_table} (competition_id, recipe_id) VALUES (?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $competition_id, $recipe_id);
        
        // Execute query
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    // Get recipes by competition
    public function get_by_competition($competition_id) {
        $query = "SELECT r.*, cr.id as comp_recipe_id, u.username, 
                 (SELECT COUNT(*) FROM votes v WHERE v.recipe_id = cr.id) as vote_count
                 FROM {$this->comp_recipe_table} cr
                 JOIN {$this->table} r ON cr.recipe_id = r.recipe_id
                 LEFT JOIN users u ON r.user_id = u.id
                 WHERE cr.competition_id = ?
                 GROUP BY cr.id
                 ORDER BY vote_count DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $competition_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $recipes = [];
        while ($row = $result->fetch_assoc()) {
            $recipes[] = $row;
        }
        
        $stmt->close();
        return $recipes;
    }
    
    // Get single recipe
    public function get_by_id($recipe_id) {
        $query = "SELECT r.*, u.username 
                 FROM {$this->table} r
                 LEFT JOIN users u ON r.user_id = u.id
                 WHERE r.recipe_id = ?
                 LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $recipe = $result->fetch_assoc();
        
        $stmt->close();
        return $recipe;
    }
    
    // Get recipe from competition
    public function get_competition_recipe($competition_id, $recipe_id) {
        $query = "SELECT r.*, cr.id as comp_recipe_id, u.username,
                 (SELECT COUNT(*) FROM votes v WHERE v.recipe_id = cr.id) as vote_count
                 FROM {$this->comp_recipe_table} cr
                 JOIN {$this->table} r ON cr.recipe_id = r.recipe_id
                 LEFT JOIN users u ON r.user_id = u.id
                 WHERE cr.competition_id = ? AND r.recipe_id = ?
                 LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $competition_id, $recipe_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $recipe = $result->fetch_assoc();
        
        $stmt->close();
        return $recipe;
    }
    
    // Get all recipes
    public function get_all() {
        $query = "SELECT r.*, u.username 
                 FROM {$this->table} r
                 LEFT JOIN users u ON r.user_id = u.id
                 ORDER BY r.recipe_id DESC";
        
        $result = $this->conn->query($query);
        
        $recipes = [];
        while ($row = $result->fetch_assoc()) {
            $recipes[] = $row;
        }
        
        return $recipes;
    }

    // Get all recipes submitted by a specific user
    public function get_by_user($user_id) {
        try {
            $query = "SELECT r.*, u.username, 
                    (
                        SELECT c.title FROM {$this->comp_recipe_table} cr
                        JOIN competitions c ON cr.competition_id = c.id
                        WHERE cr.recipe_id = r.recipe_id
                        LIMIT 1
                    ) as competition_name 
                    FROM {$this->table} r
                    LEFT JOIN users u ON r.user_id = u.id
                    WHERE r.user_id = ?
                    ORDER BY r.recipe_id DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            $recipes = [];
            while ($row = $result->fetch_assoc()) {
                $recipes[] = $row;
            }
            
            $stmt->close();
            return $recipes;
        } catch (Exception $e) {
            // Log error
            error_log("Database error in Recipe::get_by_user: " . $e->getMessage());
            return [];
        }
    }
    
    // Check if user has already submitted a recipe to this competition
    public function already_submitted($competition_id, $user_id) {
        $query = "SELECT cr.id 
                 FROM {$this->comp_recipe_table} cr
                 JOIN {$this->table} r ON cr.recipe_id = r.recipe_id
                 WHERE cr.competition_id = ? AND r.user_id = ?
                 LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $competition_id, $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        
        $stmt->close();
        return $exists;
    }
}
?>
