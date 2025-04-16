<?php
class Competition {
    private $conn;
    private $table = 'competitions';
    private $comp_recipe_table = 'competition_recipes';
    private $recipe_table = 'recipes';
    private $vote_table = 'votes';
    
    // Competition properties
    public $id;
    public $title;
    public $description;
    public $image;
    public $start_date;
    public $end_date;
    public $voting_end_date;
    public $status;
    public $created_at;
    public $created_by; // Added created_by property
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create competition
    public function create() {
        // Clean data
        $this->title = strip_tags($this->title);
        $this->description = strip_tags($this->description);
        $this->image = htmlspecialchars(strip_tags($this->image)); // Clean image
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->end_date = htmlspecialchars(strip_tags($this->end_date));
        $this->voting_end_date = htmlspecialchars(strip_tags($this->voting_end_date));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->created_by = htmlspecialchars(strip_tags($this->created_by)); // Clean created_by
        
        // Prepare query
        $query = "INSERT INTO {$this->table} (title, description, image, start_date, end_date, voting_end_date, status, created_by) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ssssssss", 
            $this->title, 
            $this->description, 
            $this->image,
            $this->start_date, 
            $this->end_date, 
            $this->voting_end_date, 
            $this->status,
            $this->created_by // Bind created_by
        );
        
        // Execute query
        if($stmt->execute()) {
            $stmt->close();
            return true;
        }
        
