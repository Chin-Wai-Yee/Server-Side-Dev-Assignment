<?php
class Vote {
    private $conn;
    private $table = 'votes';
    
    // Vote properties
    public $id;
    public $recipe_id;  // This refers to competition_recipes.id, not recipes.recipe_id
    public $user_id;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create vote
    public function create() {
        // Clean data
        $this->recipe_id = htmlspecialchars(strip_tags($this->recipe_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        
        $query = "INSERT INTO {$this->table} (recipe_id, user_id) VALUES (?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $this->recipe_id, $this->user_id);
        
        // Execute query
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    // Remove vote (unlike)
    public function remove() {
        // Clean data
        $this->recipe_id = htmlspecialchars(strip_tags($this->recipe_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        
        $query = "DELETE FROM {$this->table} WHERE recipe_id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $this->recipe_id, $this->user_id);
        
        // Execute query
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    // Check if user already voted for this recipe
    public function already_voted() {
        $query = "SELECT id FROM {$this->table} WHERE recipe_id = ? AND user_id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $this->recipe_id, $this->user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        
        $stmt->close();
        return $exists;
    }
    
    // Get user votes
    public function get_user_votes() {
        $query = "SELECT recipe_id FROM {$this->table} WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $votes = [];
        while ($row = $result->fetch_assoc()) {
            $votes[] = $row['recipe_id'];
        }
        
        $stmt->close();
        return $votes;
    }
    
    // Count votes for a competition recipe
    public function count_votes($comp_recipe_id) {
        $query = "SELECT COUNT(*) as vote_count FROM {$this->table} WHERE recipe_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $comp_recipe_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $stmt->close();
        return $row['vote_count'];
    }
    
    // Get top voted recipes for a competition
    public function get_top_voted($competition_id, $limit = 3) {
        $query = "SELECT cr.recipe_id, r.title, r.image_path, u.username, COUNT(v.id) as vote_count
                  FROM competition_recipes cr
                  JOIN recipes r ON cr.recipe_id = r.recipe_id
                  LEFT JOIN users u ON r.user_id = u.user_id
                  LEFT JOIN votes v ON cr.id = v.recipe_id
                  WHERE cr.competition_id = ?
                  GROUP BY cr.id
                  ORDER BY vote_count DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $competition_id, $limit);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $recipes = [];
        while ($row = $result->fetch_assoc()) {
            $recipes[] = $row;
        }
        
        $stmt->close();
        return $recipes;
    }
    
    // Get the winner of a competition
    public function get_competition_winner($competition_id) {
        $query = "SELECT cr.id as comp_recipe_id, cr.recipe_id, r.title as recipe_title, 
                 r.image_path, u.username as name, u.user_id,
                 COUNT(v.id) as vote_count
                 FROM competition_recipes cr
                 JOIN recipes r ON cr.recipe_id = r.recipe_id
                 LEFT JOIN users u ON r.user_id = u.user_id
                 LEFT JOIN votes v ON cr.id = v.recipe_id
                 WHERE cr.competition_id = ?
                 GROUP BY cr.id
                 ORDER BY vote_count DESC
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $competition_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        $stmt->close();
        return null;
    }
}
?>
