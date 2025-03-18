<?php
session_start();
require "dbconnect.php";

// Check if user is an admin
if (!isset($_SESSION['status']) || $_SESSION['status'] !== "admin") {
    $_SESSION['error'] = "You do not have permission to delete categories.";
    header("Location: index.php");
    exit();
}

// Check if category name is provided
if (!isset($_POST['category']) || empty($_POST['category'])) {
    $_SESSION['error'] = "No category was specified for deletion.";
    header("Location: index.php");
    exit();
}

$category = mysqli_real_escape_string($con, $_POST['category']);

// First, get the quiz IDs in this category
$quizIdQuery = "SELECT quizid FROM quizdetails WHERE category = ?";
$quizIdStmt = $con->prepare($quizIdQuery);
$quizIdStmt->bind_param("s", $category);
$quizIdStmt->execute();
$quizIdResult = $quizIdStmt->get_result();

// Store all quiz IDs for deletion
$quizIds = [];
while ($row = $quizIdResult->fetch_assoc()) {
    $quizIds[] = $row['quizid'];
}

// Count how many quizzes will be deleted
$quizCount = count($quizIds);

// Begin transaction to ensure data integrity
$con->begin_transaction();

try {
    // For each quiz, delete related records
    foreach ($quizIds as $quizId) {
        // Delete quiz questions from quizes table
        $deleteQuestionsQuery = "DELETE FROM quizes WHERE quizid = ?";
        $deleteQuestionsStmt = $con->prepare($deleteQuestionsQuery);
        $deleteQuestionsStmt->bind_param("i", $quizId);
        $deleteQuestionsStmt->execute();
        
        // Delete quiz results (if applicable)
        if ($con->query("SHOW TABLES LIKE 'quiz_results'")->num_rows > 0) {
            $deleteResultsQuery = "DELETE FROM quiz_results WHERE quizid = ?";
            $deleteResultsStmt = $con->prepare($deleteResultsQuery);
            $deleteResultsStmt->bind_param("i", $quizId);
            $deleteResultsStmt->execute();
        }
        
        // Delete scheduled quizzes (if applicable)
        if ($con->query("SHOW TABLES LIKE 'scheduled_quizzes'")->num_rows > 0) {
            $deleteScheduledQuery = "DELETE FROM scheduled_quizzes WHERE quizid = ?";
            $deleteScheduledStmt = $con->prepare($deleteScheduledQuery);
            $deleteScheduledStmt->bind_param("i", $quizId);
            $deleteScheduledStmt->execute();
        }
        
        // Finally, delete the quiz details
        $deleteQuizQuery = "DELETE FROM quizdetails WHERE quizid = ?";
        $deleteQuizStmt = $con->prepare($deleteQuizQuery);
        $deleteQuizStmt->bind_param("i", $quizId);
        $deleteQuizStmt->execute();
    }
    
    // Commit the transaction if everything succeeded
    $con->commit();
    
    if ($quizCount > 0) {
        $_SESSION['success'] = "Category '" . htmlspecialchars($category) . "' has been permanently deleted along with " . $quizCount . " quiz(es).";
    } else {
        $_SESSION['success'] = "Category '" . htmlspecialchars($category) . "' has been permanently deleted. No quizzes were affected.";
    }
} catch (Exception $e) {
    // Roll back the transaction if something failed
    $con->rollback();
    $_SESSION['error'] = "Failed to delete category. Error: " . $e->getMessage();
}

// Redirect back to the index page
header("Location: index.php");
exit();
?> 