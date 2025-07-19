<?php
require_once 'config/database.php';
require_once 'includes/MealAPI.php';

// Initialize MealAPI
$mealAPI = new MealAPI($conn);

// Get random meal
$randomMeal = $mealAPI->getRandomMeal();

// Redirect to the meal page
if ($randomMeal && isset($randomMeal['meals'][0]['idMeal'])) {
    header('Location: meal.php?id=' . $randomMeal['meals'][0]['idMeal']);
    exit;
} else {
    // If API call fails, redirect to home
    header('Location: index.php');
    exit;
}
?> 