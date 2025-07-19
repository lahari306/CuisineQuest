<?php
require_once 'config/database.php';
require_once 'includes/MealAPI.php';

// Initialize MealAPI
$mealAPI = new MealAPI($conn);

// Get meal ID from URL
$mealId = isset($_GET['id']) ? $_GET['id'] : 0;

// Get meal details
$meal = null;
if ($mealId) {
    $mealData = $mealAPI->getMealById($mealId);
    if ($mealData && isset($mealData['meals'][0])) {
        $meal = $mealData['meals'][0];
    }
}

// Redirect to home if meal not found
if (!$meal) {
    header('Location: index.php');
    exit;
}

// Get ingredients list
$ingredients = [];
for ($i = 1; $i <= 20; $i++) {
    $ingredient = $meal["strIngredient$i"];
    $measure = $meal["strMeasure$i"];
    
    if (!empty(trim($ingredient))) {
        $ingredients[] = [
            'name' => $ingredient,
            'measure' => $measure
        ];
    }
}

// Include header
include 'includes/header.php';
?>

<!-- Recipe Header -->
<div class="recipe-header" style="background-image: url('<?php echo $meal['strMealThumb']; ?>');">
    <div class="recipe-header-overlay"></div>
    <div class="recipe-header-content">
        <div class="container">
            <div class="d-flex mb-3">
                <span class="badge bg-primary me-2"><?php echo $meal['strCategory']; ?></span>
                <span class="badge bg-secondary"><?php echo $meal['strArea']; ?></span>
            </div>
            <h1 class="display-5 fw-bold mb-0"><?php echo $meal['strMeal']; ?></h1>
        </div>
    </div>
</div>

<div class="container">
    <!-- Recipe Content -->
    <div class="row mb-5">
        <!-- Main Recipe Information -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="card-title mb-3">Instructions</h2>
                    <p class="instructions"><?php echo nl2br($meal['strInstructions']); ?></p>
                    
                    <?php if (!empty($meal['strYoutube'])): ?>
                    <div class="ratio ratio-16x9 mt-4">
                        <?php 
                        // Extract YouTube video ID
                        $videoId = '';
                        $url = $meal['strYoutube'];
                        if (preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $url, $matches)) {
                            $videoId = $matches[1];
                        }
                        
                        if ($videoId): 
                        ?>
                        <iframe src="https://www.youtube.com/embed/<?php echo $videoId; ?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Similar Recipes -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="card-title mb-3">More <?php echo $meal['strCategory']; ?> Recipes</h3>
                    <div class="row">
                        <?php 
                        // Get more recipes from the same category
                        $categoryMeals = $mealAPI->getMealsByCategory($meal['strCategory']);
                        if ($categoryMeals && isset($categoryMeals['meals'])) {
                            $count = 0;
                            foreach ($categoryMeals['meals'] as $catMeal):
                                // Skip current meal and limit to 4
                                if ($catMeal['idMeal'] == $mealId || $count >= 4) continue;
                        ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="row g-0">
                                    <div class="col-4">
                                        <img src="<?php echo $catMeal['strMealThumb']; ?>" class="img-fluid rounded-start h-100" style="object-fit: cover;" alt="<?php echo $catMeal['strMeal']; ?>">
                                    </div>
                                    <div class="col-8">
                                        <div class="card-body py-2 px-3">
                                            <h6 class="card-title mb-1"><?php echo $catMeal['strMeal']; ?></h6>
                                            <a href="meal.php?id=<?php echo $catMeal['idMeal']; ?>" class="btn btn-sm btn-outline-primary mt-2">View Recipe</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                                $count++;
                            endforeach;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Ingredients Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="card-title mb-3">Ingredients</h3>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($ingredients as $item): ?>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span><?php echo $item['name']; ?></span>
                            <span class="text-muted"><?php echo $item['measure']; ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Additional Information Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="card-title mb-3">Recipe Info</h3>
                    
                    <div class="mb-3">
                        <h6>Category</h6>
                        <a href="category.php?name=<?php echo urlencode($meal['strCategory']); ?>" class="btn btn-outline-primary btn-sm">
                            <?php echo $meal['strCategory']; ?>
                        </a>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Cuisine</h6>
                        <a href="area.php?name=<?php echo urlencode($meal['strArea']); ?>" class="btn btn-outline-secondary btn-sm">
                            <?php echo $meal['strArea']; ?>
                        </a>
                    </div>
                    
                    <?php if (!empty($meal['strSource'])): ?>
                    <div class="mb-3">
                        <h6>Source</h6>
                        <a href="<?php echo $meal['strSource']; ?>" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-external-link-alt me-1"></i> Original Recipe
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div>
                        <h6>Share</h6>
                        <div class="d-flex gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode('Check out this ' . $meal['strMeal'] . ' recipe!'); ?>&url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&media=<?php echo urlencode($meal['strMealThumb']); ?>&description=<?php echo urlencode($meal['strMeal'] . ' - Recipe'); ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                <i class="fab fa-pinterest"></i>
                            </a>
                            <a href="mailto:?subject=<?php echo urlencode($meal['strMeal'] . ' Recipe'); ?>&body=<?php echo urlencode('Check out this recipe: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-envelope"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Random Recipe Card -->
            <div class="card shadow-sm">
                <div class="card-body p-4 text-center">
                    <h3 class="card-title mb-3">Not what you're looking for?</h3>
                    <p class="mb-3">Try another recipe from our collection.</p>
                    <a href="random.php" class="btn btn-primary">
                        <i class="fas fa-random me-2"></i> Get Random Recipe
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 