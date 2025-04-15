<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set the base path for XAMPP
$base_path = 'recipe culinary';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Planner</title>
    <link rel="stylesheet" href="/<?= $base_path ?>/styles.css">
</head>
<body>
    <main>
        <?php require_once __DIR__ . '/../header.php'; ?>