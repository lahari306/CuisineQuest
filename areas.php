<?php
require_once 'config/database.php';
require_once 'includes/MealAPI.php';

// Initialize MealAPI
$mealAPI = new MealAPI($conn);

// Get all areas
$areas = $mealAPI->getAreas();

// Create directory for cuisine images if it doesn't exist
$cuisineDir = 'images/cuisines';
if (!file_exists($cuisineDir)) {
    mkdir($cuisineDir, 0755, true);
}

// Image mapping for cuisines with updated URLs
$cuisineImages = [
    'American' => 'https://images.pexels.com/photos/2725744/pexels-photo-2725744.jpeg?auto=compress&cs=tinysrgb&w=500',
    'British' => 'https://images.pexels.com/photos/5559549/pexels-photo-5559549.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Canadian' => 'https://images.pexels.com/photos/5622882/pexels-photo-5622882.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Chinese' => 'https://images.pexels.com/photos/955137/pexels-photo-955137.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Dutch' => 'https://images.pexels.com/photos/1435895/pexels-photo-1435895.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Egyptian' => 'https://images.pexels.com/photos/5677634/pexels-photo-5677634.jpeg?auto=compress&cs=tinysrgb&w=500',
    'French' => 'https://images.pexels.com/photos/1307658/pexels-photo-1307658.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Greek' => 'https://images.pexels.com/photos/1527603/pexels-photo-1527603.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Indian' => 'https://images.pexels.com/photos/2474661/pexels-photo-2474661.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Irish' => 'https://images.pexels.com/photos/5031971/pexels-photo-5031971.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Italian' => 'https://images.pexels.com/photos/1527603/pexels-photo-1527603.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Jamaican' => 'https://images.pexels.com/photos/1640773/pexels-photo-1640773.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Japanese' => 'https://images.pexels.com/photos/884600/pexels-photo-884600.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Kenyan' => 'https://images.pexels.com/photos/1092730/pexels-photo-1092730.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Malaysian' => 'https://images.pexels.com/photos/699953/pexels-photo-699953.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Mexican' => 'https://images.pexels.com/photos/2092507/pexels-photo-2092507.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Moroccan' => 'https://images.pexels.com/photos/7511907/pexels-photo-7511907.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Polish' => 'https://images.pexels.com/photos/5409010/pexels-photo-5409010.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Portuguese' => 'https://images.pexels.com/photos/842142/pexels-photo-842142.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Russian' => 'https://images.pexels.com/photos/5589030/pexels-photo-5589030.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Spanish' => 'https://images.pexels.com/photos/5718073/pexels-photo-5718073.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Thai' => 'https://images.pexels.com/photos/699953/pexels-photo-699953.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Tunisian' => 'https://images.pexels.com/photos/5677634/pexels-photo-5677634.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Turkish' => 'https://images.pexels.com/photos/5779364/pexels-photo-5779364.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Vietnamese' => 'https://images.pexels.com/photos/1437590/pexels-photo-1437590.jpeg?auto=compress&cs=tinysrgb&w=500',
    'Unknown' => 'https://images.pexels.com/photos/1640774/pexels-photo-1640774.jpeg?auto=compress&cs=tinysrgb&w=500'
];

// Default image for cuisines not in the mapping
$defaultImage = 'https://images.pexels.com/photos/1640774/pexels-photo-1640774.jpeg?auto=compress&cs=tinysrgb&w=500';

// Download some cuisine images to local storage for better reliability
$downloadCuisines = ['Indian', 'Jamaican', 'Malaysian', 'Polish'];
foreach ($downloadCuisines as $cuisine) {
    if (isset($cuisineImages[$cuisine])) {
        $localPath = "$cuisineDir/$cuisine.jpg";
        if (!file_exists($localPath)) {
            // Only download if image doesn't exist locally
            $imageData = @file_get_contents($cuisineImages[$cuisine]);
            if ($imageData !== false) {
                file_put_contents($localPath, $imageData);
            }
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container">
    <!-- Areas Header -->
    <div class="bg-light p-4 rounded-3 mb-4">
        <h1 class="mb-3">World Cuisines</h1>
        <p class="mb-0">Explore recipes from different culinary traditions around the world.</p>
    </div>
    
    <!-- Areas Grid -->
    <div class="row mb-5">
        <?php if (isset($areas['meals'])): ?>
            <?php foreach ($areas['meals'] as $area): ?>
            <?php 
                $areaName = $area['strArea']; 
                
                // Check if we have a local image first
                $localImagePath = "$cuisineDir/$areaName.jpg";
                if (file_exists($localImagePath)) {
                    $imageUrl = $localImagePath;
                } else {
                    // Use the remote image from our mapping or the default
                    $imageUrl = isset($cuisineImages[$areaName]) ? $cuisineImages[$areaName] : $defaultImage;
                }
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card h-100 area-card">
                    <img src="<?php echo $imageUrl; ?>" 
                         class="card-img-top" 
                         alt="<?php echo $areaName; ?> Cuisine"
                         onerror="imgError(this)">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo $areaName; ?></h5>
                    </div>
                    <div class="card-footer bg-white border-top-0 text-center">
                        <a href="area.php?name=<?php echo urlencode($areaName); ?>" class="btn btn-outline-primary btn-sm">Browse Recipes</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    No cuisines available at the moment. Please try again later.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 