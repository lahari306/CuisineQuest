<?php
require_once 'config/database.php';
require_once 'includes/MealAPI.php';

// Initialize MealAPI
$mealAPI = new MealAPI($conn);

// Get search keyword
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// Get similar searches
$similarSearches = [];
if (!empty($keyword)) {
    $similarSearches = $mealAPI->getSimilarSearches($keyword);
}

// Get search results or all meals if no keyword
$meals = [];
if (!empty($keyword)) {
    $searchResults = $mealAPI->searchMeal($keyword);
    if ($searchResults && isset($searchResults['meals']) && is_array($searchResults['meals'])) {
        $meals = $searchResults['meals'];
    }
} else {
    // Get all available meals (limited to a reasonable number, e.g., 20)
    $allMeals = $mealAPI->getLatestMeals(20);
    if ($allMeals && isset($allMeals['meals']) && is_array($allMeals['meals'])) {
        $meals = $allMeals['meals'];
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container">
    <!-- Search Header -->
    <div class="bg-light p-4 rounded-3 mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="mb-3">
                    <?php if (!empty($keyword)): ?>
                        Search Results
                    <?php else: ?>
                        Available Recipes
                    <?php endif; ?>
                </h1>
                <p class="text-muted mb-0">
                    <?php if (!empty($keyword)): ?>
                        Found <?php echo count($meals); ?> results for "<?php echo htmlspecialchars($keyword); ?>"
                    <?php else: ?>
                        Browse our latest recipes or search for something specific
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-6">
                <form action="search.php" method="GET">
                    <div class="input-group search-container">
                        <input type="text" class="form-control" name="keyword" id="search-input" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Search for recipes...">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
                <!-- Live search suggestions will appear here -->
                <div id="live-search-results" class="list-group mt-1" style="position: absolute; z-index: 1000; width: 100%; display: none;"></div>
            </div>
        </div>
    </div>
    
    <!-- Similar Searches -->
    <?php if (!empty($similarSearches)): ?>
    <div class="search-suggestions mb-4">
        <h6 class="mb-3">Similar Searches:</h6>
        <div>
            <?php foreach ($similarSearches as $search): ?>
            <a href="search.php?keyword=<?php echo urlencode($search['keyword']); ?>" class="popular-keyword">
                <?php echo htmlspecialchars($search['keyword']); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Toggle View Options -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <?php if (!empty($meals)): ?>
                <h2 class="section-title mb-0">
                    <?php if (!empty($keyword)): ?>
                        Recipes
                    <?php else: ?>
                        Latest Recipes
                    <?php endif; ?>
                </h2>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Try searching for a different meal or ingredient.
                </div>
                <h2 class="section-title mb-0">Try these popular categories</h2>
            <?php endif; ?>
        </div>
        <?php if (!empty($meals)): ?>
        <div class="btn-group view-toggle" role="group">
            <button type="button" class="btn btn-outline-primary grid-view-btn active">
                <i class="fas fa-th-large"></i>
            </button>
            <button type="button" class="btn btn-outline-primary list-view-btn">
                <i class="fas fa-list"></i>
            </button>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Results Container -->
    <div class="results-container mb-4">
        <div class="row">
            <?php if (!empty($meals)): ?>
                <?php foreach ($meals as $meal): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo $meal['strMealThumb']; ?>" 
                             class="card-img-top" 
                             alt="<?php echo $meal['strMeal']; ?>"
                             onerror="imgError(this)">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $meal['strMeal']; ?></h5>
                            <?php if (isset($meal['strCategory']) && isset($meal['strArea'])): ?>
                            <p class="card-text small">
                                <span class="badge bg-primary"><?php echo $meal['strCategory']; ?></span>
                                <span class="badge bg-secondary"><?php echo $meal['strArea']; ?></span>
                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <a href="meal.php?id=<?php echo $meal['idMeal']; ?>" class="btn btn-outline-primary btn-sm w-100">View Recipe</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php 
                // Get categories if no search results
                $categories = $mealAPI->getCategories();
                if ($categories && isset($categories['categories'])) {
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
                }
                ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Load More Button (if needed) -->
    <?php if (!empty($meals) && count($meals) >= 10): ?>
    <div class="text-center mb-5">
        <button id="load-more-btn" class="btn btn-outline-primary" data-page="1" data-keyword="<?php echo htmlspecialchars($keyword); ?>" data-search-type="name">
            Load More
        </button>
    </div>
    <?php endif; ?>
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
        
        // Focus search input when page loads
        searchInput.focus();
    }
});
</script>

<?php include 'includes/footer.php'; ?>

<!-- Empty image replacement fix -->
<?php 
// JavaScript to fix empty image sources
echo '<script>
document.addEventListener("DOMContentLoaded", function() {
    // Find all images with empty src attributes
    const emptyImages = document.querySelectorAll("img[src=\'\']");
    
    // Replace empty sources with default images or apply error handling
    emptyImages.forEach(function(img) {
        const altText = img.getAttribute("alt") || "Image";
        if (img.classList.contains("rounded-circle")) {
            // For rounded ingredient images
            const ingredientName = altText.trim();
            // Try to use local image first if possible
            const localPath = "images/ingredients/" + ingredientName + ".png";
            // Set src to a food image placeholder
            img.setAttribute("src", "https://via.placeholder.com/120/f0f0f0/666666?text=" + encodeURIComponent(ingredientName));
            img.onerror = function() { imgError(this); };
        } else {
            // For other images
            imgError(img);
        }
    });
});
</script>';
?> 