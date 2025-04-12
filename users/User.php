<?php
require_once dirname(__DIR__) . '/database.php';

class User {
    private $conn;
    private $table = 'users';
    
    // User properties
    public $id;
    public $username;
    public $email;
    public $password;
    public $profile_image;
    public $bio;
    public $created_at;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function register($username, $email, $password) {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO users " .
                "(username, email, password, created_at) " .
                "VALUES (?, ?, ?, NOW())"
            );
            $stmt->bind_param("sss", $username, $email, $password);
            return $stmt->execute();
        } catch (Exception $e) {
            // Log error
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }
    
    // Login user
    public function login() {
        $query = 'SELECT id, username, email, password 
                  FROM ' . $this->table . ' 
                  WHERE email = ? 
                  LIMIT 0,1';
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $this->email);
        $stmt->execute();
        
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->id = $row['id'];
            $this->username = $row['username'];
            
            // Verify password
            if(password_verify($this->password, $row['password'])) {
                return true;
            }
        }
        
        return false;
    }
    
    // Get user by ID
    public function read_single() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = ? LIMIT 0,1';
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $this->id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if($row) {
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->profile_image = $row['profile_image'];
            $this->bio = $row['bio'];
            $this->created_at = $row['created_at'];
            return true;
        }
        
        return false;
    }

    // Get user by username
    public function getUserByUsername($username) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE username = ? LIMIT 0,1';
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->profile_image = $row['profile_image'];
            $this->bio = $row['bio'];
            $this->created_at = $row['created_at'];
            return true;
        }
        
        return false;
    }
    
    // Get user by email
    public function getUserByEmail($email) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE email = ? LIMIT 0,1';
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->profile_image = $row['profile_image'];
            $this->bio = $row['bio'];
            $this->created_at = $row['created_at'];
            return true;
        }
        
        return false;
    }

    // Update user profile
    public function update_profile($username, $email, $bio = null) {
        try {
            $query = 'UPDATE ' . $this->table . ' 
                      SET username = ?, email = ?, bio = ? 
                      WHERE id = ?';
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('sssi', $username, $email, $bio, $this->id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating user profile: " . $e->getMessage());
            return false;
        }
    }
    
    // Update profile image
    public function update_profile_image($image_path) {
        try {
            $query = 'UPDATE ' . $this->table . ' 
                      SET profile_image = ? 
                      WHERE id = ?';
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('si', $image_path, $this->id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating profile image: " . $e->getMessage());
            return false;
        }
    }
    
    // Update password
    public function update_password($new_password) {
        try {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $query = 'UPDATE ' . $this->table . ' 
                      SET password = ? 
                      WHERE id = ?';
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('si', $hashed_password, $this->id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }
    
    public function verify_password($password) {
        $query = 'SELECT password FROM ' . $this->table . ' WHERE id = ?';
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $this->id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if($row && password_verify($password, $row['password'])) {
            return true;
        }
        
        return false;
    }
}
?>
