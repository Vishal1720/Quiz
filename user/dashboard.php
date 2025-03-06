<?php
require "../dbconnect.php";

if ($_SESSION['status'] != "loggedin" || $_SESSION['role'] != "user") {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['email'];
// Get user's name
$query = "SELECT name FROM users WHERE email = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userName = $user['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Quiz Management System</title>
    <link rel="stylesheet" href="../css/enhanced-style.css">
    <link rel="stylesheet" href="../nav.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 20px;
        }
        .welcome-section {
            text-align: center;
            margin-bottom: 2rem;
            color: #fff;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }
        .category-section {
            margin-top: 2rem;
        }
        .category-title {
            color: #fff;
            margin-bottom: 1rem;
            padding: 10px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
        }
        .quiz-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 2rem;
        }
        .quiz-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            color: #fff;
        }
        .quiz-card h3 {
            margin-top: 0;
            margin-bottom: 15px;
        }
        .take-quiz-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .take-quiz-btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <nav>
        <a href="../index.php">Home</a>
        <a href="../logout.php">Logout</a>
        <div class="animation start-home"></div>
    </nav>

    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>Welcome, <?php echo htmlspecialchars($userName); ?>!</h1>
            <p>Choose a category and take a quiz to test your knowledge</p>
        </div>

        <?php
        // Get all categories
        $categoryQuery = "SELECT DISTINCT category FROM quizdetails ORDER BY category";
        $categoryResult = $con->query($categoryQuery);

        while ($category = $categoryResult->fetch_assoc()) {
            $currentCategory = $category['category'];
            echo "<div class='category-section'>";
            echo "<h2 class='category-title'>{$currentCategory}</h2>";
            echo "<div class='quiz-list'>";

            // Get quizzes for current category
            $quizQuery = "SELECT * FROM quizdetails WHERE category = ?";
            $stmt = $con->prepare($quizQuery);
            $stmt->bind_param("s", $currentCategory);
            $stmt->execute();
            $quizResult = $stmt->get_result();

            if ($quizResult->num_rows > 0) {
                while ($quiz = $quizResult->fetch_assoc()) {
                    echo "<div class='quiz-card'>";
                    echo "<h3>" . htmlspecialchars($quiz['quizname']) . "</h3>";
                    echo "<a href='../takequiz.php?quizid=" . $quiz['quizid'] . "' class='take-quiz-btn'>Take Quiz</a>";
                    echo "</div>";
                }
            } else {
                echo "<p>No quizzes available in this category.</p>";
            }

            echo "</div>";
            echo "</div>";
        }

        if ($categoryResult->num_rows == 0) {
            echo "<p style='text-align: center; color: #fff;'>No categories available at the moment.</p>";
        }
        ?>
    </div>
</body>
</html>