<?php
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../includes/MealAPI.php';

// Initialize MealAPI
$mealAPI = new MealAPI($conn);

// Get limit from query parameter, default to 10
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

// Get popular searches
$popularSearches = $mealAPI->getPopularSearches($limit);

// Return JSON response
echo json_encode([
    'searches' => $popularSearches
]);
?> 