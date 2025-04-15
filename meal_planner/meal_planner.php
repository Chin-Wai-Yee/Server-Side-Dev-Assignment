<?php
require_once __DIR__ . '/../database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Set content type to JSON
header('Content-Type: application/json');

// Handle different actions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    switch ($action) {
        case 'get_plan':
            getMealPlan($user_id);
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
        exit;
    }
    
    $action = isset($data['action']) ? $data['action'] : '';
    
    switch ($action) {
        case 'add_entry':
            addMealEntry($user_id, $data);
            break;
        case 'save_plan':
            saveMealPlan($user_id, $data);
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
}

function saveMealPlan($user_id, $data) {
    global $conn;
    
    try {
        if (!isset($data['plan']) || !is_array($data['plan'])) {
            error_log("Invalid plan data received: " . print_r($data, true));
            echo json_encode(['success' => false, 'error' => 'Invalid plan data']);
            return;
        }

        $plan = $data['plan'];
        if (empty($plan)) {
            error_log("Empty plan data received");
            echo json_encode(['success' => false, 'error' => 'Plan data is empty']);
            return;
        }

        $dates = array_keys($plan);
        if (empty($dates)) {
            error_log("No dates found in plan data");
            echo json_encode(['success' => false, 'error' => 'No dates found in plan']);
            return;
        }

        $start_date = min($dates);
        $end_date = max($dates);
        
        // Get or create plan
        $stmt = $conn->prepare("
            SELECT plan_id FROM meal_plans 
            WHERE user_id = ? 
            AND start_date <= ? 
            AND end_date >= ?
            LIMIT 1
        ");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            echo json_encode(['success' => false, 'error' => 'Database prepare failed']);
            return;
        }

        $stmt->bind_param("iss", $user_id, $end_date, $start_date);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            echo json_encode(['success' => false, 'error' => 'Database execute failed']);
            return;
        }

        $result = $stmt->get_result();
        $plan_id = null;
        
        if ($result->num_rows === 0) {
            // Create new plan
            $stmt = $conn->prepare("
                INSERT INTO meal_plans (user_id, start_date, end_date)
                VALUES (?, ?, ?)
            ");
            if (!$stmt) {
                error_log("Prepare failed for insert: " . $conn->error);
                echo json_encode(['success' => false, 'error' => 'Database prepare failed']);
                return;
            }

            $stmt->bind_param("iss", $user_id, $start_date, $end_date);
            if (!$stmt->execute()) {
                error_log("Execute failed for insert: " . $stmt->error);
                echo json_encode(['success' => false, 'error' => 'Database execute failed']);
                return;
            }
            $plan_id = $conn->insert_id;
        } else {
            $row = $result->fetch_assoc();
            $plan_id = $row['plan_id'];
        }
        
        if (!$plan_id) {
            error_log("Failed to get or create plan_id");
            echo json_encode(['success' => false, 'error' => 'Failed to create or get plan']);
            return;
        }
        
        // Delete existing entries
        $stmt = $conn->prepare("DELETE FROM meal_entries WHERE plan_id = ?");
        if (!$stmt) {
            error_log("Prepare failed for delete: " . $conn->error);
            echo json_encode(['success' => false, 'error' => 'Database prepare failed']);
            return;
        }

        $stmt->bind_param("i", $plan_id);
        if (!$stmt->execute()) {
            error_log("Execute failed for delete: " . $stmt->error);
            echo json_encode(['success' => false, 'error' => 'Database execute failed']);
            return;
        }
        
        // Add new entries
        foreach ($plan as $date => $meals) {
            foreach ($meals as $type => $meal) {
                if (isset($meal['recipe_id'])) {
                    $stmt = $conn->prepare("
                        INSERT INTO meal_entries (plan_id, meal_date, meal_type, recipe_id)
                        VALUES (?, ?, ?, ?)
                    ");
                    if (!$stmt) {
                        error_log("Prepare failed for insert entry: " . $conn->error);
                        continue;
                    }

                    $stmt->bind_param("issi", $plan_id, $date, $type, $meal['recipe_id']);
                    if (!$stmt->execute()) {
                        error_log("Execute failed for insert entry: " . $stmt->error);
                        continue;
                    }
                }
            }
        }
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        error_log("Error saving meal plan: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'An error occurred while saving the meal plan']);
    }
}

function getMealPlan($user_id) {
    global $conn;
    
    try {
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
        $end_date = date('Y-m-d', strtotime($start_date . ' +6 days'));
        
        // Get or create meal plan
        $stmt = $conn->prepare("
            SELECT plan_id FROM meal_plans 
            WHERE user_id = ? 
            AND start_date <= ? 
            AND end_date >= ?
            LIMIT 1
        ");
        $stmt->bind_param("iss", $user_id, $end_date, $start_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Create new plan
            $stmt = $conn->prepare("
                INSERT INTO meal_plans (user_id, start_date, end_date)
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("iss", $user_id, $start_date, $end_date);
            $stmt->execute();
            $plan_id = $conn->insert_id;
        } else {
            $row = $result->fetch_assoc();
            $plan_id = $row['plan_id'];
        }
        
        // Get meal entries
        $stmt = $conn->prepare("
            SELECT me.meal_date, me.meal_type, me.recipe_id, me.custom_meal_name, r.title as recipe_title
            FROM meal_entries me
            LEFT JOIN recipes r ON me.recipe_id = r.recipe_id
            WHERE me.plan_id = ?
            ORDER BY me.meal_date, me.meal_type
        ");
        $stmt->bind_param("i", $plan_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $plan = [];
        while ($row = $result->fetch_assoc()) {
            $date = $row['meal_date'];
            $type = $row['meal_type'];
            
            if (!isset($plan[$date])) {
                $plan[$date] = [];
            }
            
            $plan[$date][$type] = [
                'recipe_id' => $row['recipe_id'],
                'recipe_title' => $row['recipe_title'],
                'custom_meal_name' => $row['custom_meal_name']
            ];
        }
        
        echo json_encode(['success' => true, 'plan' => $plan]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to get meal plan']);
    }
}

function addMealEntry($user_id, $data) {
    global $conn;
    
    try {
        $meal_date = $data['meal_date'];
        $meal_type = $data['meal_type'];
        $recipe_id = $data['recipe_id'];
        
        // Get plan_id
        $stmt = $conn->prepare("
            SELECT plan_id FROM meal_plans 
            WHERE user_id = ? 
            AND start_date <= ? 
            AND end_date >= ?
            LIMIT 1
        ");
        $stmt->bind_param("iss", $user_id, $meal_date, $meal_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Create new plan
            $start_date = $meal_date;
            $end_date = date('Y-m-d', strtotime($meal_date . ' +6 days'));
            
            $stmt = $conn->prepare("
                INSERT INTO meal_plans (user_id, start_date, end_date)
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("iss", $user_id, $start_date, $end_date);
            $stmt->execute();
            $plan_id = $conn->insert_id;
        } else {
            $row = $result->fetch_assoc();
            $plan_id = $row['plan_id'];
        }
        
        // Add or update meal entry
        $stmt = $conn->prepare("
            INSERT INTO meal_entries (plan_id, meal_date, meal_type, recipe_id)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE recipe_id = VALUES(recipe_id)
        ");
        $stmt->bind_param("issi", $plan_id, $meal_date, $meal_type, $recipe_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to add meal entry']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to add meal entry']);
    }
}
?> 