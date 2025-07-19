<?php
require_once 'config/database.php';
require_once 'includes/MealAPI.php';

// Initialize MealAPI
$mealAPI = new MealAPI($conn);

// Get all categories
$categories = $mealAPI->getCategories();

// Include header
include 'includes/header.php';
?>

<div class="container">
    <!-- Categories Header -->
    <div class="bg-light p-4 rounded-3 mb-4">
        <h1 class="mb-0">Recipe Categories</h1>
    </div>
    
    <!-- Categories Grid -->
    <div class="row mb-5">
        <?php if (isset($categories['categories'])): ?>
            <?php foreach ($categories['categories'] as $category): ?>
            <?php
                // Get the category name and determine image path
                $categoryName = $category['strCategory'];
                $localImagePath = "images/categories/{$categoryName}.png";
                $remoteImageUrl = "https://themealdb.com/images/category/{$categoryName}.png";
                
                // Use local image if it exists, otherwise use remote URL
                $imageUrl = file_exists($localImagePath) ? $localImagePath : $remoteImageUrl;
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card h-100 category-card">
                    <img src="<?php echo $imageUrl; ?>" class="card-img-top" alt="<?php echo $category['strCategory']; ?>" onerror="imgError(this)">
                    <div class="card-body">
                        <h5 class="card-title text-center"><?php echo $category['strCategory']; ?></h5>
                        <p class="card-text small text-muted">
                            <?php 
                            $description = $category['strCategoryDescription'];
                            echo (strlen($description) > 100) 
                                ? substr($description, 0, 100) . '...' 
                                : $description;
                            ?>
                        </p>
                    </div>
                    <div class="card-footer bg-white border-top-0 text-center">
                        <a href="category.php?name=<?php echo urlencode($category['strCategory']); ?>" class="btn btn-outline-primary btn-sm">Browse Recipes</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    No categories available at the moment. Please try again later.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 