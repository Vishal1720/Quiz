<?php 
include "dbconnect.php";

if($_SESSION['status'] !== "loggedin" && $_SESSION['status'] !== "admin") {
    header("Location: login.php");
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="css/responsive.css">
    <title>Quiz Dashboard</title>
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #357abd;
            --background-dark: #1a1a2e;
            --text-light: #ffffff;
            --text-muted: #a0a0a0;
            --card-background: rgba(255, 255, 255, 0.05);
            --card-border: rgba(255, 255, 255, 0.1);
            --card-hover: rgba(255, 255, 255, 0.08);
            --container-width: 1200px;
            --spacing-lg: 2rem;
            --spacing-md: 1.5rem;
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
            background: var(--card-background);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            animation: fadeIn 0.5s ease;
        }

        .welcome-title {
            font-size: clamp(2rem, 5vw, 2.5rem);
            margin-bottom: var(--spacing-md);
            background: linear-gradient(135deg, #fff, var(--primary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-subtitle {
            font-size: clamp(1rem, 3vw, 1.2rem);
            color: var(--text-muted);
            margin-bottom: var(--spacing-lg);
        }

        .category-section {
            margin: var(--spacing-lg) 0;
            background: var(--card-background);
            border-radius: 15px;
            padding: var(--spacing-lg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
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
            background: var(--card-background);
            border-radius: 15px;
            padding: var(--spacing-lg);
            transition: all 0.3s ease;
            border: 1px solid var(--card-border);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .quiz-card:hover {
            transform: translateY(-5px);
            background: var(--card-hover);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .quiz-title {
            color: var(--text-light);
            font-size: clamp(1.2rem, 3vw, 1.4rem);
            margin-bottom: var(--spacing-md);
            text-align: center;
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
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
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
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
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
</head>
<body>
    <nav>
        <a href="./index.php" class="active">Home</a>
        <?php if($_SESSION['status'] === "admin"): ?>
            <a href="./quizmanip.php">Edit</a>
            <a href="./createquiz.php">Create</a>
            <a href="./quizform.php">Insert</a>
        <?php endif; ?>
        <a href="./logout.php">Logout</a>
        <div class="animation start-home"></div>
    </nav>

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
</body>
</html>