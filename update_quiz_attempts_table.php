<?php
include "dbconnect.php";

// Check if user is logged in and is admin
if ($_SESSION['status'] != "admin" || !isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$success = false;
$message = "";

// Check if the quiz_attempts table exists
try {
    $tableCheck = $con->query("SHOW TABLES LIKE 'quiz_attempts'");
    $tableExists = $tableCheck->num_rows > 0;
    
    if (!$tableExists) {
        // Create the quiz_attempts table if it doesn't exist
        $createTableSQL = "
            CREATE TABLE quiz_attempts (
                attempt_id INT AUTO_INCREMENT PRIMARY KEY,
                user_email VARCHAR(255) NOT NULL,
                quizid INT,
                schedule_id INT NULL,
                score DECIMAL(5,2) DEFAULT 0,
                total_questions INT DEFAULT 0,
                start_time DATETIME,
                end_time DATETIME NULL,
                status ENUM('in-progress', 'completed', 'abandoned') DEFAULT 'in-progress',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (user_email),
                INDEX (quizid),
                INDEX (schedule_id)
            )";
        
        if ($con->query($createTableSQL)) {
            $message .= "Quiz attempts table created successfully.<br>";
        } else {
            throw new Exception("Error creating quiz_attempts table: " . $con->error);
        }
    } else {
        $message .= "Quiz attempts table already exists.<br>";
        
        // Check if the schedule_id column exists
        $columnCheck = $con->query("SHOW COLUMNS FROM quiz_attempts LIKE 'schedule_id'");
        $columnExists = $columnCheck->num_rows > 0;
        
        if (!$columnExists) {
            // Add the schedule_id column
            $alterTableSQL = "
                ALTER TABLE quiz_attempts 
                ADD COLUMN schedule_id INT NULL AFTER quizid,
                ADD INDEX (schedule_id)";
            
            if ($con->query($alterTableSQL)) {
                $message .= "Added schedule_id column to quiz_attempts table.<br>";
            } else {
                throw new Exception("Error adding schedule_id column: " . $con->error);
            }
        } else {
            $message .= "Schedule_id column already exists in quiz_attempts table.<br>";
        }
    }
    
    // Check if the quiz_responses table exists
    $tableCheck = $con->query("SHOW TABLES LIKE 'quiz_responses'");
    $tableExists = $tableCheck->num_rows > 0;
    
    if (!$tableExists) {
        // Create the quiz_responses table if it doesn't exist
        $createResponsesSQL = "
            CREATE TABLE quiz_responses (
                response_id INT AUTO_INCREMENT PRIMARY KEY,
                attempt_id INT NOT NULL,
                question_id INT NOT NULL,
                user_answer TEXT,
                is_correct BOOLEAN DEFAULT 0,
                response_time DATETIME,
                INDEX (attempt_id),
                INDEX (question_id),
                FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(attempt_id) ON DELETE CASCADE
            )";
        
        if ($con->query($createResponsesSQL)) {
            $message .= "Quiz responses table created successfully.<br>";
        } else {
            throw new Exception("Error creating quiz_responses table: " . $con->error);
        }
    } else {
        $message .= "Quiz responses table already exists.<br>";
    }
    
    // Check if the scheduled_quizzes table exists
    $tableCheck = $con->query("SHOW TABLES LIKE 'scheduled_quizzes'");
    $tableExists = $tableCheck->num_rows > 0;
    
    if (!$tableExists) {
        // Create the scheduled_quizzes table if it doesn't exist
        $createScheduledSQL = "
            CREATE TABLE scheduled_quizzes (
                schedule_id INT AUTO_INCREMENT PRIMARY KEY,
                quizid INT NOT NULL,
                start_time DATETIME NOT NULL,
                end_time DATETIME NOT NULL,
                access_code VARCHAR(20) NOT NULL,
                created_by VARCHAR(255),
                active BOOLEAN DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (quizid),
                INDEX (access_code)
            )";
        
        if ($con->query($createScheduledSQL)) {
            $message .= "Scheduled quizzes table created successfully.<br>";
        } else {
            throw new Exception("Error creating scheduled_quizzes table: " . $con->error);
        }
    } else {
        $message .= "Scheduled quizzes table already exists.<br>";
    }
    
    $success = true;
    
} catch (Exception $e) {
    $message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Database Structure</title>
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
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
        
        .btn:hover {
            background: #2980b9;
        }
        
        code {
            background: #f8f9fa;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: monospace;
        }
        
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <?php include "components/header.php"; ?>

    <div class="container">
        <h1>Update Database Structure</h1>
        
        <?php if($success): ?>
            <div class="alert alert-success">
                <h2>Success!</h2>
                <p><?php echo $message; ?></p>
                <p>Your database structure has been updated successfully.</p>
                <a href="quiz_statistics.php" class="btn">Return to Statistics</a>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <h2>Error</h2>
                <p><?php echo $message; ?></p>
                <p>Please check your database configuration and try again.</p>
                <a href="quiz_statistics.php" class="btn">Return to Statistics</a>
            </div>
        <?php endif; ?>
        
        <div class="alert alert-info">
            <h3>Database Schema Information</h3>
            <p>The following tables are required for quiz statistics:</p>
            
            <h4>quiz_attempts</h4>
            <pre>
CREATE TABLE quiz_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    quizid INT,
    schedule_id INT NULL,
    score DECIMAL(5,2) DEFAULT 0,
    total_questions INT DEFAULT 0,
    start_time DATETIME,
    end_time DATETIME NULL,
    status ENUM('in-progress', 'completed', 'abandoned') DEFAULT 'in-progress',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
            </pre>
            
            <h4>quiz_responses</h4>
            <pre>
CREATE TABLE quiz_responses (
    response_id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    user_answer TEXT,
    is_correct BOOLEAN DEFAULT 0,
    response_time DATETIME
);
            </pre>
            
            <h4>scheduled_quizzes</h4>
            <pre>
CREATE TABLE scheduled_quizzes (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    quizid INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    access_code VARCHAR(20) NOT NULL,
    created_by VARCHAR(255),
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
            </pre>
        </div>
    </div>
</body>
</html> 