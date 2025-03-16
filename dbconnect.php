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

// Create category table if not exists
$createCategoryTable = "CREATE TABLE IF NOT EXISTS category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoryname VARCHAR(100) NOT NULL UNIQUE
)";

$con->query($createCategoryTable);

// Add default categories if none exist
$checkCategories = "SELECT COUNT(*) as count FROM category";
$result = $con->query($checkCategories);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
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