<?php
include "dbconnect.php";

if ($_SESSION['status'] == "loggedout" || $_SESSION['status'] == "" || empty($_SESSION['status'])) {
    header("Location: login.php");
    exit();
}

// Initialize timer if not set
if (!isset($_SESSION['quiz_start_time'])) {
    $_SESSION['quiz_start_time'] = time();
}

// Set quiz duration (30 minutes = 1800 seconds)
$quizDuration = 1800;
$timeRemaining = $quizDuration - (time() - $_SESSION['quiz_start_time']);

if ($timeRemaining <= 0) {
    // Time's up - redirect to results
    unset($_SESSION['quiz_start_time']);
    header("Location: results.php");
    exit();
}

if (!isset($_GET['quizid'])) {
    header("Location: index.php");
    exit();
}

$quizid = $_GET['quizid'];
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

while ($row = $result->fetch_assoc()) {
    if (empty($quizName)) {
        $quizName = $row['quizname'];
        $category = $row['category'];
    }
    $questions[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="nav.css">
    <style>
        .hidden {
            display: none !important;
        }
    </style>
    <title><?php echo htmlspecialchars($quizName); ?> Quiz</title>
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="css/enhanced-style.css">
    <style>
        .quiz-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .quiz-header {
            text-align: center;
            margin-bottom: 2rem;
            color: #fff;
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

        .options-list {
            list-style: none;
            padding: 0;
        }

        .option-item {
            margin: 0.5rem 0;
        }

        .option-label {
            display: block;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .option-label:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(10px);
        }

        .submit-btn {
            display: block;
            width: 100%;
            padding: 1rem;
            background: #4a90e2;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 2rem;
        }

        .submit-btn:hover {
            background: #357abd;
            transform: scale(1.02);
        }

        .radio-input {
            display: none;
        }

        .radio-input:checked + .option-label {
            background: rgba(74, 144, 226, 0.3);
            border: 1px solid #4a90e2;
        }

        /* Add timer styles */
        .timer {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            color: white;
            font-size: 1.2rem;
            backdrop-filter: blur(10px);
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="timer" id="timer">Time remaining: --:--</div>
    <nav>
        <a href="./index.php">Home</a>
        <?php if(isAdmin()): ?>
            <a href="./quizmanip.php">Edit</a>
            <a href="./createquiz.php">Create</a>
            <a href="./quizform.php">Insert</a>
        <?php endif; ?>
        <a style="width:100px" href="./logout.php">Logout</a>
        <div class="animation start-home"></div>
    </nav>

    <div class="quiz-container">
        <div class="quiz-header">
            <h1><?php echo htmlspecialchars($quizName); ?></h1>
            <p>Category: <?php echo htmlspecialchars($category); ?></p>
        </div>

        <form id="quizForm" method="POST" action="submit_quiz.php">
            <input type="hidden" name="quizid" value="<?php echo $quizid; ?>">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-card">
                    <p class="question-text"><?php echo ($index + 1) . ". " . htmlspecialchars($question['question']); ?></p>
                    <ul class="options-list">
                        <?php
                        $options = array(
                            'option1' => $question['option1'],
                            'option2' => $question['option2'],
                            'option3' => $question['option3'],
                            'option4' => $question['option4']
                        );
                        foreach ($options as $key => $option):
                        ?>
                            <li class="option-item">
                                <input type="radio" 
                                       id="<?php echo $question['ID'] . '_' . $key; ?>" 
                                       name="answer[<?php echo $question['ID']; ?>]" 
                                       value="<?php echo htmlspecialchars($option); ?>" 
                                       class="radio-input" 
                                       required>
                                <label for="<?php echo $question['ID'] . '_' . $key; ?>" 
                                       class="option-label">
                                    <?php echo htmlspecialchars($option); ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="submit-btn">Submit Quiz</button>
        </form>
    </div>

    <script>
        // Timer implementation
        let timeLeft = <?php echo $timeRemaining; ?>;
        
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('timer').innerHTML = `Time remaining: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            
            if (timeLeft <= 0) {
                document.getElementById('quizForm').submit();
            } else {
                timeLeft--;
                setTimeout(updateTimer, 1000);
            }
        }

        updateTimer();

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>