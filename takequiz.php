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

// Get quiz details
$stmt = $con->prepare("SELECT * FROM quizdetails WHERE quizid = ?");
$stmt->bind_param("i", $quizid);
$stmt->execute();
$quizDetails = $stmt->get_result()->fetch_assoc();

if (!$quizDetails) {
    header("Location: index.php?error=invalid_quiz");
    exit();
}

// Initialize timer if not set
if (!isset($_SESSION['quiz_start_time'])) {
    $_SESSION['quiz_start_time'] = time();
}

// Get quiz duration (in minutes), default to 30 minutes if not set
$quizDuration = (isset($quizDetails['timer']) ? intval($quizDetails['timer']) : 30) * 60; // Convert minutes to seconds
$timeRemaining = $quizDuration - (time() - $_SESSION['quiz_start_time']);

// If time's up, save the current answers and redirect to results
if ($timeRemaining <= 0) {
    $_SESSION['quiz_completed'] = true;
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz</title>
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
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

        .timer {
            position: fixed;
            top: var(--spacing-md);
            right: var(--spacing-md);
            background: rgba(52, 73, 94, 0.95);
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: 10px;
            color: white;
            font-size: clamp(1rem, 2.5vw, 1.2rem);
            backdrop-filter: blur(10px);
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media screen and (max-width: 768px) {
            .quiz-container {
                width: 95%;
                padding: var(--spacing-md);
                margin: var(--spacing-md) auto;
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
    <div class="timer" id="timer">Time remaining: --:--</div>
    
    <nav>
        <a href="index.php">Home</a>
        <a href="quizmanip.php">Edit</a>
        <a href="createquiz.php">Create</a>
        <a href="quizform.php">Insert</a>
        <a href="logout.php">Logout</a>
        <div class="animation"></div>
    </nav>

    <div class="quiz-container">
        <div class="quiz-header">
            <h1><?php echo htmlspecialchars($quizName); ?></h1>
            <p><?php echo htmlspecialchars($category); ?></p>
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

        function updateTimer() {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            
            timerElement.textContent = `Time remaining: ${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeRemaining <= 300) { // 5 minutes warning
                timerElement.classList.add('warning');
            }
            
            if (timeRemaining <= 0) {
                quizForm.submit();
                return;
            }
            
            timeRemaining--;
            setTimeout(updateTimer, 1000);
        }

        updateTimer();

        // Form submission handling
        quizForm.addEventListener('submit', function(e) {
            if (timeRemaining <= 0) {
                e.preventDefault();
                alert('Time is up! Your answers will be submitted automatically.');
                window.location.href = 'results.php?quizid=<?php echo $quizid; ?>';
            }
        });
    </script>
</body>
</html>