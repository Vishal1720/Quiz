<?php
require 'dbconnect.php';

if(!isset($_GET['code'])) {
    header("Location: index.php");
    exit();
}

$code = $_GET['code'];
$current_time = date('Y-m-d H:i:s');

// Get scheduled quiz details
$stmt = $con->prepare("SELECT sq.*, qd.quizname, qd.category 
                      FROM scheduled_quizzes sq 
                      JOIN quizdetails qd ON sq.quizid = qd.quizid 
                      WHERE sq.access_code = ? 
                      AND sq.start_time <= ? 
                      AND sq.end_time >= ?");
$stmt->bind_param("sss", $code, $current_time, $current_time);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    $error = "This quiz is not currently available or the link is invalid.";
}

$quiz = $result->fetch_assoc();
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
        <div class="error-message"><?php echo $error; ?></div>
    <?php else: ?>
        <h1><?php echo htmlspecialchars($quiz['quizname']); ?></h1>
        <div class="quiz-info">
            <p><strong>Category:</strong> <?php echo htmlspecialchars($quiz['category']); ?></p>
            <p><strong>Available until:</strong> <?php echo date('F j, Y, g:i a', strtotime($quiz['end_time'])); ?></p>
        </div>
        
        <?php if(!isset($_SESSION['status']) || $_SESSION['status'] !== 'loggedin'): ?>
            <p>Please log in to take the quiz.</p>
            <a href="login.php" class="start-btn">Login</a>
        <?php else: ?>
            <a href="takequiz.php?quizid=<?php echo $quiz['quizid']; ?>" class="start-btn">Start Quiz</a>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include "components/footer.php"; ?>
