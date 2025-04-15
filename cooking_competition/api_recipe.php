<?php
// API endpoint to fetch recipe details for the competition module

// Initialize session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../database.php';
require_once 'models/Recipe.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods');

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'Invalid request',
    'data' => null
];

// Check if it's a GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if recipe_id is provided
    if (isset($_GET['comp_recipe_id']) && !empty($_GET['comp_recipe_id'])) {
        $comp_recipe_id = intval($_GET['comp_recipe_id']);
        
        // Create Recipe instance
        $recipe = new Recipe($conn);
        
        // Get recipe details from competition recipe ID
        $recipeData = $recipe->get_competition_recipe_details($comp_recipe_id);
        
        if ($recipeData) {
            // Format ingredients and instructions
            if (isset($recipeData['ingredients'])) {
                $recipeData['ingredients_list'] = explode("\n", $recipeData['ingredients']);
            }
            
            if (isset($recipeData['instructions'])) {
                $recipeData['instructions_formatted'] = nl2br($recipeData['instructions']);
            }
            
            $response = [
                'status' => 'success',
                'message' => 'Recipe details retrieved successfully',
                'data' => $recipeData
            ];
        } else {
            $response['message'] = 'Recipe not found';
        }
    } else {
        $response['message'] = 'Recipe ID is required';
    }
}

// Return JSON response
echo json_encode($response);
exit;
?>