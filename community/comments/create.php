<?php
/*
session_start();
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. Validate CSRF first
        if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
            throw new Exception("Invalid CSRF token");
        }

        // 2. Regenerate CSRF token
        generate_csrf_token();

        // 3. Validate user session
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("You must be logged in to comment");
        }

        // 4. Validate required fields
        if (!isset($_POST['discussion_id']) || !isset($_POST['content'])) {
            throw new Exception("Missing required fields");
        }

        // 5. Process data
        $discussion_id = (int)$_POST['discussion_id'];
        $parent_comment_id = !empty($_POST['parent_comment_id']) 
            ? (int)$_POST['parent_comment_id'] 
            : null;
        $content = trim($_POST['content']);

        if (empty($content)) {
            throw new Exception("Comment cannot be empty");
        }

        // 6. Handle NULL parent_comment_id properly
        if ($parent_comment_id === null) {
            $stmt = $conn->prepare("INSERT INTO comments 
                                  (user_id, discussion_id, content)
                                  VALUES (?, ?, ?)");
            $stmt->bind_param("iis", 
                $_SESSION['user_id'], 
                $discussion_id, 
                $content
            );
        } else {
            $stmt = $conn->prepare("INSERT INTO comments 
                                  (user_id, discussion_id, parent_comment_id, content)
                                  VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", 
                $_SESSION['user_id'], 
                $discussion_id, 
                $parent_comment_id, 
                $content
            );
        }

        // 7. Execute and check results
        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }

        // 8. Redirect back to discussion
        header("Location: ../discussions/view.php?id=$discussion_id");
        exit();

    } catch (Exception $e) {
        // 9. Handle errors with proper feedback
        $_SESSION['error'] = $e->getMessage();
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
    
}
*/

/*
// Enable error reporting at the TOP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../includes/csrf.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // Validate CSRF first
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        throw new Exception("Invalid CSRF token - please try again");
    }

    // Validate user session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("You must be logged in to comment");
    }

    // Validate required fields
    if (!isset($_POST['discussion_id']) || !isset($_POST['content'])) {
        throw new Exception("Missing required fields");
    }

    // Process data
    $discussion_id = (int)$_POST['discussion_id'];
    $content = trim(htmlspecialchars($_POST['content']));
    $parent_comment_id = isset($_POST['parent_comment_id']) ? (int)$_POST['parent_comment_id'] : null;

    if (empty($content)) {
        throw new Exception("Comment cannot be empty");
    }

    // Insert comment
    if ($parent_comment_id) {
        $stmt = $conn->prepare("INSERT INTO comments 
                              (user_id, discussion_id, parent_comment_id, content)
                              VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $_SESSION['user_id'], $discussion_id, $parent_comment_id, $content);
    } else {
        $stmt = $conn->prepare("INSERT INTO comments 
                              (user_id, discussion_id, content)
                              VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $_SESSION['user_id'], $discussion_id, $content);
    }

    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }

    // Regenerate CSRF token after successful submission
    generate_csrf_token();

    // Redirect back to discussion
    header("Location: ../discussions/view.php?id=$discussion_id");
    exit();

} catch (Exception $e) {
    // Generate new CSRF token for retry
    generate_csrf_token();
    
    // Store error message
    $_SESSION['error'] = $e->getMessage();
    
    // Store form data for preservation
    $_SESSION['form_data'] = $_POST;
    
    // Redirect back
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
    */


// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../includes/csrf.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        throw new Exception("Security error: Please refresh the page and try again");
    }

    // Validate user session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("You must be logged in to comment");
    }

    // Validate required fields
    if (!isset($_POST['discussion_id']) || empty(trim($_POST['content']))) {
        throw new Exception("Comment cannot be empty");
    }

    // Process data
    $discussion_id = (int)$_POST['discussion_id'];
    $content = htmlspecialchars(trim($_POST['content']));
    $parent_comment_id = isset($_POST['parent_comment_id']) ? (int)$_POST['parent_comment_id'] : null;

    // Insert comment
    $stmt = $conn->prepare($parent_comment_id 
        ? "INSERT INTO comments (user_id, discussion_id, parent_comment_id, content) VALUES (?, ?, ?, ?)"
        : "INSERT INTO comments (user_id, discussion_id, content) VALUES (?, ?, ?)"
    );
    
    $params = $parent_comment_id 
        ? [$_SESSION['user_id'], $discussion_id, $parent_comment_id, $content]
        : [$_SESSION['user_id'], $discussion_id, $content];

    $stmt->bind_param($parent_comment_id ? "iiis" : "iis", ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }

    // Regenerate token AFTER successful submission
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    // Redirect back
    header("Location: ../discussions/view.php?id=$discussion_id");
    exit();

} catch (Exception $e) {
    // Preserve form data
    $_SESSION['form_data'] = $_POST;
    $_SESSION['error'] = $e->getMessage();
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

?>
