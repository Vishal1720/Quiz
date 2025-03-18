<?php
session_start();
require "dbconnect.php";

// Check if user is an admin
if (!isset($_SESSION['status']) || $_SESSION['status'] !== "admin") {
    $_SESSION['error'] = "You do not have permission to add categories.";
    header("Location: index.php");
    exit();
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: index.php");
    exit();
}

// Check if category name is provided
if (!isset($_POST['category_name']) || empty(trim($_POST['category_name']))) {
    $_SESSION['error'] = "Please enter a category name.";
    header("Location: index.php");
    exit();
}

$categoryName = trim($_POST['category_name']);

// Validate category name - only allow alphanumeric characters, spaces, and basic punctuation
if (!preg_match('/^[a-zA-Z0-9\s\-\_\&\.\,\:\;]+$/', $categoryName)) {
    $_SESSION['error'] = "Category name contains invalid characters. Please use only letters, numbers, spaces, and basic punctuation.";
    header("Location: index.php");
    exit();
}

// Check if category already exists
$checkQuery = "SELECT COUNT(*) as count FROM quizdetails WHERE category = ?";
$checkStmt = $con->prepare($checkQuery);
$checkStmt->bind_param("s", $categoryName);
$checkStmt->execute();
$result = $checkStmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    $_SESSION['error'] = "A category with the name '" . htmlspecialchars($categoryName) . "' already exists.";
    header("Location: index.php");
    exit();
}

// Create a placeholder quiz in the new category to make it visible in the list
$insertQuery = "INSERT INTO quizdetails (quizname, category, timer, email) VALUES (?, ?, 30, ?)";
$insertStmt = $con->prepare($insertQuery);
$placeholderQuizName = "New Quiz in " . $categoryName;

// Use admin's email from session or a default email
$adminEmail = isset($_SESSION['email']) ? $_SESSION['email'] : 'admin@quizplatform.com';

$insertStmt->bind_param("sss", $placeholderQuizName, $categoryName, $adminEmail);

if ($insertStmt->execute()) {
    $_SESSION['success'] = "Category '" . htmlspecialchars($categoryName) . "' has been created successfully with a placeholder quiz. You can edit or delete this quiz as needed.";
} else {
    $_SESSION['error'] = "Failed to create category. Error: " . $con->error;
}

header("Location: index.php");
exit();
?> 