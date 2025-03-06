<?php
require "../dbconnect.php";

if ($_SESSION['status'] != "loggedin" || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['email'];

// Handle quiz deletion
if (isset($_POST['delete_quiz'])) {
    $quiz_id = $_POST['quiz_id'];
    $delete_query = "DELETE FROM quizdetails WHERE quizid = ?";
    $stmt = $con->prepare($delete_query);
    $stmt->bind_param("i", $quiz_id);
    if ($stmt->execute()) {
        echo "<script>alert('Quiz deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting quiz!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quizzes - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/enhanced-style.css">
    <style>
        .quiz-management {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 20px;
        }
        .quiz-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 2rem;
        }
        .quiz-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
        }
        .quiz-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn-edit {
            background: #4CAF50;
            color: white;
        }
        .btn-delete {
            background: #f44336;
            color: white;
        }
        .btn-view {
            background: #2196F3;
            color: white;
        }
    </style>
</head>
<body>
    <nav>
        <h2>Quiz Management</h2>
        <a href="dashboard.php">Back to Dashboard</a>
    </nav>

    <div class="quiz-management">
        <h1>Manage Quizzes</h1>
        <a href="create_quiz.php" class="btn btn-edit">Create New Quiz</a>

        <div class="quiz-grid">
            <?php
            $query = "SELECT qd.*, c.category_name, 
                     (SELECT COUNT(*) FROM quizes WHERE quizid = qd.quizid) as question_count 
                     FROM quizdetails qd 
                     JOIN category c ON qd.category = c.category_id";
            $result = $con->query($query);

            while ($quiz = $result->fetch_assoc()) {
                echo "<div class='quiz-card'>";
                echo "<h3>{$quiz['quizname']}</h3>";
                echo "<p>Category: {$quiz['category_name']}</p>";
                echo "<p>Questions: {$quiz['question_count']}</p>";
                echo "<div class='quiz-actions'>";
                echo "<a href='edit_quiz.php?id={$quiz['quizid']}' class='btn btn-edit'>Edit</a>";
                echo "<a href='view_quiz.php?id={$quiz['quizid']}' class='btn btn-view'>View</a>";
                echo "<form method='POST' style='display: inline;' onsubmit='return confirm(\"Are you sure you want to delete this quiz?\");'>";
                echo "<input type='hidden' name='quiz_id' value='{$quiz['quizid']}'>";
                echo "<button type='submit' name='delete_quiz' class='btn btn-delete'>Delete</button>";
                echo "</form>";
                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>
    </div>
</body>
</html>