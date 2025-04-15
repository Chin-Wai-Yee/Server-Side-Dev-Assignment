<?php
// Start session at the very top
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../includes/csrf.php';

// Generate or preserve CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF first
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new Exception("Invalid CSRF token!");
        }

        // Regenerate CSRF token after validation
        unset($_SESSION['csrf_token']);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $title = htmlspecialchars($_POST['title']);
        $content = htmlspecialchars($_POST['content']);
        $recipe_id = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : null;
        $user_id = $_SESSION['user_id'] ?? null;
        $media_path = null;

        // Handle file upload
        if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'video/mp4'];
            $file_type = mime_content_type($_FILES['media']['tmp_name']);

            if (in_array($file_type, $allowed_types)) {
                $upload_dir = __DIR__ . '/../../uploads/discussion_media/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $file_name = uniqid() . '_' . basename($_FILES['media']['name']);
                move_uploaded_file($_FILES['media']['tmp_name'], $upload_dir . $file_name);
                $media_path = 'uploads/discussion_media/' . $file_name;
            }
        }

        if (!$user_id) {
            throw new Exception("User not logged in. Cannot post discussion.");
        }

        $stmt = $conn->prepare("INSERT INTO discussions 
                              (user_id, recipe_id, title, content, media_path)
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $user_id, $recipe_id, $title, $content, $media_path);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            header("Location: list.php");
            exit();
        }
    } catch (Exception $e) {
        // Preserve CSRF token for resubmission
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $error = $e->getMessage();
    }
}

// Get recipes
$result = $conn->query("SELECT recipe_id, title FROM recipes");
$recipes = $result->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../header.php';
?>

<link rel="stylesheet" href="/recipe%20culinary/styles.css">

<body class="community-page">
    <div class="community-header">
        <div class="container">
            <h2 class="addrecipe-title">Start New Discussion</h2>
            <?php if (!empty($error)): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="full-width">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <!-- Rest of your form fields remain the same -->
                <div class="form-group">
                    <label>Discussion Title:</label>
                    <input type="text" name="title" class="form-control" placeholder="Enter Title" required>
                </div>

                <div class="form-group">
                    <label>Related Recipe (optional):</label>
                    <select name="recipe_id" class="form-control">
                        <option value="">-- Select Recipe --</option>
                        <?php foreach ($recipes as $recipe): ?>
                            <option value="<?= $recipe['recipe_id'] ?>">
                                <?= htmlspecialchars($recipe['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Discussion Content:</label>
                    <textarea name="content" class="form-control" rows="6" placeholder="Description" required></textarea>
                </div>

                <div class="form-group">
                    <label>Upload Media (optional):</label>
                    <input type="file" name="media" class="form-control-file">
                    <small class="text-muted">Allowed types: JPG, PNG, MP4</small>
                </div>

                <div class="button-container">
                    <button type="submit" class="action-button">
                        <i class="fas fa-comment-medical"></i> Create Discussion
                    </button>
                    <a href="list.php" class="action-button cancel-button">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
<?php include __DIR__ . '/../../footer.php'; ?>