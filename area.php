<?php
require_once 'config/database.php';
require_once 'includes/MealAPI.php';

// Initialize MealAPI
$mealAPI = new MealAPI($conn);

// Get area name from URL
$areaName = isset($_GET['name']) ? trim($_GET['name']) : '';

// Image mapping for cuisines
$cuisineImages = [
    'American' => 'https://images.unsplash.com/photo-1551782450-a2132b4ba21d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'British' => 'https://images.unsplash.com/photo-1553621042-f6e147245754?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Canadian' => 'https://images.unsplash.com/photo-1593079831268-3381b0db4a77?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Chinese' => 'https://images.unsplash.com/photo-1563245372-f21724e3856d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Dutch' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Egyptian' => 'https://images.unsplash.com/photo-1590579491624-f98f36d4c763?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'French' => 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Greek' => 'https://images.unsplash.com/photo-1604329760661-e71dc83f8f26?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Indian' => 'https://images.unsplash.com/photo-1589778655375-3e622c9f31c8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Irish' => 'https://images.unsplash.com/photo-1608855238293-a8853e7f7c98?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Italian' => 'https://images.unsplash.com/photo-1546549032-9571cd6b27df?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Jamaican' => 'https://images.unsplash.com/photo-1544378730-8d994c3e4aaf?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Japanese' => 'https://images.unsplash.com/photo-1611143669185-af224c5e3252?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Kenyan' => 'https://images.unsplash.com/photo-1528207776546-365bb710ee93?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Malaysian' => 'https://images.unsplash.com/photo-1570275239925-4af0aa8b4e5f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Mexican' => 'https://images.unsplash.com/photo-1625167171750-419e95f47ca6?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Moroccan' => 'https://images.unsplash.com/photo-1541845157-a6d2d100c931?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Polish' => 'https://images.unsplash.com/photo-1619683551667-d5b630002b18?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Portuguese' => 'https://images.unsplash.com/photo-1536489885071-87983c3e2859?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Russian' => 'https://images.unsplash.com/photo-1547069001-93bcf2210cd6?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Spanish' => 'https://images.unsplash.com/photo-1515443961218-a51367888e4b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Thai' => 'https://images.unsplash.com/photo-1569562211093-4ed0d0758f10?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Tunisian' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Turkish' => 'https://images.unsplash.com/photo-1579697096985-41fe1430e5df?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Vietnamese' => 'https://images.unsplash.com/photo-1576577445504-6af96cc4d5d8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60',
    'Unknown' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60'
];

// Default image for cuisines not in the mapping
$defaultImage = 'https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=60';

