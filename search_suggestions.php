<?php
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../includes/MealAPI.php';

// Get keyword from request
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// Return empty result if keyword is too short
if (strlen($keyword) < 2) {
    echo json_encode(['suggestions' => []]);
    exit;
}

// Initialize MealAPI
$mealAPI = new MealAPI($conn);

// Get meals matching the keyword
$suggestions = [];

// Try to get suggestions from database first (search history, popular searches)
$dbSuggestions = $mealAPI->getSimilarSearches($keyword, 5);
if (!empty($dbSuggestions)) {
    foreach ($dbSuggestions as $suggestion) {
        $suggestions[] = $suggestion['keyword'];
    }
}

// Try to get meal name suggestions
$searchResults = $mealAPI->searchMeal($keyword);
if ($searchResults && isset($searchResults['meals']) && is_array($searchResults['meals'])) {
    foreach ($searchResults['meals'] as $meal) {
        // Add meal name if it's not already in suggestions
        if (!in_array($meal['strMeal'], $suggestions)) {
            $suggestions[] = $meal['strMeal'];
        }
        
        // Limit to 10 suggestions total
        if (count($suggestions) >= 10) {
            break;
        }
    }
}

// Return suggestions as JSON
echo json_encode(['suggestions' => $suggestions]);
?> 