<?php
require_once dirname(__DIR__) . '/database.php';

class User {
    private $conn;
    private $table = 'users';

    // User properties
    public $user_id;
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
                "INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())"
            );
            $stmt->bind_param("sss", $username, $email, $password);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }

    public function login() {
        $stmt = $this->conn->prepare(
            "SELECT user_id, username, password FROM {$this->table} WHERE email = ? LIMIT 1"
        );
        $stmt->bind_param("s", $this->email);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result && $row = $result->fetch_assoc()) {
            // Debug log
            error_log("Login: user found for email " . $this->email);
            
            if (password_verify($this->password, $row['password'])) {
                $this->user_id = $row['user_id'];
                $this->username = $row['username'];
                return true;
            } else {
                error_log("Login: password mismatch for email " . $this->email);
            }
        } else {
            error_log("Login: no user found for email " . $this->email);
        }
    
        return false;
    }

    public function read_single() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE user_id = ? LIMIT 1';

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $this->user_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->profile_image = $row['profile_image'];
            $this->bio = $row['bio'];
            $this->created_at = $row['created_at'];
            return true;
        }

        return false;
    }

    public function getUserByUsername($username) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE username = ? LIMIT 1';

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            $this->user_id = $row['user_id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->profile_image = $row['profile_image'];
            $this->bio = $row['bio'];
            $this->created_at = $row['created_at'];
            return true;
        }

        return false;
    }

    public function getUserByEmail($email) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE email = ? LIMIT 1';

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            $this->user_id = $row['user_id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->profile_image = $row['profile_image'];
            $this->bio = $row['bio'];
            $this->created_at = $row['created_at'];
            return true;
        }

        return false;
    }

    public function update_profile($username, $email, $bio = null) {
        try {
            $query = 'UPDATE ' . $this->table . ' 
                      SET username = ?, email = ?, bio = ? 
                      WHERE user_id = ?';

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('sssi', $username, $email, $bio, $this->user_id);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating user profile: " . $e->getMessage());
            return false;
        }
        
    }

    public function update_profile_image($image_path) {
        try {
            $query = 'UPDATE ' . $this->table . ' 
                      SET profile_image = ? 
                      WHERE user_id = ?';

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('si', $image_path, $this->user_id);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating profile image: " . $e->getMessage());
            return false;
        }
    }

    public function update_password($new_password) {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $query = 'UPDATE ' . $this->table . ' 
                      SET password = ? 
                      WHERE user_id = ?';

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('si', $hashed_password, $this->user_id);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }

    public function verify_password($password) {
        $query = 'SELECT password FROM ' . $this->table . ' WHERE user_id = ?';

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $this->user_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row && password_verify($password, $row['password'])) {
            return true;
        }

        return false;
    }
}
?>

