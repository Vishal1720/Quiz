<?php
session_start();
require "dbconnect.php";

// Check login status
if (!isset($_SESSION['status']) || $_SESSION['status'] !== "loggedin" && $_SESSION['status'] !== "admin") {
    // Redirect non-logged in users
    header("Location: login.php?error=login_required&redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Add near the top of the file after session checks
$difficulty = $_GET['difficulty'] ?? 'beginner';
$quizid = $_GET['quizid'] ?? '';

if(!$quizid || !$difficulty) {
    header("Location: index.php");
    exit();
}

// Check if quiz ID is provided
if (!isset($_GET['quizid'])) {
    header("Location: index.php");
    exit();
}

$quizid = mysqli_real_escape_string($con, $_GET['quizid']);

// Check scheduled quiz access
if (isset($_GET['scheduled']) && isset($_GET['code'])) {
    $code = mysqli_real_escape_string($con, $_GET['code']);
    $scheduleCheck = $con->prepare("SELECT sq.*, qd.quizname 
                                  FROM scheduled_quizzes sq 
                                  JOIN quizdetails qd ON sq.quizid = qd.quizid 
                                  WHERE sq.access_code = ? 
                                  AND sq.quizid = ? 
                                  AND sq.start_time <= NOW() 
                                  AND sq.end_time >= NOW()");
                                  
    if (!$scheduleCheck) {
        error_log("Failed to prepare schedule check: " . $con->error);
        header("Location: index.php?error=system_error");
        exit();
    }

    $scheduleCheck->bind_param("si", $code, $quizid);
    $scheduleCheck->execute();
    $scheduleResult = $scheduleCheck->get_result();
    
    if ($scheduleResult->num_rows === 0) {
        error_log("Scheduled quiz access denied - Code: $code, Quiz ID: $quizid");
        header("Location: scheduled_quiz.php?code=$code&error=not_available");
        exit();
    }

    // Get the scheduled quiz details
    $scheduledQuiz = $scheduleResult->fetch_assoc();
    $_SESSION['is_scheduled_quiz'] = true;
    $_SESSION['scheduled_end_time'] = strtotime($scheduledQuiz['end_time']);
    $_SESSION['scheduled_start_time'] = strtotime($scheduledQuiz['start_time']);
    $_SESSION['scheduled_quiz_id'] = $scheduledQuiz['schedule_id'];
    
    // Calculate and store the total scheduled duration (in seconds)
    $_SESSION['scheduled_total_duration'] = $_SESSION['scheduled_end_time'] - $_SESSION['scheduled_start_time'];
    
    // Cap the total duration to a reasonable maximum (e.g., 8 hours = 28800 seconds)
    if ($_SESSION['scheduled_total_duration'] > 28800) {
        $_SESSION['scheduled_total_duration'] = 28800;
    }
    
    // Important: For scheduled quizzes, we do NOT reset quiz_start_time on reload
    // Only set it if it's not already set or if it's a different quiz
    if (!isset($_SESSION['quiz_start_time']) || 
        !isset($_SESSION['current_quiz_id']) || 
        $_SESSION['current_quiz_id'] != $quizid) {
        // Initialize quiz start time for first time access
        $_SESSION['quiz_start_time'] = time();
        
        // Store additional tracking data for this scheduled quiz
        $_SESSION['actual_quiz_start_time'] = time(); // When user actually started the quiz
        
        // Log the initial quiz access
        error_log("User started scheduled quiz. QuizID: $quizid, Code: $code, Start time: " . 
                 date('Y-m-d H:i:s', $_SESSION['quiz_start_time']));
    }
    
    // Log the values for debugging
    error_log("Scheduled quiz: Start=" . date('Y-m-d H:i:s', $_SESSION['scheduled_start_time']) . 
              ", End=" . date('Y-m-d H:i:s', $_SESSION['scheduled_end_time']) . 
              ", Total Duration=" . ($_SESSION['scheduled_total_duration'] / 60) . " minutes" .
              ", User Start=" . date('Y-m-d H:i:s', $_SESSION['quiz_start_time']));

    // Update quiz status
    $updateStmt = $con->prepare("UPDATE scheduled_quizzes SET status = 'active' WHERE access_code = ?");
    $updateStmt->bind_param("s", $code);
    $updateStmt->execute();
} else {
    // Not a scheduled quiz
    $_SESSION['is_scheduled_quiz'] = false;
    unset($_SESSION['scheduled_end_time']);
    unset($_SESSION['scheduled_start_time']);
    unset($_SESSION['scheduled_total_duration']);
    unset($_SESSION['actual_quiz_start_time']);
    
    // For regular quizzes, reset timer only if quiz hasn't been started or is a different quiz
    if (!isset($_SESSION['quiz_start_time']) || 
        !isset($_SESSION['current_quiz_id']) || 
        $_SESSION['current_quiz_id'] != $quizid) {
        $_SESSION['quiz_start_time'] = time();
    }
}

// Store current quiz ID in session
$_SESSION['current_quiz_id'] = $quizid;

// Get quiz details
$stmt = $con->prepare("SELECT qd.*, q.* FROM quizdetails qd 
                      LEFT JOIN quizes q ON qd.quizid = q.quizid 
                      WHERE qd.quizid = ?");
$stmt->bind_param("i", $quizid);
$stmt->execute();
$quizDetails = $stmt->get_result()->fetch_assoc();

// Add these lines to get quiz name and category
$quizName = $quizDetails['quizname'] ?? 'Untitled Quiz';
$category = $quizDetails['category'] ?? 'Uncategorized';

if (!$quizDetails) {
    header("Location: index.php?error=invalid_quiz");
    exit();
}

// Calculate remaining time based on the original quiz start time - this ensures
// the timer doesn't reset on page refresh/reload
$originalStartTime = $_SESSION['quiz_start_time'];

// Check if this is a scheduled quiz with a specific end time
if ($_SESSION['is_scheduled_quiz'] && isset($_SESSION['scheduled_end_time'])) {
    // Calculate remaining time based on scheduled end time
    $timeRemainingSeconds = $_SESSION['scheduled_end_time'] - time();
    
    // Get the total scheduled duration (end time - start time)
    $totalScheduledDuration = $_SESSION['scheduled_end_time'] - $_SESSION['scheduled_start_time'];
    $totalScheduledMinutes = ceil($totalScheduledDuration / 60);
    
    // Calculate elapsed time from scheduled start, not from when user started
    $elapsedFromScheduledStart = time() - $_SESSION['scheduled_start_time'];
    
    // Calculate percentage of scheduled time used (100% means we're at the end time)
    $percentOfTimeUsed = min(100, round(($elapsedFromScheduledStart / $totalScheduledDuration) * 100));
    
    // Log time calculation details for debugging
    error_log("Time calc: end_time=" . $_SESSION['scheduled_end_time'] . 
              ", current=" . time() . 
              ", diff=" . $timeRemainingSeconds . 
              " seconds, percent used=" . $percentOfTimeUsed . "%");
    
    // Ensure time is never negative - important for JavaScript timer
    $timeRemainingSeconds = max(0, $timeRemainingSeconds);
    
    // Convert seconds to minutes (rounded up to give full minutes)
    $scheduledMinutes = ceil($timeRemainingSeconds / 60);
    
    // Ensure there's at least 1 second left, or end the quiz
    if ($timeRemainingSeconds <= 0) {
        $_SESSION['quiz_completed'] = true;
        $_SESSION['quiz_answers'] = isset($_POST['answers']) ? $_POST['answers'] : array();
        unset($_SESSION['quiz_start_time']);
        header("Location: results.php?quizid=" . $quizid);
        exit();
    }
    
    // Set duration to the scheduled end time
    $_SESSION['quiz_duration'] = $scheduledMinutes;
    $_SESSION['total_quiz_duration'] = $totalScheduledMinutes;
    
    echo "<!-- DEBUG: Scheduled quiz with " . $scheduledMinutes . " minutes remaining out of total " . 
         $totalScheduledMinutes . " minutes. Percent of time used: " . $percentOfTimeUsed . "% -->";
    
    // For scheduled quizzes, time remaining is based on end time, not quiz duration
    $timeRemaining = $timeRemainingSeconds;
    
    // Store percentage for JavaScript to use
    $_SESSION['percent_time_used'] = $percentOfTimeUsed;
} else {
    // Use the standard quiz timer
    $dbTimer = intval($quizDetails['timer']);
    
    // If timer is 0 or not set, use 30 minutes as fallback
    if ($dbTimer <= 0) {
        $_SESSION['quiz_duration'] = 30; // Default 30 minutes
        $_SESSION['total_quiz_duration'] = 30;
        echo "<!-- DEBUG: Timer was 0 or negative, using default 30 minutes -->";
    } else {
        $_SESSION['quiz_duration'] = $dbTimer;
        $_SESSION['total_quiz_duration'] = $dbTimer;
    }
    
    // Convert minutes to seconds for consistent handling with scheduled quizzes
    $quizDuration = $_SESSION['quiz_duration'] * 60;
    
    // Calculate time remaining based on the original start time
    $timeRemaining = $quizDuration - (time() - $originalStartTime);
    
    // Ensure time is never negative
    $timeRemaining = max(0, $timeRemaining);
}

// Force debug output to browser for troubleshooting
echo "<!-- DEBUG: Quiz ID: {$quizid}, DB Timer Value: {$quizDetails['timer']}, " . 
     "Using: {$_SESSION['quiz_duration']} minutes, " . 
     "Original start: " . date('Y-m-d H:i:s', $originalStartTime) . ", " .
     "Current time: " . date('Y-m-d H:i:s', time()) . ", " .
     "Time remaining: " . floor($timeRemaining/60) . " minutes " . ($timeRemaining % 60) . " seconds -->";

// If time's up, save the current answers and redirect to results
if ($timeRemaining <= 0) {
    $_SESSION['quiz_completed'] = true;
    $_SESSION['quiz_answers'] = isset($_POST['answers']) ? $_POST['answers'] : array();
    unset($_SESSION['quiz_start_time']);
    header("Location: results.php?quizid=" . $quizid);
    exit();
}

// At the start of quiz loading...
function getUniqueQuestions($con, $quizid, $user_email, $difficulty_level, $count) {
    // Get questions user hasn't answered yet for this difficulty
    $query = "SELECT q.* FROM quizes q 
              WHERE q.quizid = ? 
              AND q.difficulty_level = ?
              AND q.ID NOT IN (
                  SELECT question_id 
                  FROM user_quiz_history 
                  WHERE user_email = ? 
                  AND quizid = ?
              )
              ORDER BY RAND() 
              LIMIT ?";
              
    $stmt = $con->prepare($query);
    $stmt->bind_param("issis", $quizid, $difficulty_level, $user_email, $quizid, $count);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Add this after the session checks at the beginning of the file
function getQuestionsForDifficulty($con, $quizid, $difficulty, $count) {
    try {
        // First check if user_quiz_history table exists
        $tableCheck = $con->query("SHOW TABLES LIKE 'user_quiz_history'");
        
        if ($tableCheck->num_rows > 0) {
            // If table exists, use the original query
            $stmt = $con->prepare("
                SELECT q.* FROM quizes q 
                LEFT JOIN user_quiz_history h ON q.ID = h.question_id 
                    AND h.user_email = ? 
                    AND h.quizid = ?
                WHERE q.quizid = ? 
                AND q.difficulty_level = ?
                AND h.question_id IS NULL
                ORDER BY RAND() 
                LIMIT ?
            ");
            
            $stmt->bind_param("siisi", $_SESSION['email'], $quizid, $quizid, $difficulty, $count);
        } else {
            // If table doesn't exist, just get random questions
            $stmt = $con->prepare("
                SELECT * FROM quizes 
                WHERE quizid = ? 
                AND difficulty_level = ?
                ORDER BY RAND() 
                LIMIT ?
            ");
            
            $stmt->bind_param("isi", $quizid, $difficulty, $count);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error in getQuestionsForDifficulty: " . $e->getMessage());
        // Fallback - return empty array if there's an error
        return array();
    }
}

// Get questions for each difficulty level
$questions = [];
$questionsPerDifficulty = 10; // Changed from 5 to 10

// Use the difficulty from URL parameter if provided
if (isset($_GET['difficulty'])) {
    $difficulty = $_GET['difficulty'];
    // Map the URL parameter to the database difficulty levels
    $difficultyMap = [
        'easy' => 'easy',
        'medium' => 'medium',
        'intermediate' => 'intermediate',
        'hard' => 'hard',
        'beginner' => 'easy'
    ];
    
    if (isset($difficultyMap[$difficulty])) {
        $dbDifficulty = $difficultyMap[$difficulty];
        $difficultyQuestions = getQuestionsForDifficulty($con, $quizid, $dbDifficulty, $questionsPerDifficulty);
        $questions = array_merge($questions, $difficultyQuestions);
    }
} else {
    // Fallback to getting a mix of questions if no difficulty specified
    // Exclude 'easy' difficulty from the fallback
    $difficulties = ['medium', 'intermediate', 'hard'];
    $questionsPerType = floor($questionsPerDifficulty / count($difficulties));
    
    foreach ($difficulties as $difficulty) {
        $difficultyQuestions = getQuestionsForDifficulty($con, $quizid, $difficulty, $questionsPerType);
        $questions = array_merge($questions, $difficultyQuestions);
    }
}

// If we don't have enough questions, get any remaining questions regardless of previous attempts
if (count($questions) < $questionsPerDifficulty) {
    $remaining = $questionsPerDifficulty - count($questions);
    $stmt = $con->prepare("
        SELECT * FROM quizes 
        WHERE quizid = ? 
        AND ID NOT IN (" . 
        (count($questions) > 0 ? "SELECT ID FROM quizes WHERE ID IN (" . 
        implode(',', array_column($questions, 'ID')) . ")" : "0") . ")
        ORDER BY RAND() 
        LIMIT ?
    ");
    $stmt->bind_param("ii", $quizid, $remaining);
    $stmt->execute();
    $remainingQuestions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $questions = array_merge($questions, $remainingQuestions);
}

// Limit to exactly 10 questions
if (count($questions) > $questionsPerDifficulty) {
    $questions = array_slice($questions, 0, $questionsPerDifficulty);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($timeRemaining <= 0) {
        $_SESSION['quiz_completed'] = true;
        unset($_SESSION['quiz_start_time']);
        header("Location: results.php?quizid=" . $quizid);
        exit();
    }
    
    // Save answers and redirect to results
    $_SESSION['quiz_completed'] = true;
    $_SESSION['quiz_answers'] = isset($_POST['answers']) ? $_POST['answers'] : array();
    unset($_SESSION['quiz_start_time']);
    header("Location: results.php?quizid=" . $quizid);
    exit();
}
?>
<?php include "components/header.php"; ?>
    <style>
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background-clip: padding-box;
            height: 4rem;
            background: rgba(15, 23, 42, 0.98);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        nav a {
            height: calc(4rem - 1rem);
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            position: relative;
            text-decoration: none;
            text-transform: uppercase;
            text-align: center;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0 1.5rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            border-radius: 8px;
            margin: 0.5rem 0;
            background: rgba(255, 255, 255, 0.05);
        }

        nav a:hover {
            color: var(--text-light);
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        nav a:last-child {
            margin-left: auto;
        }

        :root {
            --primary-color: #4a90e2;
            --secondary-color: #357abd;
            --background-dark: #1a1a2e;
            --text-light: #ffffff;
            --text-muted: #a0a0a0;
            --container-width: 800px;
            --spacing-lg: 2rem;
            --spacing-md: 1.5rem;
            --spacing-sm: 1rem;
        }

        .quiz-container {
            width: var(--container-width);
            margin: var(--spacing-lg) auto;
            padding: var(--spacing-lg);
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .question-card {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
        }

        .question-card:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.1);
        }

        .question-text {
            font-size: clamp(1.1rem, 2.5vw, 1.3rem);
            color: #fff;
            margin-bottom: var(--spacing-md);
            line-height: 1.5;
        }

        .options-list {
            list-style: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-md);
        }

        .option-item {
            margin-bottom: var(--spacing-sm);
            position: relative;
        }

        .radio-input {
            display: none;
        }

        .option-label {
            display: block;
            padding: var(--spacing-md);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: clamp(0.9rem, 2vw, 1rem);
        }

        .radio-input:checked + .option-label {
            background: rgba(74, 144, 226, 0.3);
            border-color: #4a90e2;
            box-shadow: 0 0 15px rgba(74, 144, 226, 0.2);
        }

        .option-label:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .submit-btn {
            display: block;
            width: 100%;
            max-width: 300px;
            margin: var(--spacing-lg) auto 0;
            padding: var(--spacing-md);
            background: linear-gradient(135deg, #4a90e2, #357abd);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: clamp(1rem, 2.5vw, 1.1rem);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.3);
            background: linear-gradient(135deg, #357abd, #4a90e2);
        }

        .quiz-header {
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 2rem;
            background: rgba(15, 23, 42, 0.6);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .quiz-header h1 {
            font-size: 2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        .quiz-header p {
            color: var(--text-muted);
            font-size: 1.1rem;
            margin: 0;
        }

        .quiz-info {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-top: 1.5rem;
            padding: 1rem 2rem;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            min-width: 280px;
            justify-content: center;
            backdrop-filter: blur(5px);
        }

        .timer {
            padding: 0.5rem 1rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-right: 1rem;
            transition: all 0.3s ease;
        }
        
        .timer i {
            color: var(--primary-color);
            font-size: 1.2rem;
        }
        
        .timer.warning {
            background: rgba(220, 53, 69, 0.2);
            animation: pulse 1s infinite;
            color: #fff;
        }
        
        .timer.warning i {
            color: #dc3545;
        }
        
        .scheduled-time {
            padding: 0.5rem 1rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-left: 0.5rem;
            color: #ffc107;
        }
        
        .scheduled-time i {
            color: #ffc107;
            font-size: 1.2rem;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        @media screen and (max-width: 768px) {
            .quiz-container {
                width: 95%;
                margin: 2rem auto;
                padding: var(--spacing-md);
            }

            .quiz-header {
                padding: 1rem;
            }

            .quiz-info {
                padding: 0.5rem 1rem;
                gap: 1rem;
                flex-direction: column;
            }

            .timer {
                font-size: 1rem;
            }

            .quiz-header h1 {
                font-size: 1.5rem;
            }

            .quiz-header p {
                font-size: 1rem;
            }

            .question-card {
                padding: var(--spacing-md);
                margin-bottom: var(--spacing-md);
            }

            .options-list {
                grid-template-columns: 1fr;
            }

            .timer {
                top: auto;
                bottom: var(--spacing-md);
                right: var(--spacing-md);
                font-size: 1rem;
                padding: var(--spacing-sm) var(--spacing-md);
            }
        }

        .difficulty-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            margin-left: 10px;
            text-transform: capitalize;
        }

        .difficulty-badge.easy {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid rgba(46, 204, 113, 0.3);
        }

        .difficulty-badge.medium {
            background: rgba(241, 196, 15, 0.2);
            color: #f1c40f;
            border: 1px solid rgba(241, 196, 15, 0.3);
        }

        .difficulty-badge.intermediate {
            background: rgba(230, 126, 34, 0.2);
            color: #e67e22;
            border: 1px solid rgba(230, 126, 34, 0.3);
        }

        .difficulty-badge.hard {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }
    </style>
</head>
<body>
    <div class="quiz-container">
        <div class="quiz-header">
            <h1><?php echo htmlspecialchars($quizName); ?></h1>
            <p><?php echo htmlspecialchars($category); ?></p>
            <div class="quiz-info">
                <div class="timer" id="timer">
                    <i class="fas fa-clock"></i>
                    <span><?php 
                    // Pre-initialize timer text instead of showing --:--
                    if ($_SESSION['is_scheduled_quiz']) {
                        // For scheduled quizzes, show the time remaining until the end time
                        $totalMinutes = floor($timeRemaining / 60);
                        $seconds = $timeRemaining % 60;
                        
                        // Clean, standard timer format
                        echo "Time remaining: " . $totalMinutes . ":" . str_pad($seconds, 2, "0", STR_PAD_LEFT);
                    } else {
                        // Format time for regular quizzes - pad with zeros to match JS format
                        $minutes = floor($timeRemaining / 60);
                        $seconds = $timeRemaining % 60;
                        echo "Time remaining: " . $minutes . ":" . str_pad($seconds, 2, "0", STR_PAD_LEFT);
                    }
                    ?></span>
                </div>
                <?php if ($_SESSION['is_scheduled_quiz']): ?>
                <div class="scheduled-time">
                    <i class="fas fa-calendar-alt"></i>
                    <span>
                        Quiz: <?php echo date('g:i a', $_SESSION['scheduled_start_time']); ?> - 
                        <?php echo date('g:i a', $_SESSION['scheduled_end_time']); ?> 
                        (<?php 
                        // Format the duration nicely
                        $durationMinutes = ceil(($_SESSION['scheduled_end_time'] - $_SESSION['scheduled_start_time']) / 60);
                        $_SESSION['total_quiz_duration'] = $durationMinutes;
                        
                        if ($durationMinutes >= 60) {
                            $hours = floor($durationMinutes / 60);
                            $mins = $durationMinutes % 60;
                            echo $hours . 'h ' . ($mins > 0 ? $mins . 'm' : '');
                        } else {
                            echo $durationMinutes . ' min';
                        }
                        ?>)
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <form method="POST" id="quiz-form">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-card">
                    <div class="question-header">
                        <p class="question-text">
                            <?php 
                            $ques=$question['question'];
                            $ques=str_replace("\\","", $ques); // Remove all backslashes
                            $ques=str_replace("\\'",'\'',$ques);
                            $ques=str_replace('\\"','"',$ques);
                            echo ($index + 1)."." . htmlspecialchars($ques);  ?>
                            <span class="difficulty-badge <?php echo htmlspecialchars($question['difficulty_level']); ?>">
                                <?php echo htmlspecialchars($question['difficulty_level']); ?>
                            </span>
                        </p>
                    </div>
                    <ul class="options-list">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <li class="option-item">
                                <input type="radio" 
                                       id="q<?php echo $question['ID']; ?>_option<?php echo $i; ?>" 
                                       name="answers[<?php echo $question['ID']; ?>]" 
                                       value="<?php echo $question['option' . $i]; ?>" 
                                       class="radio-input" 
                                       required>
                                <label for="q<?php echo $question['ID']; ?>_option<?php echo $i; ?>" 
                                       class="option-label">
                                    <?php echo htmlspecialchars($question['option' . $i]); ?>
                                </label>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="submit-btn">Submit Quiz</button>
        </form>
    </div>

    <script>
        // Timer functionality
        const timerElement = document.getElementById('timer');
        const quizForm = document.getElementById('quiz-form');
        
        // Add visibility change detection
        document.addEventListener('visibilitychange', function() {
            if (document.hidden && !timeIsUp) {
                alert('Tab switching detected. The quiz will now be submitted.');
                endQuiz();
            }
        });
        
        // Detect fullscreen exit
        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement && !timeIsUp) {
                warningCount++;
                const remainingWarnings = MAX_WARNINGS - warningCount;
                
                if (warningCount >= MAX_WARNINGS) {
                    alert('You have exceeded the maximum number of fullscreen exits. The quiz will now be submitted.');
                    endQuiz();
                } else {
                    alert(`Warning: Exiting fullscreen during the quiz is not allowed! You have ${remainingWarnings} warning(s) remaining before automatic submission.`);
                }
            }
        });
        
        // ...existing code ...
        // Prevent back button functionality
        window.history.pushState(null, null, window.location.href);
        window.addEventListener('popstate', function() {
            window.history.pushState(null, null, window.location.href);
            alert('Back button is disabled during the quiz!');
        });

        // Request fullscreen when starting the quiz
        document.addEventListener('DOMContentLoaded', function() {
            const quizContainer = document.querySelector('.quiz-container');
            if (quizContainer.requestFullscreen) {
                quizContainer.requestFullscreen();
            }
        });
        
        // Get quiz ID and create localStorage keys
        const quizId = <?php echo $quizid; ?>;
        const quizStartKey = 'quiz_' + quizId + '_start_time';
        const quizTimeRemainingKey = 'quiz_' + quizId + '_time_remaining';
        const quizEndTimeKey = 'quiz_' + quizId + '_end_time';
        
        // Get server-provided time remaining
        let serverTimeRemaining = <?php echo max(0, $timeRemaining); ?>;
        let timeRemaining;
        
        // Get the current timestamp
        const currentTime = Math.floor(Date.now() / 1000);
        
        // Initialize or retrieve quiz end time from localStorage
        if (!localStorage.getItem(quizEndTimeKey)) {
            // First visit - calculate and store end time
            const endTime = currentTime + serverTimeRemaining;
            localStorage.setItem(quizEndTimeKey, endTime.toString());
            localStorage.setItem(quizStartKey, currentTime.toString());
            timeRemaining = serverTimeRemaining;
        } else {
            // On refresh - calculate time remaining based on stored end time
            const storedEndTime = parseInt(localStorage.getItem(quizEndTimeKey));
            timeRemaining = Math.max(0, storedEndTime - currentTime);
            
            // If server time is less than calculated time (e.g., admin reduced time),
            // use the server time instead
            if (serverTimeRemaining < timeRemaining) {
                timeRemaining = serverTimeRemaining;
                // Update stored end time
                localStorage.setItem(quizEndTimeKey, (currentTime + serverTimeRemaining).toString());
            }
        }
        
        // Store current time remaining for reference
        localStorage.setItem(quizTimeRemainingKey, timeRemaining.toString());
        
        console.log("Initial time setup - Server time:", serverTimeRemaining, 
                    "Calculated time:", timeRemaining, 
                    "End time:", localStorage.getItem(quizEndTimeKey));
        
        let quizDuration = <?php echo isset($quizDuration) ? $quizDuration : ($_SESSION['total_quiz_duration'] * 60); ?>;
        let timerInterval;
        const isScheduledQuiz = <?php echo $_SESSION['is_scheduled_quiz'] ? 'true' : 'false'; ?>;
        const totalQuizDuration = <?php echo $_SESSION['total_quiz_duration'] * 60; ?>;
        let timeIsUp = false;
        
        <?php if ($_SESSION['is_scheduled_quiz']): ?>
        const percentTimeUsed = <?php echo $_SESSION['percent_time_used']; ?>;
        <?php endif; ?>
        
        // If time is already up when page loads, end the quiz immediately
        if (timeRemaining <= 0) {
            console.log("Time already expired - ending quiz immediately");
            document.addEventListener('DOMContentLoaded', function() {
                endQuiz();
            });
        }
        
        // Cap the time remaining to the total duration
        if (timeRemaining > totalQuizDuration) {
            console.log("Correcting excessive time remaining");
            timeRemaining = totalQuizDuration;
            // Update stored end time
            localStorage.setItem(quizEndTimeKey, (currentTime + timeRemaining).toString());
            localStorage.setItem(quizTimeRemainingKey, timeRemaining.toString());
        }

        function endQuiz() {
            if (timeIsUp) return; // Prevent multiple submissions
            
            timeIsUp = true;
            clearInterval(timerInterval);
            timerElement.querySelector('span').textContent = 'Time is up!';
            
            // Clear localStorage timer data
            localStorage.removeItem(quizStartKey);
            localStorage.removeItem(quizTimeRemainingKey);
            localStorage.removeItem(quizEndTimeKey);
            
            // Disable submit button
            const submitBtn = document.querySelector('.submit-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Quiz Ended';
            }
            
            console.log("Quiz ended - submitting answers and redirecting to results page");
            
            // Display overlay with message
            const overlay = document.createElement('div');
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
            overlay.style.display = 'flex';
            overlay.style.justifyContent = 'center';
            overlay.style.alignItems = 'center';
            overlay.style.zIndex = '9999';
            overlay.style.color = 'white';
            overlay.style.fontSize = '24px';
            overlay.style.textAlign = 'center';
            overlay.style.padding = '20px';
            overlay.innerHTML = '<div>Time is up!<br>Submitting your answers...</div>';
            document.body.appendChild(overlay);
            
            // If the form has selections, submit it - otherwise create a hidden form to force submission
            if (hasAnswers()) {
                console.log("Submitting form with user's answers");
                quizForm.submit();
            } else {
                console.log("No answers selected - redirecting to results page directly");
                // Create and submit a hidden form to ensure the quiz is marked as completed
                submitEmptyForm();
            }
            
            // As a final fallback, redirect after a short delay if form submission doesn't work
            setTimeout(function() {
                console.log("Fallback redirect activated");
                window.location.href = 'results.php?quizid=' + quizId;
            }, 1500);
        }
        
        // Helper function to check if any answers are selected
        function hasAnswers() {
            const radios = document.querySelectorAll('input[type="radio"]:checked');
            return radios.length > 0;
        }
        
        // Function to submit an empty form to mark quiz as completed
        function submitEmptyForm() {
            const hiddenForm = document.createElement('form');
            hiddenForm.method = 'POST';
            hiddenForm.action = window.location.href; // Same URL to trigger the POST handler
            
            // Add a flag to indicate quiz is completed
            const completedField = document.createElement('input');
            completedField.type = 'hidden';
            completedField.name = 'quiz_completed';
            completedField.value = '1';
            hiddenForm.appendChild(completedField);
            
            // Add quiz ID
            const quizIdField = document.createElement('input');
            quizIdField.type = 'hidden';
            quizIdField.name = 'quizid';
            quizIdField.value = quizId;
            hiddenForm.appendChild(quizIdField);
            
            // Append to document and submit
            document.body.appendChild(hiddenForm);
            hiddenForm.submit();
        }

        function updateTimer() {
            if (timeRemaining <= 0) {
                endQuiz();
                return;
            }

            // Create a standard time message for all quiz types
            // Convert all time to total minutes and seconds
            const totalMinutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            
            // Format with proper padding - clean, simple format
            const timeMessage = `Time remaining: ${totalMinutes}:${seconds.toString().padStart(2, '0')}`;
            
            timerElement.querySelector('span').textContent = timeMessage;
            
            // Add warning class at 20% of total time remaining
            const warningThreshold = Math.min(300, quizDuration * 0.2); // 5 minutes or 20% of total time, whichever is less
            if (timeRemaining <= warningThreshold && !timerElement.classList.contains('warning')) {
                timerElement.classList.add('warning');
                // Optional: Add sound effect or vibration here
            }

            // Make timer more prominent in last minute
            if (timeRemaining <= 60) {
                timerElement.style.transform = timeRemaining % 2 ? 'scale(1.05)' : 'scale(1)';
            }
            
            timeRemaining--;
        }

        // Make sure DOM is fully loaded before manipulating elements
        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM loaded, initializing timer...");
            
            // Start timer immediately - fix for "--:--" display issue
            updateTimer();
            
            // Update every second - now set this after the initial update to prevent delay
            timerInterval = setInterval(updateTimer, 1000);
            
            // Form submission handling
            quizForm.addEventListener('submit', function(e) {
                if (timeRemaining <= 0) {
                    e.preventDefault();
                    endQuiz();
                }
            });
            
            // Set a permanent redirect after max quiz time + buffer
            // This prevents users from staying on the page after time expires
            const maxTimeWithBuffer = totalQuizDuration + 60; // Add 60 seconds buffer
            setTimeout(function() {
                if (!timeIsUp) {
                    endQuiz();
                }
            }, maxTimeWithBuffer * 1000);
            
            // Additional safeguard - check time every 5 seconds in case interval fails
            const safeguardInterval = setInterval(function() {
                if (timeRemaining <= 0 && !timeIsUp) {
                    console.log("Safeguard detected time expired");
                    clearInterval(safeguardInterval);
                    endQuiz();
                }
            }, 5000);
            
            console.log("Timer initialized successfully");
        });

        // Cleanup intervals on page unload
        window.addEventListener('unload', function() {
            clearInterval(timerInterval);
            if (typeof safeguardInterval !== 'undefined') {
                clearInterval(safeguardInterval);
            }
        });
    </script>
</body>
</html>