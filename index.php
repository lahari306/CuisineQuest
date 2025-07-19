<?php
require_once 'config/database.php';
require_once 'includes/MealAPI.php';

// Initialize MealAPI
$mealAPI = new MealAPI($conn);

// Get random meal for hero section
$randomMeal = $mealAPI->getRandomMeal();

// Get categories
$categories = $mealAPI->getCategories();

// Get popular searches
$popularSearches = $mealAPI->getPopularSearches(6);

// Include header
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold mb-4">Discover Delicious Recipes</h1>
        <p class="lead mb-5">Find and cook amazing meals from around the world with our recipe collection</p>
        <div class="d-flex justify-content-center">
            <form action="search.php" method="GET" class="col-md-6 position-relative">
                <div class="input-group input-group-lg">
                    <input type="text" class="form-control" name="keyword" id="search-input" placeholder="Search for recipes...">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <!-- Live search suggestions will appear here -->
                <div id="live-search-results" class="list-group mt-1" style="position: absolute; z-index: 1000; width: 100%; display: none;"></div>
            </form>
        </div>
    </div>
</div>

<!-- Add live search JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const liveResults = document.getElementById('live-search-results');
    
    if (searchInput && liveResults) {
        // Show live search results as user types
        searchInput.addEventListener('input', debounce(function() {
            const query = this.value.trim();
            
            if (query.length > 0) {
                // Make AJAX request to get suggestions
                fetch(`api/search_suggestions.php?keyword=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.suggestions && data.suggestions.length > 0) {
                            let html = '';
                            data.suggestions.forEach(suggestion => {
                                html += `<a href="search.php?keyword=${encodeURIComponent(suggestion)}" class="list-group-item list-group-item-action">${suggestion}</a>`;
                            });
                            liveResults.innerHTML = html;
                            liveResults.style.display = 'block';
                        } else {
                            liveResults.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching suggestions:', error);
                        liveResults.style.display = 'none';
                    });
            } else {
                liveResults.style.display = 'none';
            }
        }, 300));
        
        // Hide suggestions when clicking elsewhere
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !liveResults.contains(e.target)) {
                liveResults.style.display = 'none';
            }
        });
    }
});

// Helper function to throttle API calls
function debounce(func, delay) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), delay);
    };
}
</script>

<!-- Search Suggestions -->
<?php if (!empty($popularSearches)): ?>
<div class="search-suggestions mb-5">
    <div class="container">
        <h6 class="mb-3">Popular Searches:</h6>
        <div>
            <?php foreach ($popularSearches as $search): ?>
            <a href="search.php?keyword=<?php echo urlencode($search['keyword']); ?>" class="popular-keyword">
                <?php echo htmlspecialchars($search['keyword']); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Featured Recipe -->
<?php if (isset($randomMeal['meals'][0])): ?>
<section class="mb-5">
    <div class="container">
        <h2 class="section-title">Featured Recipe</h2>
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="row g-0">
                <div class="col-md-5">
                    <img src="<?php echo $randomMeal['meals'][0]['strMealThumb']; ?>" class="img-fluid h-100" style="object-fit: cover;" alt="<?php echo $randomMeal['meals'][0]['strMeal']; ?>">
                </div>
                <div class="col-md-7">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary"><?php echo $randomMeal['meals'][0]['strCategory']; ?></span>
                            <span class="badge bg-secondary"><?php echo $randomMeal['meals'][0]['strArea']; ?></span>
                        </div>
                        <h3 class="card-title mb-3"><?php echo $randomMeal['meals'][0]['strMeal']; ?></h3>
                        <p class="card-text">
                            <?php 
                            $instructions = $randomMeal['meals'][0]['strInstructions'];
                            echo substr($instructions, 0, 300) . '...'; 
                            ?>
                        </p>
                        <div class="mt-4">
                            <h6 class="mb-3">Main Ingredients:</h6>
                            <div>
                                <?php 
                                // Display first 5 ingredients
                                for ($i = 1; $i <= 5; $i++) {
                                    $ingredient = $randomMeal['meals'][0]["strIngredient$i"];
                                    $measure = $randomMeal['meals'][0]["strMeasure$i"];
                                    
                                    if (!empty(trim($ingredient))) {
                                        echo '<span class="ingredient-badge me-2 mb-2">' . $measure . ' ' . $ingredient . '</span>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="meal.php?id=<?php echo $randomMeal['meals'][0]['idMeal']; ?>" class="btn btn-primary">View Full Recipe</a>
                            <a href="random.php" class="btn btn-outline-secondary ms-2">Get Another Random Recipe</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Categories Section -->
<?php if (isset($categories['categories'])): ?>
<section class="mb-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0">Recipe Categories</h2>
            <a href="categories.php" class="btn btn-outline-primary btn-sm">View All</a>
        </div>
        <div class="row">
            <?php 
            // Display first 8 categories
            $count = 0;
            foreach ($categories['categories'] as $category): 
                if ($count >= 8) break;
                
                // Get the category name and determine image path
                $categoryName = $category['strCategory'];
                $localImagePath = "images/categories/{$categoryName}.png";
                $remoteImageUrl = "https://themealdb.com/images/category/{$categoryName}.png";
                
                // Use local image if it exists, otherwise use remote URL
                $imageUrl = file_exists($localImagePath) ? $localImagePath : $remoteImageUrl;
            ?>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100 category-card">
                    <img src="<?php echo $imageUrl; ?>" 
                         class="card-img-top" 
                         alt="<?php echo $category['strCategory']; ?>"
                         onerror="imgError(this)">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo $category['strCategory']; ?></h5>
                        <a href="category.php?name=<?php echo urlencode($category['strCategory']); ?>" class="btn btn-outline-primary btn-sm mt-2">Browse Recipes</a>
                    </div>
                </div>
            </div>
            <?php 
                $count++;
            endforeach; 
            ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Cuisines Section -->
<section class="mb-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0">Explore World Cuisines</h2>
            <a href="areas.php" class="btn btn-outline-primary btn-sm">View All</a>
        </div>
        <div class="row">
            <?php
            // Create cuisines directory if it doesn't exist
            $cuisineDir = 'images/cuisines';
            if (!file_exists($cuisineDir)) {
                mkdir($cuisineDir, 0755, true);
            }
            
            // Featured cuisines with updated reliable image URLs
            $featuredCuisines = [
                [
                    'name' => 'Italian',
                    'image' => 'https://images.pexels.com/photos/1527603/pexels-photo-1527603.jpeg?auto=compress&cs=tinysrgb&w=500'
                ],
                [
                    'name' => 'Indian',
                    'image' => 'https://images.pexels.com/photos/2474661/pexels-photo-2474661.jpeg?auto=compress&cs=tinysrgb&w=500'
                ],
                [
                    'name' => 'Mexican',
                    'image' => 'https://images.pexels.com/photos/2092507/pexels-photo-2092507.jpeg?auto=compress&cs=tinysrgb&w=500'
                ],
                [
                    'name' => 'Chinese',
                    'image' => 'https://images.pexels.com/photos/955137/pexels-photo-955137.jpeg?auto=compress&cs=tinysrgb&w=500'
                ]
            ];
            
            // Download the images for better reliability
            foreach ($featuredCuisines as $cuisine) {
                $localPath = "$cuisineDir/{$cuisine['name']}.jpg";
                if (!file_exists($localPath)) {
                    // Only download if image doesn't exist locally
                    $imageData = @file_get_contents($cuisine['image']);
                    if ($imageData !== false) {
                        file_put_contents($localPath, $imageData);
                    }
                }
            }
            
            foreach ($featuredCuisines as $cuisine): 
                // Use local image if available, otherwise use remote URL
                $localImagePath = "$cuisineDir/{$cuisine['name']}.jpg";
                $imageUrl = file_exists($localImagePath) ? $localImagePath : $cuisine['image'];
            ?>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100 area-card">
                    <img src="<?php echo $imageUrl; ?>" 
                         class="card-img-top" 
                         alt="<?php echo $cuisine['name']; ?> Cuisine"
                         onerror="imgError(this)">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo $cuisine['name']; ?></h5>
                        <a href="area.php?name=<?php echo urlencode($cuisine['name']); ?>" class="btn btn-outline-primary btn-sm mt-2">Browse Recipes</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Search suggestions container -->
<div id="search-suggestions" style="display: none; position: absolute; z-index: 1000; width: 100%; max-width: 600px;"></div>

<?php
// Include footer
include 'includes/footer.php';
?> 