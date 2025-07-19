-- Create the database
CREATE DATABASE IF NOT EXISTS recipe_system;
USE recipe_system;

-- Table for meals
CREATE TABLE IF NOT EXISTS meals (
    id INT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    area VARCHAR(100),
    instructions TEXT,
    thumbnail VARCHAR(255),
    youtube_link VARCHAR(255),
    source_link VARCHAR(255),
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for ingredients of meals
CREATE TABLE IF NOT EXISTS meal_ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meal_id INT,
    ingredient VARCHAR(255) NOT NULL,
    measure VARCHAR(255),
    FOREIGN KEY (meal_id) REFERENCES meals(id) ON DELETE CASCADE
);

-- Table for search history
CREATE TABLE IF NOT EXISTS search_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(255) NOT NULL,
    search_type ENUM('name', 'ingredient', 'category', 'area', 'random') NOT NULL,
    results_count INT DEFAULT 0,
    search_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (keyword)
);

-- Table for meal categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    thumbnail VARCHAR(255),
    description TEXT
);

-- Table for meal areas
CREATE TABLE IF NOT EXISTS areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL
);

-- Table for ingredients
CREATE TABLE IF NOT EXISTS ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    thumbnail VARCHAR(255)
);

-- Table for user favorites
CREATE TABLE IF NOT EXISTS user_favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    meal_id INT,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meal_id) REFERENCES meals(id) ON DELETE CASCADE
);

-- Table for api_requests to track API usage
CREATE TABLE IF NOT EXISTS api_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    endpoint VARCHAR(255) NOT NULL,
    request_type VARCHAR(50) NOT NULL,
    parameters TEXT,
    status_code INT,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 