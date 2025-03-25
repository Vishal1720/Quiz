<?php
// Ensure no output before headers
ob_start();
session_start();

// Override any previous error settings
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// Include database connection
require_once "../dbconnect.php";

// Clear any output buffers
ob_clean();

// Check if user is admin
if (!isset($_SESSION['status']) || $_SESSION['status'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Check if quizid is provided
if (!isset($_GET['quizid'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Quiz ID is required']);
    exit();
}

try {
    if (!isset($con) || $con->connect_error) {
        throw new Exception("Database connection failed");
    }

    $quizid = mysqli_real_escape_string($con, $_GET['quizid']);

    // First, check if the is_visible column exists
    $checkColumn = $con->query("SHOW COLUMNS FROM quizdetails LIKE 'is_visible'");
    if ($checkColumn->num_rows === 0) {
        // Add the column if it doesn't exist
        $con->query("ALTER TABLE quizdetails ADD COLUMN is_visible BOOLEAN DEFAULT FALSE");
    }

    // Get visibility status
    $query = "SELECT COALESCE(is_visible, FALSE) as is_visible FROM quizdetails WHERE quizid = ?";
    $stmt = $con->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $con->error);
    }
    
    $stmt->bind_param("s", $quizid);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if (!$row) {
        throw new Exception("Quiz not found");
    }
    
    echo json_encode([
        'success' => true,
        'is_visible' => (bool)$row['is_visible']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// Flush and end output buffer
ob_end_flush(); 