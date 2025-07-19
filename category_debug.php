<?php
require_once 'config/database.php';
require_once 'includes/MealAPI.php';

// Initialize MealAPI
$mealAPI = new MealAPI($conn);

// Get categories
$categories = $mealAPI->getCategories();

// Debug output
echo '<pre>';
print_r($categories);
echo '</pre>';
?> 