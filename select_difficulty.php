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
    :root {
        --primary-color: #4a90e2;
        --secondary-color: #357abd;
        --success-color: #2ecc71;
        --error-color: #e74c3c;
        --background-dark: #1a1a2e;
        --text-light: #ffffff;
        --text-muted: #a0a0a0;
    }

    body {
        background: linear-gradient(135deg, var(--background-dark) 0%, #16213e 100%);
        min-height: 100vh;
        margin: 0;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: var(--text-light);
    }

    .difficulty-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        animation: slideUp 0.5s ease;
    }

    .difficulty-title {
        text-align: center;
        margin-bottom: 2rem;
        font-size: 2rem;
        background: linear-gradient(135deg, #fff, var(--primary-color));
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .difficulty-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .difficulty-card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .difficulty-card:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.1);
    }

    .difficulty-easy { border-left: 4px solid #4ade80; }
    .difficulty-medium { border-left: 4px solid #60a5fa; }
    .difficulty-intermediate { border-left: 4px solid #f472b6; }
    .difficulty-hard { border-left: 4px solid #8b5cf6; }

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
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        transition: all 0.3s ease;
    }

    .back-link:hover {
        background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(74, 144, 226, 0.3);
    }
</style>

<div class="difficulty-container">
    <a href="index.php" class="back-link">← Back to Quizzes</a>
    
    <h1 class="difficulty-title">Select Difficulty Level for:<br><?php echo htmlspecialchars($quiz_name); ?></h1>
    
    <div class="difficulty-grid">
        <a href="takequiz.php?quizid=<?php echo $quizid; ?>&difficulty=easy" class="difficulty-card difficulty-easy">
            <div class="difficulty-icon">🔰</div>
            <div class="difficulty-name">Easy</div>
            <div class="difficulty-desc">Basic concepts and simple questions</div>
        </a>
        
        <a href="takequiz.php?quizid=<?php echo $quizid; ?>&difficulty=medium" class="difficulty-card difficulty-medium">
            <div class="difficulty-icon">🧩</div>
            <div class="difficulty-name">Medium</div>
            <div class="difficulty-desc">Moderate complexity and challenge</div>
        </a>
        
        <a href="takequiz.php?quizid=<?php echo $quizid; ?>&difficulty=intermediate" class="difficulty-card difficulty-intermediate">
            <div class="difficulty-icon">🔥</div>
            <div class="difficulty-name">Intermediate</div>
            <div class="difficulty-desc">Complex concepts and tough questions</div>
        </a>
        
        <a href="takequiz.php?quizid=<?php echo $quizid; ?>&difficulty=hard" class="difficulty-card difficulty-hard">
            <div class="difficulty-icon">💎</div>
            <div class="difficulty-name">Hard</div>
            <div class="difficulty-desc">Most challenging questions</div>
        </a>
    </div>
</div>
<?php include "components/footer.php"; ?>