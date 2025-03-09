<?php 
include "dbconnect.php";

if(!isset($_SESSION['status']) || ($_SESSION['status'] !== "loggedin" && $_SESSION['status'] !== "admin")) {
    header("Location: landing.php");
    exit();
}

// Get available quizzes
$quizQuery = "SELECT qd.quizid, qd.quizname, qd.category, 
              (SELECT COUNT(*) FROM quizes WHERE quizid = qd.quizid) as question_count 
              FROM quizdetails qd";
$quizResult = $con->query($quizQuery);

// Get unique categories
$categories = array();
if ($quizResult) {
    while ($row = $quizResult->fetch_assoc()) {
        if (!in_array($row['category'], $categories)) {
            $categories[] = $row['category'];
        }
    }
    // Reset result pointer
    $quizResult->data_seek(0);
}
?>
<?php include "components/header.php"; ?>
    <style>
        /* Dashboard specific styles */
        :root {
            --quiz-primary: #4a90e2;
            --quiz-secondary: #357abd;
            --quiz-card-bg: rgba(255, 255, 255, 0.05);
            --quiz-card-border: rgba(255, 255, 255, 0.1);
            --quiz-card-hover: rgba(255, 255, 255, 0.08);
        }

        .dashboard {
            width: var(--container-width);
            margin: var(--spacing-lg) auto;
            padding: var(--spacing-md);
        }

        .welcome-section {
            text-align: center;
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
            background: var(--quiz-card-bg);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid var(--quiz-card-border);
            animation: fadeIn 0.5s ease;
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(236, 72, 153, 0.15) 0%, transparent 50%);
            z-index: 0;
        }

        .welcome-title {
            position: relative;
            font-size: clamp(2rem, 5vw, 2.5rem);
            margin-bottom: var(--spacing-md);
            background: linear-gradient(135deg, #fff, var(--quiz-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 10px rgba(255, 255, 255, 0.1);
        }

        .welcome-subtitle {
            position: relative;
            font-size: clamp(1rem, 3vw, 1.2rem);
            color: var(--text-muted);
            margin-bottom: var(--spacing-lg);
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .category-section {
            margin: var(--spacing-lg) 0;
            background: var(--quiz-card-bg);
            border-radius: 15px;
            padding: var(--spacing-lg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--quiz-card-border);
        }

        .category-title {
            color: var(--text-light);
            font-size: clamp(1.5rem, 4vw, 1.8rem);
            margin-bottom: var(--spacing-md);
            padding-bottom: 10px;
            border-bottom: 2px solid var(--card-border);
            text-align: center;
        }

        .quiz-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: var(--spacing-lg);
            padding: var(--spacing-md);
        }

        .quiz-card {
            background: var(--quiz-card-bg);
            border-radius: 15px;
            padding: var(--spacing-lg);
            transition: all 0.3s ease;
            border: 1px solid var(--quiz-card-border);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .quiz-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, 
                rgba(99, 102, 241, 0.05) 0%, 
                rgba(236, 72, 153, 0.05) 100%);
            z-index: 0;
            transition: all 0.3s ease;
        }

        .quiz-card > * {
            position: relative;
            z-index: 1;
        }

        .quiz-card:hover {
            transform: translateY(-5px);
            background: var(--quiz-card-hover);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .quiz-card:hover::before {
            transform: scale(1.1);
            opacity: 0.8;
        }

        .quiz-title {
            color: var(--text-light);
            font-size: clamp(1.2rem, 3vw, 1.4rem);
            margin-bottom: var(--spacing-md);
            text-align: center;
            font-weight: 600;
            background: linear-gradient(135deg, #fff, var(--quiz-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .quiz-info {
            color: var(--text-muted);
            font-size: clamp(0.8rem, 2vw, 0.9rem);
            margin-bottom: var(--spacing-md);
            text-align: center;
        }

        .take-quiz-btn {
            width: 100%;
            padding: var(--spacing-md);
            background: linear-gradient(135deg, var(--quiz-primary), var(--quiz-secondary));
            border: none;
            border-radius: 10px;
            color: var(--text-light);
            font-size: clamp(1rem, 2.5vw, 1.1rem);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            margin-top: auto;
        }

        .take-quiz-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.3);
            background: linear-gradient(135deg, var(--quiz-secondary), var(--quiz-primary));
        }

        .empty-category {
            text-align: center;
            color: var(--text-muted);
            padding: var(--spacing-lg);
            font-style: italic;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</style>

    <div class="dashboard">
        <section class="welcome-section">
            <h1 class="welcome-title">
                <?php if($_SESSION['status'] === "admin"): ?>
                    Welcome Admin
                <?php else: ?>
                    Welcome to the Quiz Platform
                <?php endif; ?>
            </h1>
            <p class="welcome-subtitle">
                <?php if($_SESSION['status'] === "admin"): ?>
                    Manage quizzes and monitor user progress
                <?php else: ?>
                    Choose a quiz from the categories below to test your knowledge
                <?php endif; ?>
            </p>
        </section>

        <?php foreach($categories as $category): ?>
            <section class="category-section">
                <h2 class="category-title"><?php echo htmlspecialchars($category); ?></h2>
                <div class="quiz-grid">
                    <?php
                    $quizResult->data_seek(0);
                    $hasQuizzes = false;
                    while ($quiz = $quizResult->fetch_assoc()):
                        if ($quiz['category'] === $category):
                            $hasQuizzes = true;
                    ?>
                        <article class="quiz-card">
                            <div>
                                <h3 class="quiz-title"><?php echo htmlspecialchars($quiz['quizname']); ?></h3>
                                <p class="quiz-info"><?php echo $quiz['question_count']; ?> Questions</p>
                            </div>
                            <?php if($_SESSION['status'] === "admin"): ?>
                                <a href="quizmanip.php?quizid=<?php echo $quiz['quizid']; ?>" class="take-quiz-btn">
                                    Edit Quiz
                                </a>
                            <?php else: ?>
                                <a href="takequiz.php?quizid=<?php echo $quiz['quizid']; ?>" class="take-quiz-btn">
                                    Take Quiz
                                </a>
                            <?php endif; ?>
                        </article>
                    <?php
                        endif;
                    endwhile;
                    if (!$hasQuizzes):
                    ?>
                        <p class="empty-category">No quizzes available in this category yet.</p>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
<?php include "components/footer.php"; ?>