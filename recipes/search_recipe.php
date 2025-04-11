<?php
// Assuming connection to database is already established
$search = $_POST['search'] ?? ''; // If a search term is provided
$query = "SELECT * FROM recipes WHERE title LIKE '%$search%' OR ingredients LIKE '%$search%'";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    echo "<div>" . $row['title'] . "</div>";
}
?>
