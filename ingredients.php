<?php
require_once 'config/database.php';
require_once 'includes/MealAPI.php';

// Initialize MealAPI
$mealAPI = new MealAPI($conn);

// Get search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get all ingredients
$ingredientsData = $mealAPI->getIngredients();
$allIngredients = [];

if ($ingredientsData && isset($ingredientsData['meals'])) {
    $allIngredients = $ingredientsData['meals'];
    
    // If search query is provided, filter ingredients
    if (!empty($search)) {
        $filteredIngredients = [];
        foreach ($allIngredients as $ingredient) {
            if (stripos($ingredient['strIngredient'], $search) !== false) {
                $filteredIngredients[] = $ingredient;
            }
        }
        $allIngredients = $filteredIngredients;
    }
}

// Sort ingredients alphabetically
usort($allIngredients, function($a, $b) {
    return strcasecmp($a['strIngredient'], $b['strIngredient']);
});

// Include header
include 'includes/header.php';
?>

<div class="container">
    <!-- Ingredients Header -->
    <div class="bg-light p-4 rounded-3 mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="mb-0">Ingredients</h1>
                <p class="text-muted mt-2 mb-0">
                    Browse our collection of <?php echo count($allIngredients); ?> ingredients or search for specific ones.
                </p>
            </div>
            <div class="col-md-6">
                <form action="ingredients.php" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search for ingredients...">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Alphabet Navigation -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-center alphabet-nav">
                <?php
                $letters = range('A', 'Z');
                foreach ($letters as $letter) {
                    echo '<a href="#letter-' . $letter . '" class="btn btn-sm btn-outline-secondary m-1">' . $letter . '</a>';
                }
                ?>
            </div>
        </div>
    </div>
    
    <!-- Ingredients List -->
    <?php
    $currentLetter = '';
    foreach ($allIngredients as $ingredient):
        $firstLetter = strtoupper(substr($ingredient['strIngredient'], 0, 1));
        
        // Create alphabet section headers
        if ($firstLetter !== $currentLetter):
            if (!empty($currentLetter)) {
                echo '</div>'; // Close previous row
            }
            $currentLetter = $firstLetter;
    ?>
    <h2 id="letter-<?php echo $currentLetter; ?>" class="mt-4 mb-3 section-title"><?php echo $currentLetter; ?></h2>
    <div class="row">
    <?php endif; ?>
        
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card h-100 ingredient-card">
                <?php 
                // Format the ingredient name and determine image path
                $ingredientName = $ingredient['strIngredient'];
                $localImagePath = "images/ingredients/{$ingredientName}.png";
                $remoteImageUrl = "https://themealdb.com/images/ingredients/{$ingredientName}.png";
                
                // Use local image if it exists, otherwise use remote URL
                $imageUrl = file_exists($localImagePath) ? $localImagePath : $remoteImageUrl;
                ?>
                <img src="<?php echo $imageUrl; ?>" 
                     class="card-img-top" 
                     alt="<?php echo $ingredientName; ?>"
                     onerror="imgError(this)">
                <div class="card-body text-center">
                    <h5 class="card-title"><?php echo $ingredient['strIngredient']; ?></h5>
                </div>
                <div class="card-footer bg-white border-top-0 text-center">
                    <a href="ingredient.php?name=<?php echo urlencode($ingredient['strIngredient']); ?>" class="btn btn-outline-primary btn-sm">Browse Recipes</a>
                </div>
            </div>
        </div>
        
    <?php endforeach; ?>
    
    <?php if (empty($allIngredients)): ?>
    <div class="alert alert-info">
        No ingredients found matching your search. Please try a different search term.
    </div>
    <?php elseif (!empty($currentLetter)): ?>
    </div> <!-- Close the last row -->
    <?php endif; ?>
    
    <!-- Back to Top Button -->
    <div class="text-center mb-5 mt-4">
        <a href="#" class="btn btn-outline-primary">
            <i class="fas fa-arrow-up me-2"></i> Back to Top
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>