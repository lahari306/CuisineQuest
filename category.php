<?php
require_once 'config/database.php';
require_once 'includes/MealAPI.php';

// Initialize MealAPI
$mealAPI = new MealAPI($conn);

// Get category name from URL
$categoryName = isset($_GET['name']) ? trim($_GET['name']) : '';

// Get category details
$categoryDetails = null;
$categories = $mealAPI->getCategories();
if ($categories && isset($categories['categories'])) {
    foreach ($categories['categories'] as $category) {
        if (strtolower($category['strCategory']) === strtolower($categoryName)) {
            $categoryDetails = $category;
            break;
        }
    }
}

// Get meals for this category
$meals = [];
if (!empty($categoryName)) {
    $categoryMeals = $mealAPI->getMealsByCategory($categoryName);
    if ($categoryMeals && isset($categoryMeals['meals'])) {
        $meals = $categoryMeals['meals'];
    }
}

// If category not found, redirect to categories page
if (empty($categoryDetails) || empty($meals)) {
    header('Location: categories.php');
    exit;
}

// Include header
include 'includes/header.php';
?>

<div class="container">
    <!-- Category Header -->
    <div class="bg-light p-4 rounded-3 mb-4">
        <div class="row align-items-center">
            <div class="col-md-9">
                <h1 class="mb-2"><?php echo $categoryDetails['strCategory']; ?> Recipes</h1>
                <p class="text-muted mb-0">
                    <?php echo $categoryDetails['strCategoryDescription']; ?>
                </p>
            </div>
            <div class="col-md-3 text-center text-md-end">
                <?php
                    // Get the category name and determine image path
                    $categoryName = $categoryDetails['strCategory'];
                    $localImagePath = "images/categories/{$categoryName}.png";
                    $remoteImageUrl = "https://themealdb.com/images/category/{$categoryName}.png";
                    
                    // Use local image if it exists, otherwise use remote URL
                    $imageUrl = file_exists($localImagePath) ? $localImagePath : $categoryDetails['strCategoryThumb'];
                ?>
                <img src="<?php echo $imageUrl; ?>" 
                     class="img-fluid rounded-circle" 
                     style="max-width: 120px;" 
                     alt="<?php echo $categoryDetails['strCategory']; ?>"
                     onerror="imgError(this)">
            </div>
        </div>
    </div>
    
    <!-- Toggle View Options -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title mb-0"><?php echo count($meals); ?> Recipes</h2>
        <div class="btn-group view-toggle" role="group">
            <button type="button" class="btn btn-outline-primary grid-view-btn active">
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
                    <img src="<?php echo $meal['strMealThumb']; ?>" 
                         class="card-img-top" 
                         alt="<?php echo $meal['strMeal']; ?>"
                         onerror="imgError(this)">
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
        <button id="load-more-btn" class="btn btn-outline-primary" data-page="1" data-keyword="<?php echo htmlspecialchars($categoryName); ?>" data-search-type="category">
            Load More
        </button>
    </div>
    <?php endif; ?>
    
    <!-- Back to Categories -->
    <div class="text-center mb-5">
        <a href="categories.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to All Categories
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 