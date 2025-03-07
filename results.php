<?php
session_start();
require "dbconnect.php";

if (!isset($_SESSION["status"]) || $_SESSION["status"] !== "loggedin") {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["quizid"]) || !isset($_SESSION["quiz_completed"])) {
    header("Location: index.php");
    exit();
}

$quizid = mysqli_real_escape_string($con, $_GET["quizid"]);

// Get quiz details
$stmt = $con->prepare("SELECT quizname, category FROM quizdetails WHERE quizid = ?");
$stmt->bind_param("i", $quizid);
$stmt->execute();
$quizDetails = $stmt->get_result()->fetch_assoc();

if (!$quizDetails) {
    header("Location: index.php?error=invalid_quiz");
    exit();
}

// Get quiz questions and correct answers
$stmt = $con->prepare("SELECT ID, question, answer FROM quizes WHERE quizid = ?");
$stmt->bind_param("i", $quizid);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate score
$totalQuestions = count($questions);
$score = 0;
$userAnswers = isset($_SESSION["quiz_answers"]) ? $_SESSION["quiz_answers"] : array();

foreach ($questions as $question) {
    if (isset($userAnswers[$question["ID"]]) && $userAnswers[$question["ID"]] === $question["answer"]) {
        $score++;
    }
}

$percentage = ($score / $totalQuestions) * 100;

// Clear quiz session data
unset($_SESSION["quiz_completed"]);
unset($_SESSION["quiz_answers"]);
unset($_SESSION["quiz_start_time"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results</title>
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #357abd;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
            --background-dark: #1a1a2e;
            --text-light: #ffffff;
            --text-muted: #a0a0a0;
            --container-width: 800px;
            --spacing-lg: 2rem;
            --spacing-md: 1.5rem;
            --spacing-sm: 1rem;
        }

        .results-container {
            width: var(--container-width);
            margin: var(--spacing-lg) auto;
            padding: var(--spacing-lg);
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .score-section {
            margin-bottom: var(--spacing-lg);
            padding: var(--spacing-lg);
            background: rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .score-title {
            font-size: clamp(1.5rem, 4vw, 2rem);
            color: var(--text-light);
            margin-bottom: var(--spacing-md);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .score-display {
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: bold;
            background: linear-gradient(135deg, #fff, var(--primary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: var(--spacing-md) 0;
        }

        .score-percentage {
            font-size: clamp(1.2rem, 3vw, 1.5rem);
            color: var(--text-muted);
            margin-bottom: var(--spacing-md);
        }

        .feedback-section {
            margin-top: var(--spacing-lg);
            padding: var(--spacing-md);
        }

        .feedback-message {
            font-size: clamp(1.1rem, 2.5vw, 1.3rem);
            margin-bottom: var(--spacing-md);
            padding: var(--spacing-md);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .excellent {
            color: var(--success-color);
            border-color: rgba(46, 204, 113, 0.3);
            background: rgba(46, 204, 113, 0.1);
        }

        .good {
            color: var(--warning-color);
            border-color: rgba(241, 196, 15, 0.3);
            background: rgba(241, 196, 15, 0.1);
        }

        .needs-improvement {
            color: var(--danger-color);
            border-color: rgba(231, 76, 60, 0.3);
            background: rgba(231, 76, 60, 0.1);
        }

        .action-buttons {
            display: flex;
            gap: var(--spacing-md);
            justify-content: center;
            margin-top: var(--spacing-lg);
        }

        .action-btn {
            padding: var(--spacing-md) var(--spacing-lg);
            border: none;
            border-radius: 8px;
            color: var(--text-light);
            font-size: clamp(0.9rem, 2vw, 1rem);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 150px;
        }

        .primary-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .secondary-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .answers-section {
            margin-top: var(--spacing-lg);
            text-align: left;
        }

        .answer-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-md);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .question-text {
            font-size: clamp(1rem, 2.5vw, 1.1rem);
            color: var(--text-light);
            margin-bottom: var(--spacing-sm);
        }

        .answer-text {
            font-size: clamp(0.9rem, 2vw, 1rem);
            color: var(--text-muted);
            margin-bottom: var(--spacing-sm);
        }

        .correct-answer {
            color: var(--success-color);
        }

        .wrong-answer {
            color: var(--danger-color);
        }

        @media screen and (max-width: 768px) {
            .results-container {
                width: 95%;
                margin: var(--spacing-md) auto;
                padding: var(--spacing-md);
            }

            .score-section {
                padding: var(--spacing-md);
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-btn {
                width: 100%;
            }

            .answer-item {
                padding: var(--spacing-sm);
            }
        }

        @media screen and (max-width: 480px) {
            .score-display {
                font-size: 3rem;
            }

            .feedback-message {
                font-size: 1rem;
                padding: var(--spacing-sm);
            }
        }
    </style>
</head>
<body>
    <nav>
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>
        <div class="animation start-home"></div>
    </nav>

    <div class="results-container">
        <div class="score-section">
            <div class="score-title">Quiz Results</div>
            <div class="score-display">Score: <?php echo $score; ?>/<?php echo $totalQuestions; ?></div>
            <div class="score-percentage"><?php echo number_format($percentage, 1); ?>%</div>
        </div>

        <div class="feedback-section">
            <div class="feedback-message <?php if ($percentage >= 80) echo 'excellent'; elseif ($percentage >= 60) echo 'good'; else echo 'needs-improvement'; ?>">
                <?php
                if ($percentage >= 80) {
                    echo "Excellent! You scored " . number_format($percentage, 1) . "%.";
                } elseif ($percentage >= 60) {
                    echo "Good effort! You scored " . number_format($percentage, 1) . "%.";
                } else {
                    echo "Keep practicing! You scored " . number_format($percentage, 1) . "%.";
                }
                ?>
            </div>
        </div>

        <div class="action-buttons">
            <a href="index.php" class="action-btn primary-btn">Back to Home</a>
        </div>
    </div>
</body>
</html>
