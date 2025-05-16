<?php 
include "dbconnect.php";

if(!isset($_SESSION['status']) || ($_SESSION['status'] !== "loggedin" && $_SESSION['status'] !== "admin")) {
    header("Location: landing.php");
    exit();
}

$quizid = $_GET['quizid'] ?? '';
$quiz_name = '';

if($quizid) {
    $stmt = $con->prepare("SELECT quizname FROM quizdetails WHERE quizid = ?");
    $stmt->bind_param("i", $quizid);
    $stmt->execute();
    $result = $stmt->get_result();
    if($row = $result->fetch_assoc()) {
        $quiz_name = $row['quizname'];
    }
}
?>

<?php include "components/header.php"; ?>
<style>
    body {
        background: var(--background);
        color: var(--text);
        min-height: 100vh;
    }

    .difficulty-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .difficulty-title {
        text-align: center;
        margin-bottom: 2rem;
        color: var(--primary); /* Changed from var(--text-light) to primary blue */
        font-size: 1.8rem;
    }

    .difficulty-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .difficulty-card {
        background: var(--quiz-card-bg);
        border: 1px solid var(--quiz-card-border);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .difficulty-card:hover {
        transform: translateY(-5px);
        background: var(--quiz-card-hover);
    }

    .difficulty-beginner { border-left: 4px solid #4ade80; }
    .difficulty-intermediate { border-left: 4px solid #60a5fa; }
    .difficulty-advanced { border-left: 4px solid #f472b6; }
    .difficulty-expert { border-left: 4px solid #8b5cf6; }

    .difficulty-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .difficulty-name {
        font-size: 1.2rem;
        color: var(--text-light);
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .difficulty-desc {
        font-size: 0.9rem;
        color: var(--text-muted);
    }

    .back-link {
        display: inline-block;
        margin-bottom: 1rem;
        color: white;
        text-decoration: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        background: var(--primary);
        transition: all 0.3s ease;
    }

    .back-link:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }
</style>

<div class="difficulty-container">
    <a href="index.php" class="back-link">← Back to Quizzes</a>
    
    <h1 class="difficulty-title">Select Difficulty Level for:<br><?php echo htmlspecialchars($quiz_name); ?></h1>
    
    <div class="difficulty-grid">
        <a href="takequiz.php?quizid=<?php echo $quizid; ?>&difficulty=beginner" class="difficulty-card difficulty-beginner">
            <div class="difficulty-icon">🔰</div>
            <div class="difficulty-name">Beginner</div>
            <div class="difficulty-desc">Basic concepts and simple questions</div>
        </a>
        
        <a href="takequiz.php?quizid=<?php echo $quizid; ?>&difficulty=intermediate" class="difficulty-card difficulty-intermediate">
            <div class="difficulty-icon">🧩</div>
            <div class="difficulty-name">Intermediate</div>
            <div class="difficulty-desc">Moderate complexity and challenge</div>
        </a>
        
        <a href="takequiz.php?quizid=<?php echo $quizid; ?>&difficulty=advanced" class="difficulty-card difficulty-advanced">
            <div class="difficulty-icon">🔥</div>
            <div class="difficulty-name">Advanced</div>
            <div class="difficulty-desc">Complex concepts and tough questions</div>
        </a>
        
        <a href="takequiz.php?quizid=<?php echo $quizid; ?>&difficulty=expert" class="difficulty-card difficulty-expert">
            <div class="difficulty-icon">💎</div>
            <div class="difficulty-name">Expert</div>
            <div class="difficulty-desc">Most challenging questions</div>
        </a>
    </div>
</div>
<?php include "components/footer.php"; ?>