# Delicious Recipe System

A modern recipe browsing system powered by TheMealDB API, featuring a responsive design, search functionality, and database caching.

## Features

- **Modern UI**: Clean and responsive design using Bootstrap 5
- **Advanced Search**: Find recipes by name, category, cuisine, or ingredient
- **Caching System**: Stores all retrieved API data in a local database for faster future access
- **Browsing Options**: Explore recipes by categories, cuisines, or ingredients
- **Random Recipe**: Get inspired with a random recipe feature
- **Search History**: Track popular searches and offer related suggestions
- **Mobile-Friendly**: Fully responsive on all device sizes

## Requirements

- PHP 7.4 or higher
- MySQL/MariaDB database
- Web server (Apache, Nginx)
- Enabled PHP extensions: mysqli, json, curl

## Installation

1. **Clone the repository to your web server**
   ```
   git clone https://github.com/yourusername/recipe-system.git
   ```
   
   Or download and extract the ZIP file to your web server directory.

2. **Create a database**
   ```sql
   CREATE DATABASE recipe_system;
   ```

3. **Import the database schema**
   ```
   mysql -u your_username -p recipe_system < config/recipe_system.sql
   ```

4. **Configure the database connection**
   
   Edit the `config/database.php` file with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'recipe_system');
   ```

5. **Set proper permissions**
   
   Make sure your web server has write permissions to the project directory.

6. **Access the application**
   
   Open your web browser and navigate to:
   ```
   http://localhost/path_to_your_project/
   ```

## Data Flow

1. When a user searches for a recipe or browses categories:
   - The system first checks the local database for matching data
   - If found, it retrieves data from the database (faster response)
   - If not found, it fetches data from TheMealDB API and stores it in the database for future use

2. All search queries are logged in the database to track popular searches and provide suggestions to users.

## API Integration

This system uses TheMealDB's free API (v1) for recipe data. The key features include:

- Recipe search by name
- Random recipe
- Filtering by category, area, or ingredient
- Full meal details including ingredients, instructions, and images

## Customization

- **Theme**: Modify the CSS variables in `css/style.css` to change colors and styling
- **Layout**: Edit the templates in the includes directory to change the page structure
- **Add features**: Extend functionality by adding new files or modifying existing ones

## File Structure

- **config/**: Database configuration and SQL schema
- **includes/**: Core PHP classes and shared templates
- **api/**: API endpoints for AJAX functionality
- **css/**: Stylesheets
- **js/**: JavaScript files
- **Root directory**: Main page templates

## Credits

- Recipe data provided by [TheMealDB API](https://www.themealdb.com/api.php)
- Built with [Bootstrap 5](https://getbootstrap.com/) and [Font Awesome](https://fontawesome.com/)
- Images from [Unsplash](https://unsplash.com/)

## License

This project is licensed under the MIT License - see the LICENSE file for details. 