<?php
// Database connection
$servername = "localhost";
$username = "root";  // Default username for localhost
$password = "";      // Default password for localhost
$dbname = "recipe_culinary";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
