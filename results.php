<?php
session_start();
require "dbconnect.php";

if (!isset($_SESSION["status"]) || ($_SESSION["status"] !== "loggedin" && $_SESSION["status"] !== "admin")) {
    header("Location: login.php?error=login_required&redirect=" . urlencode($_SERVER['REQUEST_URI']));
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
if($totalQuestions == 0)
    {
        header("Location:index.php?error=no_questions");     
    }
$percentage = ($score / $totalQuestions) * 100;

// Check if this was a scheduled quiz
$wasScheduledQuiz = isset($_SESSION['is_scheduled_quiz']) && $_SESSION['is_scheduled_quiz'] === true;

// Clear quiz session data
unset($_SESSION["quiz_completed"]);
unset($_SESSION["quiz_answers"]);
unset($_SESSION["quiz_start_time"]);
unset($_SESSION["is_scheduled_quiz"]);
unset($_SESSION["scheduled_end_time"]);
unset($_SESSION["scheduled_start_time"]);
unset($_SESSION["scheduled_total_duration"]);

// After calculating the score and before displaying results
if (!isset($_SESSION['quiz_attempt_recorded'])) {
    // Get the current user's email and name
    $user_email = $_SESSION['email'];
    $quiz_id = $_GET["quizid"];
    
    // Check if the quiz was scheduled
    $was_scheduled_quiz = isset($_SESSION['is_scheduled_quiz']) && $_SESSION['is_scheduled_quiz'] === true;
    $schedule_id = isset($_SESSION['scheduled_quiz_id']) ? $_SESSION['scheduled_quiz_id'] : null;
    
    // Calculate the score percentage
    $score_percentage = ($score / $totalQuestions) * 100;
    
    // Prepare current date/time for database
    $current_time = date('Y-m-d H:i:s');
    $start_time = isset($_SESSION['actual_quiz_start_time']) ? 
                  date('Y-m-d H:i:s', $_SESSION['actual_quiz_start_time']) : 
                  date('Y-m-d H:i:s', strtotime('-30 minutes')); // Fallback
    
    try {
        // Insert the quiz attempt
        $stmt = $con->prepare("INSERT INTO quiz_attempts (user_email, quizid, schedule_id, score, total_questions, start_time, end_time, status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, 'completed')");
        $stmt->bind_param("siidiss", $user_email, $quiz_id, $schedule_id, $score_percentage, $totalQuestions, $start_time, $current_time);
        $stmt->execute();
        $attempt_id = $con->insert_id;
        
        // Record individual question responses
        if (isset($userAnswers) && is_array($userAnswers)) {
            $response_stmt = $con->prepare("INSERT INTO quiz_responses (attempt_id, question_id, user_answer, is_correct) 
                                           VALUES (?, ?, ?, ?)");
            
            foreach ($questions as $question) {
                $question_id = $question['ID'];
                $user_answer = isset($userAnswers[$question_id]) ? $userAnswers[$question_id] : '';
                $is_correct = ($user_answer === $question['answer']) ? 1 : 0;
                
                $response_stmt->bind_param("iisi", $attempt_id, $question_id, $user_answer, $is_correct);
                $response_stmt->execute();
            }
        }
        
        // Mark that we've recorded this attempt to prevent duplicates
        $_SESSION['quiz_attempt_recorded'] = true;
        
        // Log success
        error_log("Quiz attempt recorded successfully. ID: $attempt_id, User: $user_email, Score: $score_percentage%");
    } catch (Exception $e) {
        // Log error but continue showing results
        error_log("Error recording quiz attempt: " . $e->getMessage());
    }
}
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
            background-clip: text;
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
            background: rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 100%;
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }

        .answer-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.1);
        }

        .question-text {
            font-size: 1.2rem;
            color: var(--text-light);
            margin-bottom: var(--spacing-md);
            font-weight: 500;
            line-height: 1.4;
            padding-bottom: var(--spacing-md);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .answer-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-md);
            margin-top: var(--spacing-md);
        }

        .answer-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            display: block;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .user-answer, .correct-answer {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .user-answer {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-answer.correct {
            background: rgba(46, 204, 113, 0.15);
            border-color: rgba(46, 204, 113, 0.3);
            color: var(--success-color);
            box-shadow: 0 0 15px rgba(46, 204, 113, 0.1);
        }

        .user-answer.incorrect {
            background: rgba(231, 76, 60, 0.15);
            border-color: rgba(231, 76, 60, 0.3);
            color: var(--danger-color);
            box-shadow: 0 0 15px rgba(231, 76, 60, 0.1);
        }

        .correct-answer {
            background: rgba(46, 204, 113, 0.15);
            border: 1px solid rgba(46, 204, 113, 0.3);
            color: var(--success-color);
            box-shadow: 0 0 15px rgba(46, 204, 113, 0.1);
        }
    </style>
</head>
<body>
    <nav>
        <a href="./index.php">Home</a>
        <a style="width:100px" href="./logout.php">Logout</a>
        <div class="animation start-home"></div>
    </nav>
    <div class="results-container">
        <div class="score-section">
            <h1 class="score-title">Quiz Results</h1>
            <div class="score-display"><?php echo $score; ?>/<?php echo $totalQuestions; ?></div>
            <div class="score-percentage"><?php echo number_format($percentage, 1); ?>%</div>
            <div class="feedback-message <?php 
                if ($percentage >= 80) echo 'excellent';
                elseif ($percentage >= 60) echo 'good';
                else echo 'needs-improvement';
            ?>">
                <?php
                if ($percentage >= 80) echo "Excellent! You scored {$percentage}%.";
                elseif ($percentage >= 60) echo "Good job! You scored {$percentage}%.";
                else echo "Keep practicing! You scored {$percentage}%.";
                ?>
            </div>
        </div>

        <div class="answers-section">
            <h2 style="color: var(--text-light); margin-bottom: var(--spacing-md);">Review Your Answers</h2>
            <?php foreach ($questions as $index => $question): ?>
                <div class="answer-item">
                    
                    <div class="question-text"><?php $ques=$question['question'];
                    $ques=$question["question"];
                        $ques=str_replace("\'","'",$ques);
                        $ques=str_replace('\"','"',$ques);
                         echo ($index + 1) . ". " . htmlspecialchars($ques); ?></div>
                    <div class="answer-details">
                        <div class="answer-card">
                            <span class="answer-label">Your Answer:</span>
                            <div class="user-answer <?php echo (isset($userAnswers[$question["ID"]]) && $userAnswers[$question["ID"]] === $question["answer"]) ? 'correct' : 'incorrect'; ?>">
                                <?php echo isset($userAnswers[$question["ID"]]) ? htmlspecialchars($userAnswers[$question["ID"]]) : 'Not answered'; ?>
                            </div>
                        </div>
                        <div class="answer-card">
                            <span class="answer-label">Correct Answer:</span>
                            <div class="correct-answer">
                                <?php echo htmlspecialchars($question["answer"]); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="action-buttons">
            <a href="index.php" class="action-btn primary-btn">Back to Home</a>
            <?php if(!$wasScheduledQuiz): ?>
            <a href="takequiz.php?quizid=<?php echo $quizid; ?>" class="action-btn secondary-btn">Try Again</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
