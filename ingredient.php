<?php
require_once 'config/database.php';
require_once 'includes/MealAPI.php';

// Initialize MealAPI
$mealAPI = new MealAPI($conn);

// Get ingredient name from URL
$ingredientName = isset($_GET['name']) ? trim($_GET['name']) : '';

// Get meals for this ingredient
$meals = [];
if (!empty($ingredientName)) {
    $ingredientMeals = $mealAPI->getMealsByIngredient($ingredientName);
    if ($ingredientMeals && isset($ingredientMeals['meals'])) {
        $meals = $ingredientMeals['meals'];
    }
}

// If ingredient not found, redirect to ingredients page
if (empty($meals)) {
    header('Location: ingredients.php');
    exit;
}

// Include header
include 'includes/header.php';
?>

<div class="container">
    <!-- Ingredient Header -->
    <div class="bg-light p-4 rounded-3 mb-4">
        <div class="row align-items-center">
            <div class="col-md-9">
                <h1 class="mb-3">Recipes with <?php echo $ingredientName; ?></h1>
                <p class="text-muted mb-0">
                    Discover delicious recipes that use <?php echo $ingredientName; ?> as a key ingredient.
                </p>
            </div>
            <div class="col-md-3 text-center text-md-end">
                <img src="https://www.themealdb.com/images/ingredients/<?php echo urlencode($ingredientName); ?>.png" class="img-fluid" style="max-width: 120px;" alt="<?php echo $ingredientName; ?>">
            </div>
        </div>
    </div>
    
    <!-- Toggle View Options -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title mb-0"><?php echo count($meals); ?> Recipes</h2>
        <div class="btn-group view-toggle" role="group">
            <button type="button" class="btn btn-outline-primary grid-view-btn">
                <i class="fas fa-th-large"></i>
            </button>
            <button type="button" class="btn btn-outline-primary list-view-btn">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>
    
    <!-- Results Container -->
    <div class="results-container mb-4">
        <div class="row">
            <?php foreach ($meals as $meal): ?>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card h-100">
                    <img src="<?php echo $meal['strMealThumb']; ?>" class="card-img-top" alt="<?php echo $meal['strMeal']; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $meal['strMeal']; ?></h5>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <a href="meal.php?id=<?php echo $meal['idMeal']; ?>" class="btn btn-outline-primary btn-sm w-100">View Recipe</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Load More Button (if needed) -->
    <?php if (count($meals) > 12): ?>
    <div class="text-center mb-5">
        <button id="load-more-btn" class="btn btn-outline-primary" data-page="1" data-keyword="<?php echo htmlspecialchars($ingredientName); ?>" data-search-type="ingredient">
            Load More
        </button>
    </div>
    <?php endif; ?>
    
    <!-- Back to Ingredients -->
    <div class="text-center mb-5">
        <a href="ingredients.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to All Ingredients
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 