<?php
include "dbconnect.php";

// Check if user is logged in and is admin
if ($_SESSION['status'] != "admin" || !isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Check if quiz_id is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: quiz_statistics.php");
    exit();
}

$quiz_id = $_GET['id'];

// Get quiz details
$quiz_details_query = "
    SELECT 
        qd.quizid,
        qd.quizname,
        qd.category,
        qd.timer,
        COUNT(DISTINCT q.ID) as total_questions
    FROM quizdetails qd
    LEFT JOIN quizes q ON qd.quizid = q.quizid
    WHERE qd.quizid = ?
    GROUP BY qd.quizid, qd.quizname, qd.category, qd.timer";
    
$stmt = $con->prepare($quiz_details_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz_details = $stmt->get_result()->fetch_assoc();

if (!$quiz_details) {
    header("Location: quiz_statistics.php?error=invalid_quiz");
    exit();
}

// Check if gmail_users table exists
$gmailUsersExist = true;
try {
    $checkGmailTable = $con->query("SELECT 1 FROM gmail_users LIMIT 1");
} catch (Exception $e) {
    $gmailUsersExist = false;
}

// Get all attempts for this quiz
if ($gmailUsersExist) {
    $attempts_query = "
        SELECT 
            qa.attempt_id,
            COALESCE(u.name, g.name, 'Unknown User') as user_name,
            qa.user_email,
            CASE 
                WHEN g.email IS NOT NULL THEN 'Gmail'
                WHEN u.email IS NOT NULL THEN 'Direct'
                ELSE 'Unknown'
            END as login_type,
            qa.score,
            qa.total_questions,
            qa.start_time,
            qa.end_time,
            qa.status,
            sq.access_code,
            sq.schedule_id,
            CASE WHEN sq.schedule_id IS NOT NULL THEN 'Scheduled' ELSE 'Direct' END as access_type,
            (SELECT COUNT(*) FROM quiz_responses qr WHERE qr.attempt_id = qa.attempt_id AND qr.is_correct = 1) as correct_answers
        FROM quiz_attempts qa
        LEFT JOIN users u ON qa.user_email = u.email
        LEFT JOIN gmail_users g ON qa.user_email = g.email
        LEFT JOIN scheduled_quizzes sq ON qa.schedule_id = sq.schedule_id
        WHERE qa.quizid = ?
        ORDER BY qa.score DESC, qa.end_time DESC";
} else {
    // Fallback query without gmail_users
    $attempts_query = "
        SELECT 
            qa.attempt_id,
            u.name as user_name,
            qa.user_email,
            'Direct' as login_type,
            qa.score,
            qa.total_questions,
            qa.start_time,
            qa.end_time,
            qa.status,
            sq.access_code,
            sq.schedule_id,
            CASE WHEN sq.schedule_id IS NOT NULL THEN 'Scheduled' ELSE 'Direct' END as access_type,
            (SELECT COUNT(*) FROM quiz_responses qr WHERE qr.attempt_id = qa.attempt_id AND qr.is_correct = 1) as correct_answers
        FROM quiz_attempts qa
        LEFT JOIN users u ON qa.user_email = u.email
        LEFT JOIN scheduled_quizzes sq ON qa.schedule_id = sq.schedule_id
        WHERE qa.quizid = ?
        ORDER BY qa.score DESC, qa.end_time DESC";
}
    
$stmt = $con->prepare($attempts_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$attempts_result = $stmt->get_result();

// Calculate overall statistics
$total_attempts = $attempts_result->num_rows;
$avg_score = 0;
$highest_score = 0;
$lowest_score = 100;
$completed_attempts = 0;
$scheduled_attempts = 0;
$total_correct_answers = 0;

if ($total_attempts > 0) {
    $attempts_result->data_seek(0);
    while ($attempt = $attempts_result->fetch_assoc()) {
        $avg_score += $attempt['score'];
        if ($attempt['score'] > $highest_score) $highest_score = $attempt['score'];
        if ($attempt['score'] < $lowest_score) $lowest_score = $attempt['score'];
        if ($attempt['status'] == 'completed') $completed_attempts++;
        if ($attempt['access_type'] == 'Scheduled') $scheduled_attempts++;
        $total_correct_answers += $attempt['correct_answers'];
    }
    $avg_score = round($avg_score / $total_attempts, 1);
    $attempts_result->data_seek(0);
}

// Get question statistics
try {
    $question_stats_query = "
        SELECT 
            q.ID as question_id,
            q.question,
            q.answer as correct_answer,
            COUNT(qr.response_id) as total_responses,
            SUM(CASE WHEN qr.is_correct = 1 THEN 1 ELSE 0 END) as correct_responses,
            ROUND((SUM(CASE WHEN qr.is_correct = 1 THEN 1 ELSE 0 END) / COUNT(qr.response_id)) * 100, 1) as correct_percentage
        FROM quizes q
        LEFT JOIN quiz_responses qr ON q.ID = qr.question_id
        WHERE q.quizid = ?
        GROUP BY q.ID, q.question, q.answer
        ORDER BY correct_percentage ASC";
        
    $stmt = $con->prepare($question_stats_query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $question_stats_result = $stmt->get_result();
} catch (Exception $e) {
    // If there's an error, set to null so we can handle it in the view
    $question_stats_result = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Statistics: <?php echo htmlspecialchars($quiz_details['quizname']); ?></title>
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
    <link rel="stylesheet" href="nav.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 25px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        
        h2 {
            color: #3498db;
            margin-top: 30px;
            margin-bottom: 15px;
        }

        .quiz-header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .quiz-title {
            font-size: 1.8em;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .quiz-category {
            color: #7f8c8d;
            font-size: 1.1em;
            margin-bottom: 15px;
        }

        .quiz-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .stat-card {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9em;
        }

        .search-bar {
            margin-bottom: 20px;
            width: 100%;
            display: flex;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: none;
            font-size: 16px;
        }
        
        .search-button {
            padding: 12px 20px;
            background: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        
        .search-button:hover {
            background: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #3498db;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        tr:hover {
            background: #f1f8fe;
        }

        .score {
            font-weight: bold;
            padding: 8px 12px;
            border-radius: 6px;
            text-align: center;
            display: inline-block;
            min-width: 70px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .badge-primary {
            background: #3498db;
            color: white;
        }
        
        .badge-secondary {
            background: #7f8c8d;
            color: white;
        }
        
        .badge-scheduled {
            background: #9b59b6;
            color: white;
            font-weight: bold;
            padding: 6px 10px;
            font-size: 0.9em;
        }
        
        .access-code {
            font-family: monospace;
            background: #f1f1f1;
            padding: 4px 7px;
            border-radius: 4px;
            font-size: 0.9em;
            color: #2c3e50;
            border: 1px solid #ddd;
            display: inline-block;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-completed {
            background: #2ecc71;
            color: white;
        }

        .status-in-progress {
            background: #f39c12;
            color: white;
        }

        .status-abandoned {
            background: #e74c3c;
            color: white;
        }
        
        .no-data {
            background: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-radius: 8px;
            color: #6c757d;
            font-style: italic;
            margin: 20px 0;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .tab {
            flex: 1;
            padding: 15px 0;
            text-align: center;
            background: #f8f9fa;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .tab.active {
            background: #3498db;
            color: white;
        }
        
        .tab:hover:not(.active) {
            background: #e9ecef;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Remove unused progress bar styles */
        .progress-bar-container, .progress-bar, .progress-low, .progress-medium, .progress-high {
            display: none;
        }
        
        .success-rate {
            margin-top: 15px;
            background: #f1f8fe;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .success-rate-info {
            font-size: 1.1em;
            color: #3498db;
            font-weight: 500;
            margin-top: 5px;
        }
        
        .question-answer {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            color: #2c3e50;
            border-left: 4px solid #3498db;
            font-weight: 500;
        }
        
        .back-button {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin-bottom: 20px;
            transition: background 0.3s;
        }
        
        .back-button:hover {
            background: #2980b9;
        }

        .user-name, .user-email, .total-attempts {
            color: #333 !important;
            font-weight: 500;
        }

        .quiz-date, .quiz-duration {
            color: #555;
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 5px 8px;
            border-radius: 4px;
            display: inline-block;
            font-size: 0.95em;
        }

        .badge-gmail {
            background: #DB4437;
            color: white;
        }
        
        .badge-direct {
            background: #4285F4;
            color: white;
        }
        
        .question-card {
            background: white;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .question-text {
            font-size: 1.1em;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php include "components/header.php"; ?>

    <div class="container">
        <a href="quiz_statistics.php" class="back-button">← Back to Statistics</a>
        
        <div class="quiz-header">
            <div class="quiz-title"><?php echo htmlspecialchars($quiz_details['quizname']); ?></div>
            <div class="quiz-category">Category: <?php echo htmlspecialchars($quiz_details['category']); ?></div>
            
            <div class="quiz-stats">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $quiz_details['total_questions']; ?></div>
                    <div class="stat-label">Total Questions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $total_attempts; ?></div>
                    <div class="stat-label">Total Attempts</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?php 
                            $avg_points = $avg_score ? round(($avg_score / 100) * $quiz_details['total_questions'], 1) : 0;
                            echo $avg_points . ' / ' . $quiz_details['total_questions']; 
                        ?>
                    </div>
                    <div class="stat-label">Average Points</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?php 
                            $highest_points = $highest_score ? round(($highest_score / 100) * $quiz_details['total_questions']) : 0;
                            echo $highest_points . ' / ' . $quiz_details['total_questions']; 
                        ?>
                    </div>
                    <div class="stat-label">Highest Points</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $completed_attempts; ?></div>
                    <div class="stat-label">Completed Attempts</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $scheduled_attempts; ?></div>
                    <div class="stat-label">Scheduled Attempts</div>
                </div>
            </div>
        </div>
        
        <div class="tabs">
            <button class="tab active" onclick="openTab(event, 'attempts')">Attempts</button>
            <button class="tab" onclick="openTab(event, 'questions')">Question Analysis</button>
        </div>
        
        <!-- Attempts Tab -->
        <div id="attempts" class="tab-content active">
            <div class="search-bar">
                <input type="text" id="userSearch" class="search-input" placeholder="Search by name or email...">
                <button class="search-button" onclick="searchUsers()">Search</button>
            </div>
            
            <h2>Quiz Attempts</h2>
            
            <table id="attempts-table">
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Login Type</th>
                        <th>Points Scored</th>
                        <th>Date</th>
                        <th>Duration</th>
                        <th>Access Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
    <?php if ($total_attempts > 0): ?>
        <?php while ($attempt = $attempts_result->fetch_assoc()): ?>
            <tr>
                <td><strong class="user-name"><?php echo htmlspecialchars((string)($attempt['user_name'] ?? 'Unknown User')); ?></strong></td>
                <td><span class="user-email"><?php echo htmlspecialchars((string)($attempt['user_email'] ?? 'No Email')); ?></span></td>
                <td>
                    <span class="badge <?php echo ($attempt['login_type'] ?? '') === 'Gmail' ? 'badge-gmail' : 'badge-direct'; ?>">
                        <?php echo htmlspecialchars((string)($attempt['login_type'] ?? 'Direct')); ?>
                    </span>
                </td>
                <td><span class="score">
                    <?php 
                        $points = isset($attempt['score']) && isset($attempt['total_questions']) 
                            ? round(($attempt['score'] * $attempt['total_questions']) / 100) 
                            : 0;
                        $total = $attempt['total_questions'] ?? 0;
                        echo $points . ' / ' . $total; 
                    ?>
                </span></td>
                <td><span class="quiz-date"><?php echo date('M d, Y H:i', strtotime($attempt['end_time'] ?? $attempt['start_time'] ?? 'now')); ?></span></td>
                <td>
                    <?php 
                        if (!empty($attempt['start_time']) && !empty($attempt['end_time'])) {
                            $start = new DateTime($attempt['start_time']);
                            $end = new DateTime($attempt['end_time']);
                            $duration = $start->diff($end);
                            echo '<span class="quiz-duration">' . $duration->format('%H:%I:%S') . '</span>';
                        } else {
                            echo '<span class="quiz-duration">-</span>';
                        }
                    ?>
                </td>
                <td>
                    <?php if (($attempt['access_type'] ?? '') == 'Scheduled'): ?>
                        <span class="badge badge-scheduled">Scheduled</span>
                        <span class="access-code"><?php echo htmlspecialchars((string)($attempt['access_code'] ?? '')); ?></span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Direct</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="status-badge status-<?php echo strtolower((string)($attempt['status'] ?? 'unknown')); ?>">
                        <?php echo ucfirst(htmlspecialchars((string)($attempt['status'] ?? 'Unknown'))); ?>
                    </span>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="8" class="no-data">No attempts recorded for this quiz yet.</td>
        </tr>
    <?php endif; ?>
</tbody>
            </table>
        </div>
        
        <!-- Questions Tab -->
        <div id="questions" class="tab-content">
            <h2>Question Performance Analysis</h2>
            
            <?php if ($question_stats_result && $question_stats_result->num_rows > 0): ?>
                <?php while ($question = $question_stats_result->fetch_assoc()): ?>
                    <div class="question-card">
                        <div class="question-text"><?php echo htmlspecialchars($question['question']); ?></div>
                        
                        <div class="question-answer">
                            <strong>Correct Answer:</strong> <?php echo htmlspecialchars($question['correct_answer']); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-data">
                    <p>No question data available for this quiz yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function openTab(evt, tabName) {
            // Hide all tab content
            const tabContents = document.getElementsByClassName("tab-content");
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].style.display = "none";
            }
            
            // Remove active class from all tabs
            const tabs = document.getElementsByClassName("tab");
            for (let i = 0; i < tabs.length; i++) {
                tabs[i].className = tabs[i].className.replace(" active", "");
            }
            
            // Show the selected tab content and add the active class to the button
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
        
        function searchUsers() {
            const input = document.getElementById('userSearch');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('attempts-table');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) { // Skip header row
                const row = rows[i];
                const nameCell = row.cells[0];
                const emailCell = row.cells[1];
                
                if (nameCell && emailCell) {
                    const nameText = nameCell.textContent || nameCell.innerText;
                    const emailText = emailCell.textContent || emailCell.innerText;
                    
                    if (nameText.toUpperCase().indexOf(filter) > -1 || 
                        emailText.toUpperCase().indexOf(filter) > -1) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                }
            }
        }
        
        // Color-code scores
        function colorScores() {
            const scores = document.querySelectorAll('.score');
            scores.forEach(score => {
                // Extract the actual score from the "X / Y" format
                const scoreText = score.textContent.trim();
                const parts = scoreText.split('/');
                if (parts.length !== 2) return;
                
                const points = parseInt(parts[0].trim());
                const total = parseInt(parts[1].trim());
                
                // Calculate percentage for coloring
                const percentage = (points / total) * 100;
                
                if (percentage === 100) {
                    score.style.backgroundColor = '#2ecc71'; // Green for perfect scores
                } else if (percentage >= 70) {
                    score.style.backgroundColor = '#3498db'; // Blue for good scores
                } else if (percentage >= 40) {
                    score.style.backgroundColor = '#f39c12'; // Orange for average scores
                } else {
                    score.style.backgroundColor = '#e74c3c'; // Red for poor scores
                }
                score.style.color = 'white';
                score.style.fontWeight = 'bold';
            });
        }
        
        // Run when page loads
        document.addEventListener('DOMContentLoaded', function() {
            colorScores();
            
            // Add event listener for search input
            document.getElementById('userSearch').addEventListener('keyup', searchUsers);
        });
    </script>
</body>
</html>
