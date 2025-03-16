<?php
include "dbconnect.php";

// Check if user is admin
if(!isset($_SESSION['status']) || $_SESSION['status'] !== "admin") {
    header("Location: login.php");
    exit();
}

// Check if quizid is provided
if(!isset($_POST['quizid']) || empty($_POST['quizid'])) {
    $_SESSION['error'] = "No quiz specified for deletion.";
    header("Location: index.php");
    exit();
}

$quizid = intval($_POST['quizid']);

// Start transaction
$con->begin_transaction();

try {
    // First delete all questions associated with the quiz
    $deleteQuestions = $con->prepare("DELETE FROM quizes WHERE quizid = ?");
    $deleteQuestions->bind_param("i", $quizid);
    $deleteQuestions->execute();
    
    // Then delete the quiz details
    $deleteQuiz = $con->prepare("DELETE FROM quizdetails WHERE quizid = ?");
    $deleteQuiz->bind_param("i", $quizid);
    $deleteQuiz->execute();
    
    // Commit transaction
    $con->commit();
    
    $_SESSION['success'] = "Quiz deleted successfully!";
} catch (Exception $e) {
    // Rollback transaction on error
    $con->rollback();
    $_SESSION['error'] = "Error deleting quiz: " . $e->getMessage();
}

// Redirect back to index
header("Location: index.php");
exit();
?> 