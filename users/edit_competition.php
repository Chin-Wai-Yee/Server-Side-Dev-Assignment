<?php
session_start();
require '../database.php';  // DB connection
require '../users/require_login.php';

// Validate competition ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid competition ID.";
    header("Location: manage_competition.php");
    exit;
}

$competition_id = intval($_GET['id']);

// Retrieve competition data from the database
$sql = "SELECT * FROM competitions WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $competition_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Competition not found.";
    header("Location: manage_competition.php");
    exit;
}

$competition = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    
    // Handle image upload
    $image_path = $competition['image']; // Keep existing image by default
    error_log("Existing image path: " . $image_path); // Debugging line
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        error_log("Image upload detected"); // Debugging line
        // Check if the upload directory exists, create if not
        $upload_dir = 'database/competition_images/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Process the uploaded file
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // File validation
        $extensions = array("jpeg", "jpg", "png", "gif", "webp");
        
        if (in_array($file_ext, $extensions) && $file_size < 2097152) { // 2MB max
            $new_file_name = uniqid('comp_') . '.' . $file_ext;
            $upload_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // If there's an existing image, remove it 
                if (!empty($competition['image']) && file_exists($competition['image'])) {
                    unlink($competition['image']);
                }
                $image_path = $upload_path;
            } else {
                $_SESSION['error'] = "Error uploading image";
                include 'edit_competition.php';
                return;
            }
        } else {
            if (!in_array($file_ext, $extensions)) {
                $_SESSION['error'] = "Invalid file extension. Allowed types: jpeg, jpg, png, gif, webp";
            } else {
                $_SESSION['error'] = "File size exceeds 2MB limit";
            }
            include 'edit_competition.php';
            return;
        }
    }
    
    // Combine date and time inputs
    $start_date = $_POST['start_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '00:00';
    $start_date_time = $start_date . ' ' . $start_time;
    
    $end_date = $_POST['end_date'] ?? '';
    $end_time = $_POST['end_time'] ?? '23:59';
    $end_date_time = $end_date . ' ' . $end_time;
    
    $voting_end_date = $_POST['voting_end_date'] ?? '';
    $voting_end_time = $_POST['voting_end_time'] ?? '23:59';
    $voting_end_date_time = $voting_end_date . ' ' . $voting_end_time;
    
    $status = $_POST['status'] ?? $competition['status']; // Use existing status if not provided

    // Validate dates: start_date < end_date < voting_end_date
    $start_timestamp = strtotime($start_date_time);
    $end_timestamp = strtotime($end_date_time);
    $voting_end_timestamp = strtotime($voting_end_date_time);
    
    if ($start_timestamp >= $end_timestamp) {
        $_SESSION['error'] = "Start date must be before end date";
        include 'edit_competition.php';
        return;
    }
    
    if ($end_timestamp >= $voting_end_timestamp) {
        $_SESSION['error'] = "End date must be before voting end date";
        include 'edit_competition.php';
        return;
    }

    error_log("Image path: " . $image_path); // Debugging line

    if (update($_GET['id'], $title, $description, $start_date_time, $end_date_time, $voting_end_date_time, $status, $image_path)) {
        $_SESSION['success'] = "Competition updated successfully";
        header('Location: manage_competition.php');
        return;
    } else {
        $_SESSION['error'] = "Failed to update competition";
    }
}

function update($id, $title, $description, $start_date, $end_date, $voting_end_date, $status, $image) {
    global $conn; // Use the global database connection

    $query = "UPDATE competitions 
              SET title = ?, 
                  description = ?, 
                  start_date = ?, 
                  end_date = ?, 
                  voting_end_date = ?, 
                  status = ?, 
                  image = ? 
              WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssi", $title, $description, $start_date, $end_date, $voting_end_date, $status, $image, $id);

    return $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Competition</title>
    <link rel="stylesheet" href="../styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: url('../Image/background6.png');
            background-size: cover;  
            background-position: center;  
            background-attachment: fixed;  
            background-repeat: no-repeat; 
            color: lightyellow;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 800px;
        }

        .card-header {
            background-color: rgba(0, 123, 255, 0.8);
            color: white;
        }

        .container {
            padding: 20px;
            margin: auto;
        }

        .alert {
            padding: 10px;
            margin-top: 20px;
            border-radius: 4px;
            font-size: 14px;
        }

        .feedback-success {
            background-color: #d4edda;
            color: #155724;
        }

        .feedback-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .form-floating {
            margin-bottom: 15px;
            display: block;
            width: 100%;
        }

        .form-floating input, .form-floating textarea, .form-floating select {
            padding: 10px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-floating input:focus, .form-floating textarea:focus, .form-floating select:focus {
            border-color: #8E2C2B;
            outline: none;
        }

        .form-floating label {
            font-size: 14px;
            color: #777;
        }

        button[type="submit"], .btn-secondary {
            background-color: #8E2C2B;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover, .btn-secondary:hover {
            background-color: #7a241f;
        }

        .form-text {
            font-size: 12px;
            color: #6c757d;
        }

        .img-thumbnail {
            max-width: 150px;
            height: auto;
            border-radius: 4px;
        }

        input[type="file"] {
            padding: 5px;
            margin-top: 10px;
            font-size: 14px;
            display: block;
            width: 100%;
        }

        footer {
            margin-top: auto;
        }
    </style>
</head>
<body>
<?php include '../admin_header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-light text-black">
                    <h2 class="mb-0"><i class="fas fa-edit"></i> Edit Competition</h2>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert feedback-success">
                            <?= $_SESSION['success']; ?>
                            <?php unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert feedback-error">
                            <?= $_SESSION['error']; ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="edit_competition.php?id=<?= $competition['id'] ?>" enctype="multipart/form-data">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($competition['title']) ?>" required>
                            <label for="title">Title</label>
                        </div>

                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars($competition['description']) ?></textarea>
                            <label for="description">Description</label>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Competition Image (Optional)</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">Upload an image to represent this competition. Max size: 2MB.</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= date('Y-m-d', strtotime($competition['start_date'])) ?>" required>
                                    <label for="start_date">Start Date</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating">
                                    <input type="time" class="form-control" id="start_time" name="start_time" value="<?= date('H:i', strtotime($competition['start_date'])) ?>" required>
                                    <label for="start_time">Start Time</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= date('Y-m-d', strtotime($competition['end_date'])) ?>" required>
                                    <label for="end_date">Submit Recipe End Date</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating">
                                    <input type="time" class="form-control" id="end_time" name="end_time" value="<?= date('H:i', strtotime($competition['end_date'])) ?>" required>
                                    <label for="end_time">Submit Recipe End Time</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="voting_end_date" name="voting_end_date" value="<?= date('Y-m-d', strtotime($competition['voting_end_date'])) ?>" required>
                                    <label for="voting_end_date">Voting End Date</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating">
                                    <input type="time" class="form-control" id="voting_end_time" name="voting_end_time" value="<?= date('H:i', strtotime($competition['voting_end_date'])) ?>" required>
                                    <label for="voting_end_time">Voting End Time</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="../users/manage_competition.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Competition</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>

</body>
</html>
