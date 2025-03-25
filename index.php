<?php 
include "dbconnect.php";

if(!isset($_SESSION['status']) || ($_SESSION['status'] !== "loggedin" && $_SESSION['status'] !== "admin")) {
    header("Location: landing.php");
    exit();
}

// Initialize welcome name variable
$welcome_name = '';
if (isset($_SESSION['name']) && !empty($_SESSION['name'])) {
    $welcome_name = $_SESSION['name'];
} else if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
    $welcome_name = $_SESSION['username'];
} else if (isset($_SESSION['email']) && !empty($_SESSION['email'])) {
    // Use email as fallback, but remove domain part
    $welcome_name = explode('@', $_SESSION['email'])[0];
}

// Get available quizzes with visibility check
$quizQuery = "SELECT qd.quizid, qd.quizname, qd.category, 
              (SELECT COUNT(*) FROM quizes WHERE quizid = qd.quizid) as question_count,
              qd.is_visible 
              FROM quizdetails qd";

// Add visibility condition for non-admin users
if ($_SESSION['status'] !== 'admin') {
    $quizQuery .= " WHERE qd.is_visible = TRUE";
}

$quizResult = $con->query($quizQuery);

// Get unique categories from visible quizzes only
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
            --delete-color: #ef4444;
            --delete-hover: #dc2626;
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

        .alert {
            position: relative;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            animation: fadeInUp 0.5s ease;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        /* Delete Category Button */
        .delete-category-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--delete-color);
            font-size: 1.2rem;
            margin-left: 10px;
            vertical-align: middle;
            transition: all 0.3s ease;
            opacity: 0.7;
            padding: 5px;
            border-radius: 50%;
        }

        .delete-category-btn:hover {
            opacity: 1;
            color: var(--delete-hover);
            transform: scale(1.1);
            background-color: rgba(239, 68, 68, 0.1);
        }

        .category-title-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Add Category Styles */
        .add-category-section {
            margin: var(--spacing-lg) 0;
            background: var(--quiz-card-bg);
            border-radius: 15px;
            padding: var(--spacing-lg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--quiz-card-border);
            text-align: center;
        }

        .section-title {
            color: var(--text-light);
            font-size: clamp(1.3rem, 4vw, 1.6rem);
            margin-bottom: var(--spacing-md);
            text-align: center;
        }

        .add-category-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .category-input {
            flex: 1;
            min-width: 250px;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid var(--quiz-card-border);
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .category-input:focus {
            outline: none;
            border-color: var(--quiz-primary);
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }

        .add-category-btn {
            padding: 12px 20px;
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .add-category-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
            background: linear-gradient(135deg, #059669, #10b981);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Add visibility indicator styles */
        .visibility-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            z-index: 2;
        }

        .visibility-visible {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
        }

        .visibility-hidden {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }
    </style>
</style>

    <div class="dashboard">
        <?php if(isset($_SESSION['success'])): ?>
            <div class="message success" style="background-color: rgba(34, 197, 94, 0.2); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-weight: 500;">
                <?php 
                    echo htmlspecialchars($_SESSION['success']); 
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="message error" style="background-color: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-weight: 500;">
                <?php 
                    echo htmlspecialchars($_SESSION['error']); 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <section class="welcome-section">
            <h1 class="welcome-title">
                Welcome<?php echo $welcome_name ? ', ' . htmlspecialchars($welcome_name) : ''; ?>!
            </h1>
            <p class="welcome-subtitle">
                Explore our quizzes below, categorized for your convenience.
            </p>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo htmlspecialchars($_SESSION['message']); 
                        // Clear the message after displaying it
                        unset($_SESSION['message']);
                    ?>
                </div>
            <?php endif; ?>
        </section>

        <?php if($_SESSION['status'] === "admin"): ?>
        <section class="add-category-section">
            <h2 class="section-title">Add New Category</h2>
            <form method="POST" action="add_category.php" class="add-category-form">
                <div class="form-group">
                    <input type="text" name="category_name" placeholder="Enter new category name" required class="category-input">
                    <button type="submit" class="add-category-btn">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                </div>
            </form>
        </section>
        <?php endif; ?>

        <?php foreach ($categories as $category): ?>
            <div class="category-section">
                <h2 class="category-title"><?php echo htmlspecialchars($category); ?></h2>
                <div class="quiz-grid">
                    <?php
                    $quizResult->data_seek(0);
                    $hasQuizzes = false;
                    while ($quiz = $quizResult->fetch_assoc()):
                        if ($quiz['category'] === $category):
                            $hasQuizzes = true;
                    ?>
                        <div class="quiz-card">
                            <?php if ($_SESSION['status'] === 'admin'): ?>
                                <div class="visibility-indicator <?php echo $quiz['is_visible'] ? 'visibility-visible' : 'visibility-hidden'; ?>">
                                    <?php echo $quiz['is_visible'] ? 'Visible' : 'Hidden'; ?>
                                </div>
                            <?php endif; ?>
                            <h3 class="quiz-title"><?php echo htmlspecialchars($quiz['quizname']); ?></h3>
                            <p class="quiz-info">
                                Questions: <?php echo $quiz['question_count']; ?>
                            </p>
                            <a href="takequiz.php?quizid=<?php echo $quiz['quizid']; ?>" class="take-quiz-btn">
                                Take Quiz
                            </a>
                        </div>
                    <?php
                        endif;
                    endwhile;
                    if (!$hasQuizzes):
                    ?>
                        <div class="empty-category">
                            No quizzes available in this category.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php include "components/footer.php"; ?>