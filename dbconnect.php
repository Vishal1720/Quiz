<?php 
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$server = "localhost";
$username = "root";
$pwd = "";
$database = "quiz";

$con = new mysqli($server, $username, $pwd, $database);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Set MySQL to return all results
mysqli_options($con, MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
mysqli_set_charset($con, 'utf8mb4');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Initialize session variables if not set
    if(!isset($_SESSION['status'])) {
        $_SESSION['status'] = "loggedout";
    }
    
    if(!isset($_SESSION['email'])) {
        $_SESSION['email'] = null;
    }
    
    if(!isset($_SESSION['qid'])) {
        $_SESSION['qid'] = null;
    }
    
    if(!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    }
}

// Create quizdetails table if not exists
$createQuizDetailsTable = "CREATE TABLE IF NOT EXISTS quizdetails (
    quizid INT AUTO_INCREMENT PRIMARY KEY,
    quizname VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    email VARCHAR(255),
    timer INT NOT NULL DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$con->query($createQuizDetailsTable);

// Ensure timer column exists and has correct default
$checkTimerColumn = "SHOW COLUMNS FROM quizdetails LIKE 'timer'";
$timerColumnResult = $con->query($checkTimerColumn);
if ($timerColumnResult->num_rows == 0) {
    // Timer column doesn't exist, add it
    $addTimerColumn = "ALTER TABLE quizdetails ADD COLUMN timer INT NOT NULL DEFAULT 30";
    $con->query($addTimerColumn);
} else {
    // Timer column exists, check if it has the correct default
    $timerColumn = $timerColumnResult->fetch_assoc();
    if ($timerColumn['Default'] != '30' || $timerColumn['Null'] == 'YES') {
        // Update the column to have the correct default and NOT NULL constraint
        $updateTimerColumn = "ALTER TABLE quizdetails MODIFY COLUMN timer INT NOT NULL DEFAULT 30";
        $con->query($updateTimerColumn);
    }
}

// Create quizes table if not exists
$createQuizesTable = "CREATE TABLE IF NOT EXISTS quizes (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    quizid INT NOT NULL,
    question TEXT NOT NULL,
    option1 VARCHAR(255) NOT NULL,
    option2 VARCHAR(255) NOT NULL,
    option3 VARCHAR(255) NOT NULL,
    option4 VARCHAR(255) NOT NULL,
    answer VARCHAR(255) NOT NULL,
    FOREIGN KEY (quizid) REFERENCES quizdetails(quizid) ON DELETE CASCADE
)";

$con->query($createQuizesTable);

// ===== CATEGORY AND SUBCATEGORY TABLES =====

// First, check if we need to fix the tables
$needsFix = false;

// Check if subcategories table exists
$checkSubcategoriesTable = "SHOW TABLES LIKE 'subcategories'";
$subcategoriesResult = $con->query($checkSubcategoriesTable);

// Check if category table exists
$checkCategoryTable = "SHOW TABLES LIKE 'category'";
$categoryResult = $con->query($checkCategoryTable);

// If either table doesn't exist, we need to fix
if ($subcategoriesResult->num_rows == 0 || $categoryResult->num_rows == 0) {
    $needsFix = true;
} else {
    // Both tables exist, check if subcategories table has the correct structure
    try {
        // Try to create a test subcategory to see if the foreign key works
        $testCategory = "Test_" . time();
        $con->query("INSERT IGNORE INTO category (categoryname) VALUES ('$testCategory')");
        $testSubcategory = "TestSub_" . time();
        $testInsert = $con->query("INSERT INTO subcategories (subcategory_name, category_name) VALUES ('$testSubcategory', '$testCategory')");
        
        if (!$testInsert) {
            // If insert fails, we need to fix the tables
            $needsFix = true;
        } else {
            // Clean up test data
            $con->query("DELETE FROM subcategories WHERE subcategory_name = '$testSubcategory'");
            $con->query("DELETE FROM category WHERE categoryname = '$testCategory'");
        }
    } catch (Exception $e) {
        // If any exception occurs, we need to fix the tables
        $needsFix = true;
    }
}

// If we need to fix the tables, drop and recreate them
if ($needsFix) {
    // Drop subcategories table first (to avoid foreign key constraints)
    $con->query("DROP TABLE IF EXISTS subcategories");
    
    // Drop category table
    $con->query("DROP TABLE IF EXISTS category");
    
    // Create category table
    $createCategoryTable = "CREATE TABLE category (
        categoryname VARCHAR(200) NOT NULL,
        PRIMARY KEY (categoryname)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (!$con->query($createCategoryTable)) {
        die("Error creating category table: " . $con->error);
    }
    
    // Create subcategories table
    $createSubcategoriesTable = "CREATE TABLE subcategories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subcategory_name VARCHAR(100) NOT NULL,
        category_name VARCHAR(200) NOT NULL,
        FOREIGN KEY (category_name) REFERENCES category(categoryname) ON DELETE CASCADE,
        UNIQUE KEY unique_subcategory_per_category (subcategory_name, category_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (!$con->query($createSubcategoriesTable)) {
        die("Error creating subcategories table: " . $con->error);
    }
    
    // Add default categories
    $defaultCategories = [
        'General Knowledge',
        'Science',
        'Mathematics',
        'History',
        'Geography',
        'Technology',
        'Sports',
        'Entertainment'
    ];
    
    $stmt = $con->prepare("INSERT INTO category (categoryname) VALUES (?)");
    foreach ($defaultCategories as $category) {
        $stmt->bind_param("s", $category);
        $stmt->execute();
    }
}

// Handle session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    $_SESSION['status'] = "loggedout";
    $_SESSION['qid'] = null;
}
$_SESSION['last_activity'] = time();
?>