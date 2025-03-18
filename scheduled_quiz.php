<?php
session_start();
require 'dbconnect.php';

// Ensure user is logged in
if (!isset($_SESSION["status"]) || ($_SESSION["status"] !== "loggedin" && $_SESSION["status"] !== "admin")) {
    header("Location: login.php?error=login_required&redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

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
$stmt = $con->prepare("SELECT sq.*, qd.quizname, qd.category, qd.quizid, qd.timer
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
        
        // Format times for display
        $formatted_start_time = $start->format('F j, Y, g:i a');
        $formatted_end_time = $end->format('F j, Y, g:i a');
        
        error_log("Time check - Now: " . $now->format('Y-m-d H:i:s') . 
                  ", Start: " . $start->format('Y-m-d H:i:s') . 
                  ", End: " . $end->format('Y-m-d H:i:s'));
        
        if ($now < $start) {
            // Calculate time difference for countdown
            $timeToStart = $now->diff($start);
            $secondsToStart = $start->getTimestamp() - $now->getTimestamp();
            $waitingForStart = true;
            $error = "This quiz will be available from " . $formatted_start_time;
        } elseif ($now > $end) {
            // Update quiz status to completed if it's past end time
            if ($quiz['status'] !== 'completed') {
                $updateStmt = $con->prepare("UPDATE scheduled_quizzes SET status = 'completed' WHERE access_code = ?");
                $updateStmt->bind_param("s", $code);
                $updateStmt->execute();
            }
            $error = "This quiz has ended. It was available until " . $formatted_end_time;
        } else {
            // Quiz is currently active, update status if needed
            if ($quiz['status'] === 'pending') {
                $updateStmt = $con->prepare("UPDATE scheduled_quizzes SET status = 'active' WHERE access_code = ?");
                $updateStmt->bind_param("s", $code);
                $updateStmt->execute();
                
                // Update the status in our current quiz object too
                $quiz['status'] = 'active';
            }
            
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
    .status-badge {
        display: inline-block;
        padding: 0.3rem 0.7rem;
        border-radius: 5px;
        font-size: 0.85rem;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .status-badge.pending {
        background-color: #ffd700;
        color: #000;
    }
    .status-badge.active {
        background-color: #4CAF50;
        color: #fff;
    }
    .status-badge.completed {
        background-color: #f44336;
        color: #fff;
    }
    .countdown-container {
        margin: 2rem 0;
        text-align: center;
    }
    .countdown {
        font-size: 2.5rem;
        font-weight: bold;
        color: var(--primary);
        margin: 1rem 0;
        padding: 1rem;
        background: rgba(0, 0, 0, 0.3);
        border-radius: 10px;
        display: inline-block;
    }
    .countdown-message {
        font-size: 1.2rem;
        margin-bottom: 1rem;
        color: var(--text-muted);
    }
    .countdown-hint {
        margin-top: 1rem;
        font-style: italic;
        color: var(--text-muted);
    }
    .current-time-display {
        text-align: center;
        margin-top: 1rem;
        color: var(--text-muted);
        font-size: 0.9rem;
    }
    .time-info {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        padding: 0.5rem 1rem;
        margin-top: 1rem;
    }
    .time-info p {
        margin: 0.5rem 0;
    }
    .pulse {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
</style>

<div class="scheduled-quiz-container">
    <?php if(isset($error)): ?>
        <?php if(isset($waitingForStart)): ?>
            <h1><?php echo htmlspecialchars($quiz['quizname']); ?></h1>
            <div class="quiz-info">
                <p><strong>Category:</strong> <?php echo htmlspecialchars($quiz['category']); ?></p>
                <p><strong>Status:</strong> <span class="status-badge pending">Pending</span></p>
                
                <div class="time-info">
                    <p><strong>Available from:</strong> <?php echo $formatted_start_time; ?></p>
                    <p><strong>Available until:</strong> <?php echo $formatted_end_time; ?></p>
                    <?php if($quiz['timer'] > 0): ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="countdown-container">
                <p class="countdown-message">Quiz will be available in:</p>
                <div class="countdown" id="countdown">
                    <span id="hours"><?php echo str_pad($timeToStart->h, 2, '0', STR_PAD_LEFT); ?></span>:
                    <span id="minutes"><?php echo str_pad($timeToStart->i, 2, '0', STR_PAD_LEFT); ?></span>:
                    <span id="seconds"><?php echo str_pad($timeToStart->s, 2, '0', STR_PAD_LEFT); ?></span>
                </div>
                <p class="countdown-hint">This page will automatically refresh when the quiz starts</p>
                <div class="current-time-display">
                    Current time: <span id="current-time"><?php echo $now->format('F j, Y, g:i:s a'); ?></span>
                </div>
            </div>
            
            <?php if(!isset($_SESSION['status']) || $_SESSION['status'] !== 'loggedin'): ?>
                <div style="text-align: center; margin-top: 2rem;">
                    <p>Please log in before the quiz starts.</p>
                    <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="start-btn">Login</a>
                </div>
            <?php endif; ?>
            
            <script>
                // Set the time we're counting down to
                var startTimeStamp = <?php echo $start->getTimestamp() * 1000; ?>;
                
                // Update the countdown every 1 second
                var countdownInterval = setInterval(function() {
                    // Get current time
                    var now = new Date();
                    var nowTime = now.getTime();
                    
                    // Update current time display
                    document.getElementById("current-time").textContent = 
                        now.toLocaleString('en-US', { 
                            month: 'long', 
                            day: 'numeric', 
                            year: 'numeric',
                            hour: 'numeric',
                            minute: 'numeric',
                            second: 'numeric',
                            hour12: true
                        });
                    
                    // Calculate time remaining
                    var distance = startTimeStamp - nowTime;
                    
                    // If time is up, refresh the page
                    if (distance <= 0) {
                        clearInterval(countdownInterval);
                        document.getElementById("countdown").innerHTML = "Starting...";
                        location.reload();
                        return;
                    }
                    
                    // Calculate hours, minutes, and seconds
                    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    // Format with leading zeros
                    hours = hours.toString().padStart(2, '0');
                    minutes = minutes.toString().padStart(2, '0');
                    seconds = seconds.toString().padStart(2, '0');
                    
                    // Display the countdown
                    document.getElementById("hours").textContent = hours;
                    document.getElementById("minutes").textContent = minutes;
                    document.getElementById("seconds").textContent = seconds;
                    
                    // If less than 1 minute remaining, add pulse effect
                    if (distance < 60000) { // 60 seconds
                        document.getElementById("countdown").classList.add("pulse");
                    }
                    
                    // Schedule page refresh exactly at start time
                    if (distance <= 1000) { // if less than 1 second remaining
                        setTimeout(function() {
                            location.reload();
                        }, distance);
                    }
                    
                }, 1000);
                
                // Set a backup refresh in case JavaScript timing is slightly off
                var msToStart = <?php echo $secondsToStart * 1000; ?>;
                if (msToStart > 0) {
                    setTimeout(function() {
                        location.reload();
                    }, msToStart + 500); // Add a small buffer
                }
            </script>
        <?php else: ?>
            <div class="error-message">
                <h2>Quiz Unavailable</h2>
                <p><?php echo $error; ?></p>
                <a href="index.php" class="start-btn" style="margin-top: 1rem;">Return to Homepage</a>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <h1><?php echo htmlspecialchars($quiz['quizname']); ?></h1>
        <div class="quiz-info">
            <p><strong>Category:</strong> <?php echo htmlspecialchars($quiz['category']); ?></p>
            <p><strong>Status:</strong> <span class="status-badge active">Active</span></p>
            <p><strong>Questions:</strong> <?php echo $questionCount; ?></p>
            
            <div class="time-info">
                <p><strong>Available until:</strong> <?php echo $formatted_end_time; ?></p>
                <?php if($quiz['timer'] > 0): ?>
                <p><strong>Time Limit:</strong> <?php echo $quiz['timer']; ?> minutes</p>
                <?php endif; ?>
                <p><strong>Current time:</strong> <?php echo $now->format('F j, Y, g:i a'); ?></p>
            </div>
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
