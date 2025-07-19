<?php
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../includes/MealAPI.php';

// Initialize MealAPI
$mealAPI = new MealAPI($conn);

// Get parameters from query
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : 'name';

// Items per page
$perPage = 8;
$offset = ($page - 1) * $perPage;

// Get results from database
$results = [];
$hasMore = false;

// SQL query based on search type
$sql = "";
$params = [];

switch ($type) {
    case 'name':
        $keyword = "%$keyword%";
        $sql = "SELECT m.id, m.name, m.thumbnail, m.category, m.area 
                FROM meals m 
                WHERE m.name LIKE ? 
                ORDER BY m.name 
                LIMIT ? OFFSET ?";
        $params = [$keyword, $perPage + 1, $offset];
        break;
    
    case 'category':
        $sql = "SELECT m.id, m.name, m.thumbnail, m.category, m.area 
                FROM meals m 
                WHERE m.category = ? 
                ORDER BY m.name 
                LIMIT ? OFFSET ?";
        $params = [$keyword, $perPage + 1, $offset];
        break;
    
    case 'area':
        $sql = "SELECT m.id, m.name, m.thumbnail, m.category, m.area 
                FROM meals m 
                WHERE m.area = ? 
                ORDER BY m.name 
                LIMIT ? OFFSET ?";
        $params = [$keyword, $perPage + 1, $offset];
        break;
    
    case 'ingredient':
        $keyword = "%$keyword%";
        $sql = "SELECT DISTINCT m.id, m.name, m.thumbnail, m.category, m.area 
                FROM meals m 
                JOIN meal_ingredients mi ON m.id = mi.meal_id 
                WHERE mi.ingredient LIKE ? 
                ORDER BY m.name 
                LIMIT ? OFFSET ?";
        $params = [$keyword, $perPage + 1, $offset];
        break;
}

if (!empty($sql)) {
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    if (count($params) == 3) {
        $stmt->bind_param(str_repeat('s', count($params) - 2) . 'ii', $params[0], $params[1], $params[2]);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        if ($count < $perPage) {
            $results[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'thumbnail' => $row['thumbnail'],
                'category' => $row['category'],
                'area' => $row['area']
            ];
        } else {
            $hasMore = true;
            break;
        }
        $count++;
    }
    
    $stmt->close();
}

// Return JSON response
echo json_encode([
    'results' => $results,
    'hasMore' => $hasMore,
    'page' => $page
]);
?> 