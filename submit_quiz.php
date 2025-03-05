<?php
include "dbconnect.php";

if ($_SESSION['status'] == "loggedout" || $_SESSION['status'] == "" || empty($_SESSION['status'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['quizid']) || !isset($_POST['answer'])) {
    header("Location: index.php");
    exit();
}

$quizid = $_POST['quizid'];
$userAnswers = $_POST['answer'];

// Fetch quiz details
$query = "SELECT q.*, qd.quizname, qd.category FROM quizes q 
JOIN quizdetails qd ON q.quizid = qd.quizid 
WHERE q.quizid = ?";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $quizid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$quizName = "";
$category = "";
$questions = array();
$score = 0;
$totalQuestions = 0;

while ($row = $result->fetch_assoc()) {
    if (empty($quizName)) {
        $quizName = $row['quizname'];
        $category = $row['category'];
    }
    $questions[$row['ID']] = $row;
    $totalQuestions++;

    $selectedAnswer = $userAnswers[$row['ID']] ?? '';
    $correctAnswer = $row['answer'];
    if ($selectedAnswer === $correctAnswer) {
        $score++;
    }
}

$percentage = ($score / $totalQuestions) * 100;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - <?php echo htmlspecialchars($quizName); ?></title>
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="css/enhanced-style.css">
    <style>
        .results-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .results-header {
            text-align: center;
            margin-bottom: 2rem;
            color: #fff;
        }

        .score-card {
            background: rgba(255, 255, 255, 0.15);
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 2rem;
        }

        .score-title {
            font-size: 1.5rem;
            color: #fff;
            margin-bottom: 1rem;
        }

        .score-value {
            font-size: 3rem;
            font-weight: bold;
            color: #4a90e2;
            margin-bottom: 0.5rem;
        }

        .score-details {
            color: rgba(255, 255, 255, 0.8);
        }

        .question-review {
            margin-top: 2rem;
        }

        .question-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .question-text {
            color: #fff;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .answer-status {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .correct {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }

        .incorrect {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }

        .answer-detail {
            color: rgba(255, 255, 255, 0.8);
            margin: 0.5rem 0;
        }

        .back-btn {
            display: block;
            width: 100%;
            padding: 1rem;
            background: #4a90e2;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 1.1rem;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-top: 2rem;
        }

        .back-btn:hover {
            background: #357abd;
            transform: scale(1.02);
        }
    </style>
</head>
<body>
    <nav>
        <a href="./index.php">Home</a>
        <a href="./quizmanip.php">Edit</a>
        <a href="./createquiz.php">Create</a>
        <a href="./quizform.php">Insert</a>
        <a style="width:100px" href="./logout.php">Logout</a>
        <div class="animation start-home"></div>
    </nav>

    <div class="results-container">
        <div class="results-header">
            <h1><?php echo htmlspecialchars($quizName); ?> Results</h1>
            <p>Category: <?php echo htmlspecialchars($category); ?></p>
        </div>

        <div class="score-card">
            <h2 class="score-title">Your Score</h2>
            <div class="score-value"><?php echo number_format($percentage, 1); ?>%</div>
            <p class="score-details"><?php echo $score; ?> correct out of <?php echo $totalQuestions; ?> questions</p>
        </div>

        <div class="question-review">
            <h2 style="color: #fff; margin-bottom: 1.5rem;">Question Review</h2>
            <?php foreach ($questions as $id => $question): ?>
                <div class="question-card">
                    <p class="question-text"><?php echo htmlspecialchars($question['question']); ?></p>
                    <?php
                    $isCorrect = isset($userAnswers[$id]) && $userAnswers[$id] === $question['answer'];
                    $statusClass = $isCorrect ? 'correct' : 'incorrect';
                    $statusText = $isCorrect ? 'Correct' : 'Incorrect';
                    ?>
                    <div class="answer-status <?php echo $statusClass; ?>">
                        <?php echo $statusText; ?>
                    </div>
                    <p class="answer-detail">
                        Your answer: <?php echo isset($userAnswers[$id]) ? htmlspecialchars($userAnswers[$id]) : 'Not answered'; ?>
                    </p>
                    <?php if (!$isCorrect): ?>
                        <p class="answer-detail">
                            Correct answer: <?php echo htmlspecialchars($question['answer']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <a href="index.php" class="back-btn">Back to Dashboard</a>
    </div>
</body>
</html>