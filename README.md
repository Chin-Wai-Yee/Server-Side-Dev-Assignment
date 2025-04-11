# Recipe Culinary Web Application

A PHP-based recipe management system that allows users to create, view, edit, and delete recipes. The application also includes a cooking competition feature for users to participate in culinary contests.

## Installation

### Prerequisites
- XAMPP (or equivalent with PHP 7.0+ and MySQL)
- Web browser

### Option 1: Clone the repository (Recommended)
1. Navigate to your XAMPP htdocs folder:
    ```bash
    cd /path/to/xampp/htdocs
    ```

2. Clone the repository:
    ```bash
    git clone https://github.com/Chin-Wai-Yee/Server-Side-Dev-Assignment.git "recipe culinary"
    ```
### Option 2: Manual download
1. Download the ZIP file from GitHub
2. Extract the contents
3. Rename the extracted folder to "recipe culinary"
4. Move the folder to your XAMPP htdocs directory

### Database Setup
1. Start XAMPP and ensure Apache and MySQL services are running
2. Open phpMyAdmin (usually at http://localhost/phpmyadmin)
3. Import the database structure:
   - Click on "Import" in the top menu
   - Choose the file "database.sql" from the project folder
   - Click "Go" to import the database structure and sample data