<?php
// Start the session to access session variables
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe and Culinary</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include('header.php'); ?>

    <main>
        <?php include('home.php'); ?>
    </main>

    <?php include('footer.php'); ?>
</body>
</html>
