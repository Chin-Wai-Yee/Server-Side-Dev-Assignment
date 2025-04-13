<?php
class Competition {
    private $conn;
    private $table = 'competitions';
    
    // Competition properties
    public $id;
    public $title;
    public $description;
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
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->end_date = htmlspecialchars(strip_tags($this->end_date));
        $this->voting_end_date = htmlspecialchars(strip_tags($this->voting_end_date));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->created_by = htmlspecialchars(strip_tags($this->created_by)); // Clean created_by
        
        // Prepare query
        $query = "INSERT INTO {$this->table} (title, description, start_date, end_date, voting_end_date, status, created_by) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("sssssss", 
            $this->title, 
            $this->description, 
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
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->voting_end_date = $row['voting_end_date'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            
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
    
    // Update competition
    public function update($id, $title, $description, $start_date, $end_date, $voting_end_date, $status) {
        // Clean data
        $title = htmlspecialchars(strip_tags($title));
        $description = htmlspecialchars(strip_tags($description));
        $start_date = htmlspecialchars(strip_tags($start_date));
        $end_date = htmlspecialchars(strip_tags($end_date));
        $voting_end_date = htmlspecialchars(strip_tags($voting_end_date));
        $status = htmlspecialchars(strip_tags($status));
        $id = htmlspecialchars(strip_tags($id));
        
        // Prepare query
        $query = "UPDATE {$this->table} 
                  SET title = ?,
                      description = ?,
                      start_date = ?,
                      end_date = ?,
                      voting_end_date = ?,
                      status = ?
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ssssssi", 
            $title, 
            $description, 
            $start_date, 
            $end_date, 
            $voting_end_date, 
            $status,
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
                      ELSE 'closed'
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
                  (SELECT COUNT(*) FROM competition_recipes WHERE competition_id = c.id) as recipe_count,
                  (SELECT r.title FROM competition_recipes cr 
                   JOIN recipes r ON cr.recipe_id = r.recipe_id
                   JOIN votes v ON cr.id = v.recipe_id
                   WHERE cr.competition_id = c.id
                   GROUP BY cr.id
                   ORDER BY COUNT(v.id) DESC
                   LIMIT 1) as winner_recipe_title,
                  (SELECT u.username FROM competition_recipes cr 
                   JOIN recipes r ON cr.recipe_id = r.recipe_id
                   JOIN users u ON r.user_id = u.id
                   JOIN votes v ON cr.id = v.recipe_id
                   WHERE cr.competition_id = c.id
                   GROUP BY cr.id
                   ORDER BY COUNT(v.id) DESC
                   LIMIT 1) as winner_name,
                  (SELECT cr.recipe_id FROM competition_recipes cr 
                   JOIN votes v ON cr.id = v.recipe_id
                   WHERE cr.competition_id = c.id
                   GROUP BY cr.id
                   ORDER BY COUNT(v.id) DESC
                   LIMIT 1) as winner_recipe_id
                  FROM {$this->table} c 
                  WHERE c.status = 'closed' 
                  ORDER BY c.voting_end_date DESC";
        
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
}
?>
