<?php
require 'dbconnect.php';

if(!isset($_GET['code'])) {
    header("Location: access_quiz.php");
    exit();
}

// Clean up the code parameter - remove any URL parts if full URL was pasted
$code = preg_replace('/.*code=([a-f0-9]{32}).*/', '$1', $_GET['code']);
if(!preg_match('/^[a-f0-9]{32}$/', $code)) {
    header("Location: access_quiz.php?error=invalid_code");
    exit();
}

// Set timezone to match your server
date_default_timezone_set('Asia/Kolkata'); // Change this to your timezone

// Get current time in server timezone
$now = new DateTime();
$current_time = $now->format('Y-m-d H:i:s');

// Debugging: Log the access code and current time
error_log("Access Code: $code, Current Time: $current_time");

// First check if quiz exists and is scheduled
$stmt = $con->prepare("SELECT sq.*, qd.quizname, qd.category, qd.quizid 
                      FROM scheduled_quizzes sq 
                      JOIN quizdetails qd ON sq.quizid = qd.quizid 
                      WHERE sq.access_code = ?");

if (!$stmt) {
    error_log("Prepare failed: " . $con->error);
    $error = "System error occurred";
} else {
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows === 0) {
        error_log("Quiz not found with code: $code");
        $error = "Invalid quiz link. Please check your URL.";
    } else {
        $quiz = $result->fetch_assoc();
        
        // Convert times to DateTime objects for accurate comparison
        $start = new DateTime($quiz['start_time']);
        $end = new DateTime($quiz['end_time']);
        
        error_log("Time check - Now: " . $now->format('Y-m-d H:i:s') . 
                  ", Start: " . $start->format('Y-m-d H:i:s') . 
                  ", End: " . $end->format('Y-m-d H:i:s'));
        
        if ($now < $start) {
            $error = "This quiz will be available from " . $start->format('F j, Y, g:i a');
        } elseif ($now > $end) {
            $error = "This quiz has ended. It was available until " . $end->format('F j, Y, g:i a');
        } else {
            // Get quiz questions count
            $countStmt = $con->prepare("SELECT COUNT(*) as count FROM quizes WHERE quizid = ?");
            $countStmt->bind_param("i", $quiz['quizid']);
            $countStmt->execute();
            $countResult = $countStmt->get_result()->fetch_assoc();
            $questionCount = $countResult['count'];

            if ($questionCount == 0) {
                error_log("No questions found for quiz ID: " . $quiz['quizid']);
                $error = "This quiz has no questions.";
            }
        }
    }
}
?>

<?php include "components/header.php"; ?>
<style>
    .scheduled-quiz-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        backdrop-filter: blur(10px);
    }
    .quiz-info {
        margin-bottom: 2rem;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
    }
    .start-btn {
        display: inline-block;
        padding: 1rem 2rem;
        background: var(--primary);
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    .start-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(74, 144, 226, 0.3);
    }
</style>

<div class="scheduled-quiz-container">
    <?php if(isset($error)): ?>
        <div class="error-message">
            <h2>Quiz Unavailable</h2>
            <p><?php echo $error; ?></p>
            <a href="index.php" class="start-btn" style="margin-top: 1rem;">Return to Homepage</a>
        </div>
    <?php else: ?>
        <h1><?php echo htmlspecialchars($quiz['quizname']); ?></h1>
        <div class="quiz-info">
            <p><strong>Category:</strong> <?php echo htmlspecialchars($quiz['category']); ?></p>
            <p><strong>Questions:</strong> <?php echo $questionCount; ?></p>
            <p><strong>Available until:</strong> <?php echo date('F j, Y, g:i a', strtotime($quiz['end_time'])); ?></p>
        </div>
        
        <?php if(!isset($_SESSION['status']) || $_SESSION['status'] !== 'loggedin'): ?>
            <p>Please log in to take the quiz.</p>
            <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="start-btn">Login</a>
        <?php else: ?>
            <a href="takequiz.php?quizid=<?php echo $quiz['quizid']; ?>&scheduled=1&code=<?php echo urlencode($code); ?>" class="start-btn">Start Quiz</a>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include "components/footer.php"; ?>
