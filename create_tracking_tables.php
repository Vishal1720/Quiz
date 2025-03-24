<?php
include "dbconnect.php";

// Check if user is logged in and is admin
if ($_SESSION['status'] != "admin" || !isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Set up tables
$success = true;
$messages = [];

// Create quiz_attempts table if it doesn't exist
$create_attempts_table = "CREATE TABLE IF NOT EXISTS `quiz_attempts` (
    `attempt_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_email` VARCHAR(255) NOT NULL,
    `quizid` INT,
    `schedule_id` INT NULL,
    `score` DECIMAL(5,2) NOT NULL,
    `total_questions` INT NOT NULL,
    `start_time` DATETIME NOT NULL,
    `end_time` DATETIME NULL,
    `status` ENUM('completed', 'in-progress', 'abandoned') NOT NULL DEFAULT 'in-progress',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_email` (`user_email`),
    INDEX `idx_quizid` (`quizid`),
    INDEX `idx_schedule_id` (`schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$con->query($create_attempts_table)) {
    $success = false;
    $messages[] = "Error creating quiz_attempts table: " . $con->error;
}

// Create quiz_responses table if it doesn't exist
$create_responses_table = "CREATE TABLE IF NOT EXISTS `quiz_responses` (
    `response_id` INT AUTO_INCREMENT PRIMARY KEY,
    `attempt_id` INT NOT NULL,
    `question_id` INT NOT NULL,
    `user_answer` TEXT NOT NULL,
    `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_attempt_id` (`attempt_id`),
    INDEX `idx_question_id` (`question_id`),
    CONSTRAINT `fk_responses_attempt` FOREIGN KEY (`attempt_id`) 
        REFERENCES `quiz_attempts` (`attempt_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$con->query($create_responses_table)) {
    $success = false;
    $messages[] = "Error creating quiz_responses table: " . $con->error;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Quiz Tracking Tables</title>
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
    <link rel="stylesheet" href="nav.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 25px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-button {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 20px;
        }
        
        .back-button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <?php include "components/header.php"; ?>

    <div class="container">
        <h1>Setup Quiz Tracking Tables</h1>
        
        <?php if ($success): ?>
            <div class="message success">
                <h3>✓ Setup Complete</h3>
                <p>The quiz tracking tables have been created successfully. You can now track quiz attempts and view statistics.</p>
            </div>
        <?php else: ?>
            <div class="message error">
                <h3>⚠ Setup Error</h3>
                <p>There were errors during the setup process:</p>
                <ul>
                    <?php foreach ($messages as $message): ?>
                        <li><?php echo htmlspecialchars($message); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <a href="quiz_statistics.php" class="back-button">Back to Statistics</a>
    </div>
</body>
</html> 