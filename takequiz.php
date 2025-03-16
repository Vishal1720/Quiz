<?php
session_start();
require "dbconnect.php";

// Check login status
if (!isset($_SESSION['status']) || $_SESSION['status'] !== "loggedin") {
    header("Location: login.php");
    exit();
}

// Check if quiz ID is provided
if (!isset($_GET['quizid'])) {
    header("Location: index.php");
    exit();
}

$quizid = mysqli_real_escape_string($con, $_GET['quizid']);

// Reset timer if this is a new quiz attempt or a different quiz
if (isset($_SESSION['current_quiz_id']) && $_SESSION['current_quiz_id'] != $quizid) {
    // User is starting a different quiz, reset timer
    unset($_SESSION['quiz_start_time']);
    unset($_SESSION['quiz_duration']);
    unset($_SESSION['quiz_completed']);
}

// Store current quiz ID in session
$_SESSION['current_quiz_id'] = $quizid;

// Get quiz details
$stmt = $con->prepare("SELECT * FROM quizdetails WHERE quizid = ?");
$stmt->bind_param("i", $quizid);
$stmt->execute();
$quizDetails = $stmt->get_result()->fetch_assoc();

if (!$quizDetails) {
    header("Location: index.php?error=invalid_quiz");
    exit();
}

// Always reset the timer when starting a quiz
unset($_SESSION['quiz_start_time']);

// Initialize timer
$_SESSION['quiz_start_time'] = time();

// DIRECTLY use the timer value from the database - no conditions
$dbTimer = intval($quizDetails['timer']);

// If timer is 0 or not set, use 30 minutes as fallback
if ($dbTimer <= 0) {
    $_SESSION['quiz_duration'] = 30; // Default 30 minutes
    echo "<!-- DEBUG: Timer was 0 or negative, using default 30 minutes -->";
} else {
    $_SESSION['quiz_duration'] = $dbTimer;
}

// Force debug output to browser for troubleshooting
echo "<!-- DEBUG: Quiz ID: {$quizid}, DB Timer Value: {$quizDetails['timer']}, Using: {$_SESSION['quiz_duration']} minutes -->";

// Convert minutes to seconds
$quizDuration = $_SESSION['quiz_duration'] * 60;
$timeRemaining = $quizDuration - (time() - $_SESSION['quiz_start_time']);

// If time's up, save the current answers and redirect to results
if ($timeRemaining <= 0) {
    $_SESSION['quiz_completed'] = true;
    $_SESSION['quiz_answers'] = isset($_POST['answers']) ? $_POST['answers'] : array();
    unset($_SESSION['quiz_start_time']);
    header("Location: results.php?quizid=" . $quizid);
    exit();
}

// Get quiz questions
$query = "SELECT q.*, qd.quizname, qd.category FROM quizes q 
          JOIN quizdetails qd ON q.quizid = qd.quizid 
          WHERE q.quizid = ? ORDER BY q.id ASC";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $quizid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php?error=no_questions");
    exit();
}

$quizName = "";
$category = "";
$questions = array();

while ($row = $result->fetch_assoc()) {
    if (empty($quizName)) {
        $quizName = $row['quizname'];
        $category = $row['category'];
    }
    $questions[] = $row;
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
            color: var(--text);
            font-weight: 600;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .timer i {
            color: var(--primary-color);
        }

        .timer.warning {
            color: var(--error);
            animation: pulse 2s infinite;
            background: rgba(239, 68, 68, 0.1);
            padding: 0.5rem 1rem;
        }

        .timer.warning i {
            color: var(--error);
            animation: spin 10s linear infinite;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.8; }
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
                    <span>Time remaining: --:--</span>
                </div>
            </div>
        </div>

        <form method="POST" id="quiz-form">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-card">
                    <div class="question-text">
                        <?php echo ($index + 1) . ". " . htmlspecialchars($question['question']); ?>
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
        let timeRemaining = <?php echo $timeRemaining; ?>;
        let quizDuration = <?php echo $quizDuration; ?>; // Store the total quiz duration
        let timerInterval;

        function formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
        }

        function updateTimer() {
            if (timeRemaining <= 0) {
                clearInterval(timerInterval);
                timerElement.querySelector('span').textContent = 'Time is up!';
                quizForm.submit();
                return;
            }

            timerElement.querySelector('span').textContent = `Time remaining: ${formatTime(timeRemaining)}`;
            
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

        // Start timer immediately
        updateTimer();
        // Update every second
        timerInterval = setInterval(updateTimer, 1000);

        // Form submission handling
        quizForm.addEventListener('submit', function(e) {
            if (timeRemaining <= 0) {
                e.preventDefault();
                alert('Time is up! Your answers will be submitted automatically.');
                window.location.href = 'results.php?quizid=<?php echo $quizid; ?>';
            }
        });

        // Cleanup interval on page unload
        window.addEventListener('unload', function() {
            clearInterval(timerInterval);
        });
    </script>
</body>
</html>