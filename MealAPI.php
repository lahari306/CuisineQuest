<?php
class MealAPI {
    private $baseUrl = 'https://www.themealdb.com/api/json/v1/1/';
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Generic method to fetch data from API or database
     */
    private function fetchFromApiOrDb($endpoint, $params, $searchType, $keyword) {
        // Check if data exists in database first
        $data = $this->getFromDatabase($searchType, $keyword);
        
        if ($data) {
            // Log this as a database fetch in api_requests
            $this->logApiRequest($endpoint, "DB_FETCH", json_encode($params), 200);
            return $data;
        }
        
        // If not in database, fetch from API
        $url = $this->baseUrl . $endpoint;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $response = file_get_contents($url);
        
        if ($response === false) {
            $this->logApiRequest($endpoint, "API", json_encode($params), 500);
            return null;
        }
        
        $this->logApiRequest($endpoint, "API", json_encode($params), 200);
        
        $data = json_decode($response, true);
        
        // Save to database for future use
        if ($data) {
            $this->saveToDatabase($data, $searchType, $keyword);
        }
        
        return $data;
    }
    
    /**
     * Save search to history
     */
    private function saveSearchHistory($keyword, $searchType, $resultsCount) {
        $stmt = $this->conn->prepare("INSERT INTO search_history (keyword, search_type, results_count) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $keyword, $searchType, $resultsCount);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Save API request log
     */
    private function logApiRequest($endpoint, $requestType, $parameters, $statusCode) {
        $stmt = $this->conn->prepare("INSERT INTO api_requests (endpoint, request_type, parameters, status_code) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $endpoint, $requestType, $parameters, $statusCode);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Check if data exists in database
     */
    private function getFromDatabase($searchType, $keyword) {
        switch ($searchType) {
            case 'name':
                return $this->getMealByNameFromDb($keyword);
            case 'id':
                return $this->getMealByIdFromDb($keyword);
            case 'category':
                return $this->getMealsByCategoryFromDb($keyword);
            case 'area':
                return $this->getMealsByAreaFromDb($keyword);
            case 'ingredient':
                return $this->getMealsByIngredientFromDb($keyword);
            case 'categories':
                return $this->getCategoriesFromDb();
            case 'areas':
                return $this->getAreasFromDb();
            case 'ingredients':
                return $this->getIngredientsFromDb();
            default:
                return null;
        }
    }
    
    /**
     * Save API data to database
     */
    private function saveToDatabase($data, $searchType, $keyword) {
        switch ($searchType) {
            case 'name':
            case 'id':
            case 'random':
                if (isset($data['meals']) && is_array($data['meals'])) {
                    foreach ($data['meals'] as $meal) {
                        $this->saveMeal($meal);
                    }
                    $this->saveSearchHistory($keyword, $searchType, count($data['meals']));
                }
                break;
            case 'category':
                if (isset($data['meals']) && is_array($data['meals'])) {
                    foreach ($data['meals'] as $meal) {
                        // Fetch and save full meal details
                        $fullMeal = $this->getMealById($meal['idMeal']);
                        if ($fullMeal && isset($fullMeal['meals'][0])) {
                            $this->saveMeal($fullMeal['meals'][0]);
                        }
                    }
                    $this->saveSearchHistory($keyword, $searchType, count($data['meals']));
                }
                break;
            case 'area':
                if (isset($data['meals']) && is_array($data['meals'])) {
                    foreach ($data['meals'] as $meal) {
                        // Fetch and save full meal details
                        $fullMeal = $this->getMealById($meal['idMeal']);
                        if ($fullMeal && isset($fullMeal['meals'][0])) {
                            $this->saveMeal($fullMeal['meals'][0]);
                        }
                    }
                    $this->saveSearchHistory($keyword, $searchType, count($data['meals']));
                }
                break;
            case 'ingredient':
                if (isset($data['meals']) && is_array($data['meals'])) {
                    foreach ($data['meals'] as $meal) {
                        // Fetch and save full meal details
                        $fullMeal = $this->getMealById($meal['idMeal']);
                        if ($fullMeal && isset($fullMeal['meals'][0])) {
                            $this->saveMeal($fullMeal['meals'][0]);
                        }
                    }
                    $this->saveSearchHistory($keyword, $searchType, count($data['meals']));
                }
                break;
            case 'categories':
                if (isset($data['categories']) && is_array($data['categories'])) {
                    foreach ($data['categories'] as $category) {
                        $this->saveCategory($category);
                    }
                }
                break;
            case 'areas':
                if (isset($data['meals']) && is_array($data['meals'])) {
                    foreach ($data['meals'] as $area) {
                        $this->saveArea($area);
                    }
                }
                break;
            case 'ingredients':
                if (isset($data['meals']) && is_array($data['meals'])) {
                    foreach ($data['meals'] as $ingredient) {
                        $this->saveIngredient($ingredient);
                    }
                }
                break;
        }
    }
    
    /**
     * Save a meal to the database
     */
    private function saveMeal($meal) {
        if (!isset($meal['idMeal'])) return;
        
        // Check if meal already exists
        $checkStmt = $this->conn->prepare("SELECT id FROM meals WHERE id = ?");
        $checkStmt->bind_param("i", $meal['idMeal']);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $exists = $result->num_rows > 0;
        $checkStmt->close();
        
        if ($exists) {
            // Update existing meal
            $stmt = $this->conn->prepare("UPDATE meals SET 
                name = ?, category = ?, area = ?, instructions = ?, 
                thumbnail = ?, youtube_link = ?, source_link = ? 
                WHERE id = ?");
            $stmt->bind_param("sssssssi", 
                $meal['strMeal'], 
                $meal['strCategory'], 
                $meal['strArea'], 
                $meal['strInstructions'], 
                $meal['strMealThumb'], 
                $meal['strYoutube'], 
                $meal['strSource'], 
                $meal['idMeal']
            );
            $stmt->execute();
            $stmt->close();
            
            // Delete existing ingredients for this meal
            $deleteIngStmt = $this->conn->prepare("DELETE FROM meal_ingredients WHERE meal_id = ?");
            $deleteIngStmt->bind_param("i", $meal['idMeal']);
            $deleteIngStmt->execute();
            $deleteIngStmt->close();
        } else {
            // Insert new meal
            $stmt = $this->conn->prepare("INSERT INTO meals 
                (id, name, category, area, instructions, thumbnail, youtube_link, source_link) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssss", 
                $meal['idMeal'], 
                $meal['strMeal'], 
                $meal['strCategory'], 
                $meal['strArea'], 
                $meal['strInstructions'], 
                $meal['strMealThumb'], 
                $meal['strYoutube'], 
                $meal['strSource']
            );
            $stmt->execute();
            $stmt->close();
        }
        
        // Add ingredients
        for ($i = 1; $i <= 20; $i++) {
            $ingredient = $meal["strIngredient$i"];
            $measure = $meal["strMeasure$i"];
            
            if (!empty(trim($ingredient))) {
                $stmt = $this->conn->prepare("INSERT INTO meal_ingredients 
                    (meal_id, ingredient, measure) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $meal['idMeal'], $ingredient, $measure);
                $stmt->execute();
                $stmt->close();
                
                // Also save this as an ingredient if not exists
                $this->saveIngredientIfNotExists($ingredient);
            }
        }
        
        // Save category if not exists
        if (!empty($meal['strCategory'])) {
            $this->saveCategoryIfNotExists($meal['strCategory']);
        }
        
        // Save area if not exists
        if (!empty($meal['strArea'])) {
            $this->saveAreaIfNotExists($meal['strArea']);
        }
    }
    
    /**
     * Save ingredient if not exists
     */
    private function saveIngredientIfNotExists($name) {
        $stmt = $this->conn->prepare("INSERT IGNORE INTO ingredients (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Save category if not exists
     */
    private function saveCategoryIfNotExists($name) {
        $stmt = $this->conn->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Save area if not exists
     */
    private function saveAreaIfNotExists($name) {
        $stmt = $this->conn->prepare("INSERT IGNORE INTO areas (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Save a category to the database
     */
    private function saveCategory($category) {
        $stmt = $this->conn->prepare("INSERT INTO categories 
            (name, thumbnail, description) VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE thumbnail = ?, description = ?");
        $stmt->bind_param("sssss", 
            $category['strCategory'], 
            $category['strCategoryThumb'], 
            $category['strCategoryDescription'],
            $category['strCategoryThumb'], 
            $category['strCategoryDescription']
        );
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Save an area to the database
     */
    private function saveArea($area) {
        if (isset($area['strArea'])) {
            $stmt = $this->conn->prepare("INSERT IGNORE INTO areas (name) VALUES (?)");
            $stmt->bind_param("s", $area['strArea']);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    /**
     * Save an ingredient to the database
     */
    private function saveIngredient($ingredient) {
        if (isset($ingredient['strIngredient']) && isset($ingredient['strDescription'])) {
            $thumb = "https://www.themealdb.com/images/ingredients/{$ingredient['strIngredient']}.png";
            $stmt = $this->conn->prepare("INSERT INTO ingredients 
                (name, description, thumbnail) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE description = ?, thumbnail = ?");
            $stmt->bind_param("sssss", 
                $ingredient['strIngredient'], 
                $ingredient['strDescription'],
                $thumb,
                $ingredient['strDescription'],
                $thumb
            );
            $stmt->execute();
            $stmt->close();
        }
    }
    
    /**
     * Retrieve meals by name from database
     */
    private function getMealByNameFromDb($name) {
        $name = "%$name%";
        $stmt = $this->conn->prepare("SELECT id FROM meals WHERE name LIKE ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            $meals = [];
            while ($row = $result->fetch_assoc()) {
                $mealDetails = $this->getMealByIdFromDb($row['id']);
                if ($mealDetails && isset($mealDetails['meals'][0])) {
                    $meals[] = $mealDetails['meals'][0];
                }
            }
            
            return ['meals' => $meals];
        }
        
        return null;
    }
    
    /**
     * Retrieve meal by ID from database
     */
    private function getMealByIdFromDb($id) {
        $stmt = $this->conn->prepare("
            SELECT m.*, 
                GROUP_CONCAT(mi.ingredient SEPARATOR '|') as ingredients,
                GROUP_CONCAT(mi.measure SEPARATOR '|') as measures
            FROM meals m
            LEFT JOIN meal_ingredients mi ON m.id = mi.meal_id
            WHERE m.id = ?
            GROUP BY m.id
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            $mealData = $result->fetch_assoc();
            
            // Convert to API format
            $ingredients = explode('|', $mealData['ingredients']);
            $measures = explode('|', $mealData['measures']);
            
            $meal = [
                'idMeal' => $mealData['id'],
                'strMeal' => $mealData['name'],
                'strCategory' => $mealData['category'],
                'strArea' => $mealData['area'],
                'strInstructions' => $mealData['instructions'],
                'strMealThumb' => $mealData['thumbnail'],
                'strYoutube' => $mealData['youtube_link'],
                'strSource' => $mealData['source_link']
            ];
            
            // Add ingredients
            for ($i = 0; $i < count($ingredients); $i++) {
                $idx = $i + 1;
                $meal["strIngredient$idx"] = $ingredients[$i];
                $meal["strMeasure$idx"] = $measures[$i] ?? '';
            }
            
            // Fill remaining ingredients with empty strings
            for ($i = count($ingredients) + 1; $i <= 20; $i++) {
                $meal["strIngredient$i"] = '';
                $meal["strMeasure$i"] = '';
            }
            
            // Update last accessed
            $updateStmt = $this->conn->prepare("UPDATE meals SET last_accessed = CURRENT_TIMESTAMP WHERE id = ?");
            $updateStmt->bind_param("i", $id);
            $updateStmt->execute();
            $updateStmt->close();
            
            return ['meals' => [$meal]];
        }
        
        return null;
    }
    
    /**
     * Retrieve meals by category from database
     */
    private function getMealsByCategoryFromDb($category) {
        $stmt = $this->conn->prepare("SELECT id, name, thumbnail FROM meals WHERE category = ?");
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            $meals = [];
            while ($row = $result->fetch_assoc()) {
                $meals[] = [
                    'idMeal' => $row['id'],
                    'strMeal' => $row['name'],
                    'strMealThumb' => $row['thumbnail']
                ];
            }
            
            return ['meals' => $meals];
        }
        
        return null;
    }
    
    /**
     * Retrieve meals by area from database
     */
    private function getMealsByAreaFromDb($area) {
        $stmt = $this->conn->prepare("SELECT id, name, thumbnail FROM meals WHERE area = ?");
        $stmt->bind_param("s", $area);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            $meals = [];
            while ($row = $result->fetch_assoc()) {
                $meals[] = [
                    'idMeal' => $row['id'],
                    'strMeal' => $row['name'],
                    'strMealThumb' => $row['thumbnail']
                ];
            }
            
            return ['meals' => $meals];
        }
        
        return null;
    }
    
    /**
     * Retrieve meals by ingredient from database
     */
    private function getMealsByIngredientFromDb($ingredient) {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT m.id, m.name, m.thumbnail 
            FROM meals m
            JOIN meal_ingredients mi ON m.id = mi.meal_id
            WHERE mi.ingredient LIKE ?
        ");
        $searchIngredient = "%$ingredient%";
        $stmt->bind_param("s", $searchIngredient);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            $meals = [];
            while ($row = $result->fetch_assoc()) {
                $meals[] = [
                    'idMeal' => $row['id'],
                    'strMeal' => $row['name'],
                    'strMealThumb' => $row['thumbnail']
                ];
            }
            
            return ['meals' => $meals];
        }
        
        return null;
    }
    
    /**
     * Retrieve categories from database
     */
    private function getCategoriesFromDb() {
        $stmt = $this->conn->prepare("SELECT name, thumbnail, description FROM categories");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            $categories = [];
            while ($row = $result->fetch_assoc()) {
                $categories[] = [
                    'strCategory' => $row['name'],
                    'strCategoryThumb' => $row['thumbnail'],
                    'strCategoryDescription' => $row['description']
                ];
            }
            
            return ['categories' => $categories];
        }
        
        return null;
    }
    
    /**
     * Retrieve areas from database
     */
    private function getAreasFromDb() {
        $stmt = $this->conn->prepare("SELECT name FROM areas");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            $areas = [];
            while ($row = $result->fetch_assoc()) {
                $areas[] = [
                    'strArea' => $row['name']
                ];
            }
            
            return ['meals' => $areas];
        }
        
        return null;
    }
    
    /**
     * Retrieve ingredients from database
     */
    private function getIngredientsFromDb() {
        $stmt = $this->conn->prepare("SELECT name, description, thumbnail FROM ingredients");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            $ingredients = [];
            while ($row = $result->fetch_assoc()) {
                $ingredients[] = [
                    'idIngredient' => 0, // We don't have this from API
                    'strIngredient' => $row['name'],
                    'strDescription' => $row['description'],
                    'strType' => null // We don't have this from API
                ];
            }
            
            return ['meals' => $ingredients];
        }
        
        return null;
    }
    
    /**
     * Search for meals by name
     */
    public function searchMeal($name) {
        return $this->fetchFromApiOrDb('search.php', ['s' => $name], 'name', $name);
    }
    
    /**
     * Get meal details by ID
     */
    public function getMealById($id) {
        return $this->fetchFromApiOrDb('lookup.php', ['i' => $id], 'id', $id);
    }
    
    /**
     * Get a random meal
     */
    public function getRandomMeal() {
        return $this->fetchFromApiOrDb('random.php', [], 'random', 'random');
    }
    
    /**
     * List all categories
     */
    public function getCategories() {
        return $this->fetchFromApiOrDb('categories.php', [], 'categories', 'all');
    }
    
    /**
     * List all areas
     */
    public function getAreas() {
        return $this->fetchFromApiOrDb('list.php', ['a' => 'list'], 'areas', 'all');
    }
    
    /**
     * List all ingredients
     */
    public function getIngredients() {
        return $this->fetchFromApiOrDb('list.php', ['i' => 'list'], 'ingredients', 'all');
    }
    
    /**
     * Get meals by category
     */
    public function getMealsByCategory($category) {
        return $this->fetchFromApiOrDb('filter.php', ['c' => $category], 'category', $category);
    }
    
    /**
     * Get meals by area
     */
    public function getMealsByArea($area) {
        return $this->fetchFromApiOrDb('filter.php', ['a' => $area], 'area', $area);
    }
    
    /**
     * Get meals by main ingredient
     */
    public function getMealsByIngredient($ingredient) {
        return $this->fetchFromApiOrDb('filter.php', ['i' => $ingredient], 'ingredient', $ingredient);
    }
    
    /**
     * Get similar search results based on keyword
     */
    public function getSimilarSearches($keyword) {
        $keyword = "%$keyword%";
        $stmt = $this->conn->prepare("
            SELECT keyword, COUNT(*) as count
            FROM search_history
            WHERE keyword LIKE ?
            GROUP BY keyword
            ORDER BY count DESC
            LIMIT 5
        ");
        $stmt->bind_param("s", $keyword);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        $searches = [];
        while ($row = $result->fetch_assoc()) {
            $searches[] = $row;
        }
        
        return $searches;
    }
    
    /**
     * Get most popular searches
     */
    public function getPopularSearches($limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT keyword, COUNT(*) as count, search_type
            FROM search_history
            GROUP BY keyword
            ORDER BY count DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        $searches = [];
        while ($row = $result->fetch_assoc()) {
            $searches[] = $row;
        }
        
        return $searches;
    }
    
    /**
     * Get latest meals from database, or fetch from API if not available
     * @param int $limit Number of meals to return
     * @return array|null Meals data
     */
    public function getLatestMeals($limit = 20) {
        // Try to get from database first
        $meals = $this->getLatestMealsFromDb($limit);
        
        if ($meals) {
            return ['meals' => $meals];
        }
        
        // If no meals in database, get random meals from API
        $randomMeals = [];
        for ($i = 0; $i < min($limit, 10); $i++) {
            $meal = $this->getRandomMeal();
            if ($meal && isset($meal['meals'][0]) && !$this->mealExistsInArray($randomMeals, $meal['meals'][0])) {
                $randomMeals[] = $meal['meals'][0];
            }
        }
        
        // If we got some meals, return them
        if (!empty($randomMeals)) {
            return ['meals' => $randomMeals];
        }
        
        return null;
    }
    
    /**
     * Check if a meal already exists in an array
     * @param array $mealsArray Array of meals
     * @param array $meal Meal to check
     * @return bool Whether meal exists in array
     */
    private function mealExistsInArray($mealsArray, $meal) {
        foreach ($mealsArray as $existingMeal) {
            if ($existingMeal['idMeal'] === $meal['idMeal']) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get latest meals from database
     * @param int $limit Number of meals to return
     * @return array|null Meals data
     */
    private function getLatestMealsFromDb($limit = 20) {
        try {
            $stmt = $this->conn->prepare("
                SELECT m.*, c.name as strCategory, a.name as strArea 
                FROM meals m
                LEFT JOIN categories c ON m.category_id = c.id
                LEFT JOIN areas a ON m.area_id = a.id
                ORDER BY m.id DESC
                LIMIT :limit
            ");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($meals)) {
                return null;
            }
            
            // Convert DB column names to API format if needed
            foreach ($meals as &$meal) {
                if (!isset($meal['strMeal']) && isset($meal['name'])) {
                    $meal['strMeal'] = $meal['name'];
                }
                if (!isset($meal['strMealThumb']) && isset($meal['thumbnail'])) {
                    $meal['strMealThumb'] = $meal['thumbnail'];
                }
                if (!isset($meal['idMeal']) && isset($meal['id'])) {
                    $meal['idMeal'] = $meal['id'];
                }
            }
            
            return $meals;
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return null;
        }
    }
}
?> 