// Get cuisine description
$cuisineDescriptions = [
    'American' => 'American cuisine reflects the history of the United States, blending the culinary contributions of various groups of people from around the world.',
    'British' => 'British cuisine is the specific set of cooking traditions and practices associated with the United Kingdom, known for dishes like fish and chips and full breakfast.',
    'Canadian' => 'Canadian cuisine varies widely depending on the regions of the nation, incorporating influences from English, French, and Indigenous cuisines.',
    'Chinese' => 'Chinese cuisine is an important part of Chinese culture, which includes cuisines originating from various regions of China.',
    'Dutch' => 'Dutch cuisine is formed from the cooking traditions and practices of the Netherlands, featuring simple and straightforward dishes.',
    'Egyptian' => 'Egyptian cuisine makes heavy use of legumes, vegetables and fruits from Egypt\'s rich Nile Valley and Delta.',
    'French' => 'French cuisine is renowned for being one of the finest in the world, known for its classical techniques and regional diversity.',
    'Greek' => 'Greek cuisine features Mediterranean flavors, including olive oil, vegetables, herbs, grains, bread, wine, fish, and meat.',
    'Indian' => 'Indian cuisine consists of a wide variety of regional cuisines native to India, featuring a wide assortment of dishes and cooking techniques.',
    'Irish' => 'Irish cuisine is the style of cooking that originated from Ireland, featuring hearty dishes with simple ingredients.',
    'Italian' => 'Italian cuisine has developed through centuries of social and political changes, with roots dating back to ancient Rome.',
    'Jamaican' => 'Jamaican cuisine includes a mixture of cooking techniques, flavors, spices and influences from indigenous peoples and various cultures.',
    'Japanese' => 'Japanese cuisine is based on combining the staple food, which is steamed rice, with one or more main dishes and side dishes.',
    'Kenyan' => 'Kenyan cuisine is diverse, with different tribes having their own traditional foods influenced by local produce and cultural preferences.',
    'Malaysian' => 'Malaysian cuisine reflects the multicultural aspects of Malaysia, featuring influences from Malay, Chinese, Indian, Thai, and other cultures.',
    'Mexican' => 'Mexican cuisine is primarily a fusion of indigenous Mesoamerican cooking with European, especially Spanish, elements.',
    'Moroccan' => 'Moroccan cuisine is influenced by Morocco\'s interactions and exchanges with other cultures and nations over the centuries.',
    'Polish' => 'Polish cuisine is hearty and uses a lot of cream and eggs. The traditional dishes are often rich in meat, especially pork.',
    'Portuguese' => 'Portuguese cuisine is characterized by rich, filling and full-flavored dishes and is closely related to Mediterranean cuisine.',
    'Russian' => 'Russian cuisine is diverse, with Northern and Eastern European, Caucasian, Central Asian, Siberian, and East Asian influences.',
    'Spanish' => 'Spanish cuisine consists of a variety of dishes, which stem from differences in geography, culture and climate.',
    'Thai' => 'Thai cuisine places emphasis on lightly prepared dishes with strong aromatic components and a spicy edge.',
    'Tunisian' => 'Tunisian cuisine is a blend of Mediterranean and desert dwellers\' culinary traditions, characterized by spicy food.',
    'Turkish' => 'Turkish cuisine is largely the heritage of Ottoman cuisine, which can be described as a fusion and refinement of various cuisines.',
    'Vietnamese' => 'Vietnamese cuisine encompasses the foods and beverages of Vietnam, featuring fresh ingredients, minimal use of oil, and reliance on herbs and vegetables.',
    'Unknown' => 'This cuisine represents a blend of various international cooking traditions and influences.'
];

// Get meals for this area
$meals = [];
if (!empty($areaName)) {
    $areaMeals = $mealAPI->getMealsByArea($areaName);
    if ($areaMeals && isset($areaMeals['meals'])) {
        $meals = $areaMeals['meals'];
    }
}

// If area not found, redirect to areas page
if (empty($meals)) {
    header('Location: areas.php');
    exit;
}

// Get cuisine image and description
$cuisineImage = isset($cuisineImages[$areaName]) ? $cuisineImages[$areaName] : $defaultImage;
$cuisineDescription = isset($cuisineDescriptions[$areaName]) ? $cuisineDescriptions[$areaName] : 'Discover delicious recipes from this unique culinary tradition.';

// Include header
include 'includes/header.php';
?>

<div class="container">
    <!-- Area Header -->
    <div class="recipe-header" style="background-image: url('<?php echo $cuisineImage; ?>');">
        <div class="recipe-header-overlay"></div>
        <div class="recipe-header-content">
            <h1 class="display-4 fw-bold mb-2"><?php echo $areaName; ?> Cuisine</h1>
            <p class="lead"><?php echo $cuisineDescription; ?></p>
        </div>
    </div>
    
    <!-- Toggle View Options -->
    <div class="d-flex justify-content-between align-items-center my-4">
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
        <button id="load-more-btn" class="btn btn-outline-primary" data-page="1" data-keyword="<?php echo htmlspecialchars($areaName); ?>" data-search-type="area">
            Load More
        </button>
    </div>
    <?php endif; ?>
    
    <!-- Back to Areas -->
    <div class="text-center mb-5">
        <a href="areas.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to All Cuisines
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 