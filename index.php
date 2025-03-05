<?php include "dbconnect.php" ;
if($_SESSION['status']=="loggedout" || $_SESSION['status']=="" || empty($_SESSION['status'])) 
{
    header("Location: login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="css/enhanced-style.css">
    <title>Quiz Dashboard</title>
    <style>
        .dashboard {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .category-section {
            margin: 2rem 0;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 15px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
        }

        .category-section:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.1);
        }

        .category-title {
            color: #fff;
            font-size: 2rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 1.5rem;
            text-align: center;
            position: relative;
            padding-bottom: 10px;
        }

        .category-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #fff, transparent);
        }

        .quiz-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            padding: 1rem;
        }

        .quiz-card {
            background: linear-gradient(145deg, #2a2a72, #009ffd);
            border-radius: 15px;
            padding: 1.8rem;
            transition: all 0.4s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
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
            background: linear-gradient(225deg, transparent, rgba(255, 255, 255, 0.1));
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .quiz-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .quiz-card:hover::before {
            opacity: 1;
        }

        .quiz-title {
            color: #fff;
            font-size: 1.4rem;
            margin-bottom: 1.2rem;
            text-align: center;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .take-quiz-btn {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            color: #fff;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .take-quiz-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }

        .take-quiz-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(1.05);
            letter-spacing: 1px;
        }

        .take-quiz-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .empty-category {
            text-align: center;
            color: #fff;
            font-style: italic;
            opacity: 0.7;
            padding: 2rem;
        }

        .welcome-section {
            text-align: center;
            padding: 3rem 1rem;
            margin-bottom: 2rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .welcome-title {
            font-size: 2.5rem;
            color: #fff;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .welcome-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #4a90e2;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <nav>
        <a href="#" class="active">Home</a>
        <a href="./quizmanip.php">Edit</a>
        <a href="./createquiz.php">Create</a>
        <a href="./quizform.php">Insert</a>
        <a style="width:100px" href="./logout.php">Logout</a>
        <div class="animation home"></div>
    </nav>

    <div class="welcome-section">
        <h1 class="welcome-title">Welcome to Quiz Master</h1>
        <p class="welcome-subtitle">Create, manage, and take quizzes with ease</p>
        
        <div class="stats-grid">
            <?php
            // Get statistics
            $total_quizzes = $con->query("SELECT COUNT(*) as count FROM quizdetails")->fetch_assoc()['count'];
            $user_quizzes = $con->query("SELECT COUNT(*) as count FROM quizdetails WHERE email='{$_SESSION['email']}'")->fetch_assoc()['count'];
            $total_categories = $con->query("SELECT COUNT(*) as count FROM category")->fetch_assoc()['count'];
            ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_quizzes; ?></div>
                <div class="stat-label">Total Quizzes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $user_quizzes; ?></div>
                <div class="stat-label">Your Quizzes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_categories; ?></div>
                <div class="stat-label">Categories</div>
            </div>
        </div>
    </div>

    <div class="dashboard">
        <?php 
        $query = "SELECT * FROM category";
        $res = $con->query($query);
        
        while($row = $res->fetch_assoc()) {
            $cat = $row['categoryname'];
            $query2 = "SELECT * FROM `quizdetails` WHERE `category`='$cat'";
            $res2 = $con->query($query2);

            if($res2->num_rows == 0) continue;

            echo "<div class='category-section'>";
            echo "<h2 class='category-title'>{$row['categoryname']}</h2>";
            echo "<div class='quiz-grid'>";
            
            while($row2 = $res2->fetch_assoc()) {
                echo "<div class='quiz-card'>";
                echo "<h3 class='quiz-title'>{$row2['quizname']}</h3>";
                echo "<form method='GET' action='takequiz.php'>";
                echo "<input type='hidden' name='quizid' value='{$row2['quizid']}'>";
                echo "<button type='submit' class='take-quiz-btn'>Take Quiz</button>";
                echo "</form>";
                echo "</div>";
            }
            
            echo "</div></div>";
        }
        ?>
    </div>
</body>
</html>