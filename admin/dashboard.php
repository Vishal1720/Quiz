<?php
require "../dbconnect.php";

if ($_SESSION['status'] != "loggedin" || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Quiz Management System</title>
    <link rel="stylesheet" href="../css/enhanced-style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 2rem;
        }
        .action-button {
            padding: 15px 25px;
            border: none;
            border-radius: 5px;
            background: #4CAF50;
            color: white;
            cursor: pointer;
            transition: 0.3s;
        }
        .action-button:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <nav>
        <h2>Admin Dashboard</h2>
        <a href="../logout.php">Logout</a>
    </nav>

    <div class="dashboard-container">
        <div style="text-align: center; margin-bottom: 20px; color: #fff;">
            <h2>Welcome, <?php echo htmlspecialchars($email); ?></h2>
        </div>
        <h1>Admin Dashboard</h1>
        
        <div class="stats-grid">
            <?php
            // Get total number of quizzes
            $quiz_count = $con->query("SELECT COUNT(*) as count FROM quizdetails")->fetch_assoc()['count'];
            
            // Get total number of users
            $user_count = $con->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
            
            // Get total number of categories
            $category_count = $con->query("SELECT COUNT(*) as count FROM category")->fetch_assoc()['count'];
            ?>
            
            <div class="stat-card">
                <h3>Total Quizzes</h3>
                <p><?php echo $quiz_count; ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?php echo $user_count; ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Categories</h3>
                <p><?php echo $category_count; ?></p>
            </div>
        </div>

        <div class="action-buttons">
            <a href="manage_quizzes.php" class="action-button">Manage Quizzes</a>
            <a href="quiz_attempts.php" class="action-button">View Quiz Attempts</a>
            <a href="../schedule_quiz.php" class="action-button">Schedule Quiz</a>
            <a href="manage_users.php" class="action-button">Manage Users</a>
            <a href="manage_categories.php" class="action-button">Manage Categories</a>
            <a href="view_statistics.php" class="action-button">View Statistics</a>
        </div>
    </div>
</body>
</html>