        $stmt->close();
        return false;
    }
    
    // Get all competitions
    public function read() {
        $query = "SELECT * FROM {$this->table} ORDER BY start_date DESC";
        
        $result = $this->conn->query($query);
        
        return $result;
    }
    
    // Get single competition
    public function read_single() {
        $query = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $this->id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            $this->id = $row['id'];
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->image = $row['image'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->voting_end_date = $row['voting_end_date'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->created_by = $row['created_by'];
            
            $stmt->close();
            return true;
        }
        
        $stmt->close();
        return false;
    }
    
    // Get competition by ID - Method needed by CompetitionController
    public function get_by_id($id) {
        $this->id = $id;
        if($this->read_single()) {
            return [
                'id' => $this->id,
                'title' => $this->title,
                'description' => $this->description,
                'image' => $this->image,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'voting_end_date' => $this->voting_end_date,
                'status' => $this->status,
                'created_by' => $this->created_by,
                'created_at' => $this->created_at
            ];
        }
        return false;
    }

    public function get_recipes($competition_id = null, $order_by = 'vote_count') {
        if ($competition_id == null) {
            $competition_id = $this->id;
        }

        switch ($order_by) {
            case 'vote_count':
                $order_by = 'vote_count';
                break;
            case 'submitted_at':
                $order_by = 'cr.submitted_at';
                break;
            default:
                $order_by = 'vote_count';
        }

        $query = "SELECT r.*, cr.id as comp_recipe_id, u.username, 
                    (SELECT COUNT(*) FROM votes v WHERE v.recipe_id = cr.id) as vote_count
                    FROM {$this->comp_recipe_table} cr
                    JOIN {$this->recipe_table} r ON cr.recipe_id = r.recipe_id
                    LEFT JOIN users u ON r.user_id = u.user_id
                    WHERE cr.competition_id = ?
                    GROUP BY cr.id
                    ORDER BY {$order_by} DESC";
        
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
    
    // Update competition
    public function update($id, $title, $description, $start_date, $end_date, $voting_end_date, $status, $image = '') {
        // Clean data
        $title = strip_tags($title);
        $description = strip_tags($description);
        $start_date = htmlspecialchars(strip_tags($start_date));
        $end_date = htmlspecialchars(strip_tags($end_date));
        $voting_end_date = htmlspecialchars(strip_tags($voting_end_date));
        $status = htmlspecialchars(strip_tags($status));
        $image = htmlspecialchars(strip_tags($image));
        $id = htmlspecialchars(strip_tags($id));
        
        // Prepare query
        $query = "UPDATE {$this->table} 
                  SET title = ?,
                      description = ?,
                      start_date = ?,
                      end_date = ?,
                      voting_end_date = ?,
                      status = ?,
                      image = ?
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("sssssssi", 
            $title, 
            $description, 
            $start_date, 
            $end_date, 
            $voting_end_date, 
            $status,
            $image,
            $id
        );
        
        // Execute query
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    // Delete competition
    public function delete($id) {
        // Clean data
        $id = htmlspecialchars(strip_tags($id));
        
        // Get competition details to find the image
        $this->id = $id;
        if ($this->read_single() && !empty($this->image) && file_exists($this->image)) {
            // Delete the image file
            unlink($this->image);
        }
        
        // Prepare query
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameter
        $stmt->bind_param('i', $id);
        
        // Execute query
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    // Update competition status
    public function update_status() {
        $now = date('Y-m-d H:i:s');
        
        // Update all competitions status based on dates
        $query = "UPDATE {$this->table} 
                  SET status = CASE
                      WHEN ? < start_date THEN 'upcoming'
                      WHEN ? >= start_date AND ? <= end_date THEN 'active'
                      WHEN ? > end_date AND ? <= voting_end_date THEN 'voting'
                      ELSE 'completed'
                  END";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sssss', $now, $now, $now, $now, $now);
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    // Get all competitions
    public function get_all($limit = null) {
        $query = "SELECT c.*, 
                  (SELECT COUNT(*) FROM competition_recipes WHERE competition_id = c.id) as recipe_count
                  FROM {$this->table} c 
                  ORDER BY 
                  CASE 
                    WHEN c.status = 'active' THEN 1 
                    WHEN c.status = 'voting' THEN 2 
                    ELSE 3 
                  END, 
                  c.created_at ASC";

        if ($limit) {
            $query .= " LIMIT ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $limit);
        } else {
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $competitions = [];
        while ($row = $result->fetch_assoc()) {
            $competitions[] = $row;
        }
        
        return $competitions;
    }
    
    // Get competitions by status
    public function get_by_status($status = 'all', $limit = null) {
        if ($status == 'all') {
            return $this->get_all($limit);
        }
        
        $query = "SELECT c.*, 
                  (SELECT COUNT(*) FROM competition_recipes WHERE competition_id = c.id) as recipe_count
                  FROM {$this->table} c 
                  WHERE c.status = ? 
                  ORDER BY c.end_date ASC";
        
        if ($limit) {
            $query .= " LIMIT ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('si', $status, $limit);
        } else {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('s', $status);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $competitions = [];
        while ($row = $result->fetch_assoc()) {
            $competitions[] = $row;
        }
        
        $stmt->close();
        return $competitions;
    }
    
    // Get completed competitions with limit
    public function get_completed($limit = null) {

        $query = "SELECT c.*,
                  (SELECT COUNT(*) FROM {$this->comp_recipe_table} WHERE competition_id = c.id) as recipe_count,
                  (SELECT u.username 
                   FROM {$this->comp_recipe_table} cr
                   JOIN {$this->recipe_table} r ON cr.recipe_id = r.recipe_id
                   JOIN users u ON r.user_id = u.user_id
                   LEFT JOIN {$this->vote_table} v ON cr.id = v.recipe_id
                   WHERE cr.competition_id = c.id
                   GROUP BY cr.id
                   ORDER BY COUNT(v.id) DESC
                   LIMIT 1) as winner_name
                  FROM {$this->table} c 
                  WHERE c.status = 'completed'
                  ORDER BY c.end_date DESC";
        
        if ($limit) {
            $query .= " LIMIT ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $limit);
        } else {
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $competitions = [];
        while ($row = $result->fetch_assoc()) {
            $competitions[] = $row;
        }
        
        $stmt->close();
        return $competitions;
    }
    
    // Search competitions by title or description
    public function search($search_term, $status = 'all') {
        $search_term = '%' . $search_term . '%';
        
        if ($status == 'all') {
            $query = "SELECT c.*, 
                    (SELECT COUNT(*) FROM competition_recipes WHERE competition_id = c.id) as recipe_count
                    FROM {$this->table} c 
                    WHERE (c.title LIKE ? OR c.description LIKE ?)
                    ORDER BY 
                    CASE 
                        WHEN c.status = 'active' THEN 1 
                        WHEN c.status = 'voting' THEN 2 
                        ELSE 3 
                    END, 
                    c.created_at ASC";
                    
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('ss', $search_term, $search_term);
        } else {
            $query = "SELECT c.*, 
                    (SELECT COUNT(*) FROM competition_recipes WHERE competition_id = c.id) as recipe_count
                    FROM {$this->table} c 
                    WHERE (c.title LIKE ? OR c.description LIKE ?) AND c.status = ?
                    ORDER BY c.end_date ASC";
                    
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('sss', $search_term, $search_term, $status);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $competitions = [];
        while ($row = $result->fetch_assoc()) {
            $competitions[] = $row;
        }
        
        $stmt->close();
        return $competitions;
    }
}
?>
