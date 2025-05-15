<?php
include "dbconnect.php";

// Check if user is logged in and is admin
if ($_SESSION['status'] != "admin" || !isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Check if the tables exist
$tablesExist = true;
$gmailUsersExist = true;

try {
    $checkTable = $con->query("SELECT 1 FROM quiz_attempts LIMIT 1");
} catch (Exception $e) {
    $tablesExist = false;
}

// Check if gmail_users table exists
try {
    $checkGmailTable = $con->query("SELECT 1 FROM gmail_users LIMIT 1");
} catch (Exception $e) {
    $gmailUsersExist = false;
}

if ($tablesExist) {
    // Fetch user performance statistics
    if ($gmailUsersExist) {
        $user_stats_query = "
            SELECT 
                COALESCE(u.name, g.name) as name,
                COALESCE(u.email, g.email) as email,
                CASE 
                    WHEN g.email IS NOT NULL THEN 'Gmail'
                    ELSE 'Direct'
                END as login_type,
                COUNT(DISTINCT qa.attempt_id) as total_attempts,
                ROUND(AVG(qa.score), 1) as average_score,
                MAX(qa.score) as highest_score,
                COUNT(CASE WHEN qa.status = 'completed' THEN 1 END) as completed_quizzes,
                MAX(qa.end_time) as last_attempt,
                COUNT(DISTINCT CASE WHEN qa.schedule_id IS NOT NULL THEN qa.attempt_id ELSE NULL END) as scheduled_attempts
            FROM (SELECT email, name FROM users 
                  UNION 
                  SELECT email, name FROM gmail_users) as combined_users
            LEFT JOIN users u ON combined_users.email = u.email
            LEFT JOIN gmail_users g ON combined_users.email = g.email
            LEFT JOIN quiz_attempts qa ON combined_users.email = qa.user_email
            GROUP BY COALESCE(u.name, g.name), COALESCE(u.email, g.email), 
                     CASE WHEN g.email IS NOT NULL THEN 'Gmail' ELSE 'Direct' END
            ORDER BY COUNT(DISTINCT qa.attempt_id) DESC, AVG(qa.score) DESC";
    } else {
        // Fallback query without gmail_users
        $user_stats_query = "
            SELECT 
                u.name,
                u.email,
                'Direct' as login_type,
                COUNT(DISTINCT qa.attempt_id) as total_attempts,
                ROUND(AVG(qa.score), 1) as average_score,
                MAX(qa.score) as highest_score,
                COUNT(CASE WHEN qa.status = 'completed' THEN 1 END) as completed_quizzes,
                MAX(qa.end_time) as last_attempt,
                COUNT(DISTINCT CASE WHEN qa.schedule_id IS NOT NULL THEN qa.attempt_id ELSE NULL END) as scheduled_attempts
            FROM users u
            LEFT JOIN quiz_attempts qa ON u.email = qa.user_email
            GROUP BY u.name, u.email
            ORDER BY COUNT(DISTINCT qa.attempt_id) DESC, AVG(qa.score) DESC";
    }
    
    $user_stats_result = $con->query($user_stats_query);

    // Fetch list of all quiz attempts with scheduled quiz info
    if ($gmailUsersExist) {
        $all_attempts_query = "
            SELECT 
                qa.attempt_id,
                COALESCE(u.name, g.name) as name,
                COALESCE(u.email, g.email) as email,
                CASE 
                    WHEN g.email IS NOT NULL THEN 'Gmail'
                    ELSE 'Direct'
                END as login_type,
                qd.quizname,
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
            LEFT JOIN quizdetails qd ON CASE 
                WHEN qa.schedule_id IS NOT NULL THEN sq.quizid = qd.quizid 
                ELSE qa.quizid = qd.quizid 
            END
            ORDER BY qa.end_time DESC, qa.start_time DESC
            LIMIT 100";
    } else {
        // Fallback query without gmail_users
        $all_attempts_query = "
            SELECT 
                qa.attempt_id,
                u.name,
                u.email,
                'Direct' as login_type,
                qd.quizname,
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
            LEFT JOIN quizdetails qd ON CASE 
                WHEN qa.schedule_id IS NOT NULL THEN sq.quizid = qd.quizid 
                ELSE qa.quizid = qd.quizid 
            END
            ORDER BY qa.end_time DESC, qa.start_time DESC
            LIMIT 100";
    }
        
    $all_attempts_result = $con->query($all_attempts_query);

    // Fetch scheduled quizzes statistics
    $scheduled_stats_query = "
        SELECT 
            sq.schedule_id,
            qd.quizname,
            sq.start_time,
            sq.end_time,
            sq.access_code,
            COUNT(DISTINCT qa.user_email) as total_participants,
            IFNULL(AVG(qa.score), 0) as average_score,
            IFNULL(MAX(qa.score), 0) as highest_score,
            IFNULL(MIN(qa.score), 0) as lowest_score,
            COUNT(CASE WHEN qa.status = 'completed' THEN 1 END) as completed_attempts
        FROM scheduled_quizzes sq
        JOIN quizdetails qd ON sq.quizid = qd.quizid
        LEFT JOIN quiz_attempts qa ON sq.schedule_id = qa.schedule_id
        GROUP BY sq.schedule_id, qd.quizname, sq.start_time, sq.end_time, sq.access_code
        ORDER BY sq.start_time DESC";
        
    $scheduled_stats_result = $con->query($scheduled_stats_query);

    // New query for quiz performance statistics
    $quiz_stats_query = "
        SELECT 
            qd.quizid,
            qd.quizname,
            qd.category,
            COUNT(DISTINCT qa.attempt_id) as total_attempts,
            ROUND(AVG(qa.score), 1) as average_score,
            MAX(qa.score) as highest_score,
            MIN(qa.score) as lowest_score,
            COUNT(CASE WHEN qa.status = 'completed' THEN 1 END) as completed_attempts,
            (SELECT COUNT(*) FROM quizes q WHERE q.quizid = qd.quizid) as total_questions,
            MAX(qa.end_time) as last_attempt_date
        FROM quizdetails qd
        LEFT JOIN quiz_attempts qa ON qd.quizid = qa.quizid
        GROUP BY qd.quizid, qd.quizname, qd.category
        ORDER BY COUNT(DISTINCT qa.attempt_id) DESC, qd.quizname ASC";
        
    $quiz_stats_result = $con->query($quiz_stats_query);

    // Replace the existing difficulty stats section (around line 189) with:
    $difficulty_stats = null;
    $userHistoryTableExists = false;

    try {
        // Check if user_quiz_history table exists
        $tableCheck = $con->query("SHOW TABLES LIKE 'user_quiz_history'");
        if ($tableCheck->num_rows > 0) {
            $userHistoryTableExists = true;
            $difficulty_stats_query = "
                SELECT 
                    qh.difficulty_level,
                    COUNT(DISTINCT qh.question_id) as questions_attempted,
                    COUNT(DISTINCT qh.user_email) as unique_users,
                    qd.quizname
                FROM user_quiz_history qh
                JOIN quizdetails qd ON qh.quizid = qd.quizid
                GROUP BY qh.difficulty_level, qd.quizid, qd.quizname
                ORDER BY qh.difficulty_level";
            $difficulty_stats = $con->query($difficulty_stats_query);
        }
    } catch (Exception $e) {
        error_log("Error querying difficulty stats: " . $e->getMessage());
        $difficulty_stats = null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Quiz Statistics</title>
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background: var(--background);
            color: var(--text);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        h1 {
            color: var(--text);
            margin-bottom: 25px;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 10px;
        }
        
        h2 {
            color: #3498db;
            margin-top: 30px;
            margin-bottom: 15px;
        }

        .setup-card {
            background: rgba(231, 76, 60, 0.1);
            color: var(--text);
            border: 1px solid rgba(231, 76, 60, 0.3);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .setup-card h2 {
            color: var(--text);
            margin-top: 0;
        }

        .setup-card a {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .setup-card a:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .search-bar {
            margin-bottom: 20px;
            width: 100%;
            display: flex;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            border-radius: 4px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: none;
            font-size: 16px;
            background: transparent;
            color: var(--text);
        }
        
        .search-input::placeholder {
            color: var(--text-muted);
        }
        
        .search-button {
            padding: 12px 20px;
            background: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .search-button:hover {
            background: var(--primary-dark);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            background: rgba(74, 144, 226, 0.2);
            color: var(--text);
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }

        tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.02);
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .score {
            font-weight: bold;
            padding: 8px 12px;
            border-radius: 6px;
            text-align: center;
            display: inline-block;
            min-width: 80px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
            font-size: 1em;
            letter-spacing: 0.5px;
            color: white; /* Default white text */
        }
        
        /* Score level styling */
        .score-perfect {
            background-color: #2ecc71; /* Green for perfect scores */
        }
        
        .score-good {
            background-color: #3498db; /* Blue for good scores */
        }
        
        .score-average {
            background-color: #f39c12; /* Orange for average scores */
        }
        
        .score-poor {
            background-color: #e74c3c; /* Red for poor scores */
        }

        .user-name, .user-email, .total-attempts {
            color: var(--text) !important;
            font-weight: 500;
        }
        
        .quiz-date, .quiz-duration {
            color: var(--text-muted);
            font-family: 'Courier New', monospace;
            background: rgba(255, 255, 255, 0.05);
            padding: 5px 8px;
            border-radius: 4px;
            display: inline-block;
            font-size: 0.95em;
        }
        
        /* Ensure all text in table cells has good contrast */
        td {
            color: var(--text);
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
            background: rgba(255, 255, 255, 0.05);
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 1.2em;
            font-weight: bold;
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: inline-block;
            letter-spacing: 1px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        
        /* Add a highlight box around access code */
        .access-code-container {
            margin-top: 10px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
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
            background: rgba(255, 255, 255, 0.05);
            padding: 30px;
            text-align: center;
            border-radius: 8px;
            color: var(--text-muted);
            font-style: italic;
            margin: 20px 0;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .tab {
            flex: 1;
            padding: 15px 0;
            text-align: center;
            background: transparent;
            border: none;
            cursor: pointer;
            font-weight: bold;
            color: var(--text-muted);
            transition: all 0.3s ease;
        }
        
        .tab.active {
            background: var(--primary);
            color: white;
        }
        
        .tab:hover:not(.active) {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text);
        }
        
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .scheduled-quiz-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .scheduled-quiz-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 10px;
        }
        
        .scheduled-quiz-title {
            font-size: 1.2em;
            font-weight: bold;
            color: var(--text);
        }
        
        .scheduled-quiz-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .detail-item {
            text-align: center;
        }
        
        .detail-value {
            font-size: 1.5em;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .detail-label {
            color: var(--text-muted);
            font-size: 0.9em;
        }
        
        .badge-gmail {
            background: #DB4437;
            color: white;
        }
        
        .badge-direct {
            background: #4285F4;
            color: white;
        }

        /* Responsive Styles */
        @media screen and (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .search-bar {
                flex-direction: column;
                background: none;
                box-shadow: none;
                border: none;
            }

            .search-input {
                width: 100%;
                margin-bottom: 0.5rem;
                border-radius: 4px;
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
            }

            .search-button {
                width: 100%;
                border-radius: 4px;
            }

            .tabs {
                flex-direction: column;
                background: none;
                box-shadow: none;
                border: none;
                gap: 0.5rem;
            }

            .tab {
                border-radius: 4px;
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
            }

            /* Table Responsive Styles */
            table, thead, tbody, th, td, tr {
                display: block;
            }

            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            tr {
                margin-bottom: 1rem;
                background: rgba(255, 255, 255, 0.05) !important;
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 8px;
                padding: 0.5rem;
            }

            td {
                border: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
                position: relative;
                padding-left: 50%;
                text-align: right;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            td:last-child {
                border-bottom: none;
            }

            td:before {
                content: attr(data-label);
                position: relative;
                left: 0;
                width: 45%;
                padding-right: 10px;
                text-align: left;
                font-weight: bold;
                color: var(--text-muted);
            }

            .score {
                margin-left: auto;
            }

            .badge {
                margin-left: auto;
            }

            .scheduled-quiz-card {
                padding: 1rem;
            }

            .scheduled-quiz-details {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .scheduled-quiz-header {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }

            .access-code-container {
                flex-direction: column;
                gap: 0.5rem;
            }
        }

        /* Small mobile devices */
        @media screen and (max-width: 480px) {
            h1 {
                font-size: 1.5rem;
            }

            h2 {
                font-size: 1.2rem;
            }

            .detail-value {
                font-size: 1.2em;
            }

            td {
                padding: 0.75rem;
                font-size: 0.9rem;
            }

            .badge, .status-badge {
                font-size: 0.75em;
            }

            .score {
                min-width: 60px;
                font-size: 0.9em;
            }
        }

        .difficulty-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .stat-card {
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .stat-card.difficulty-easy { background: rgba(46, 204, 113, 0.1); }
        .stat-card.difficulty-medium { background: rgba(241, 196, 15, 0.1); }
        .stat-card.difficulty-intermediate { background: rgba(230, 126, 34, 0.1); }
        .stat-card.difficulty-hard { background: rgba(231, 76, 60, 0.1); }
    </style>
</head>
<body>
    <?php include "components/header.php"; ?>

    <div class="container">
        <h1>User Quiz Statistics</h1>
        
        <?php if (!$tablesExist): ?>
            <div class="setup-card">
                <h2>Setup Required</h2>
                <p>The database tables required for quiz statistics haven't been created yet.</p>
                <p>Click the button below to create the necessary tables to track quiz attempts and responses.</p>
                <a href="create_tracking_tables.php">Setup Quiz Tracking Tables</a>
            </div>
        <?php else: ?>
            
            <div class="tabs">
                <button class="tab active" onclick="openTab(event, 'quiz-attempts')">Quiz Attempts</button>
                <button class="tab" onclick="openTab(event, 'user-performance')">User Performance</button>
                <button class="tab" onclick="openTab(event, 'quiz-scores')">Quiz Scores</button>
                
            </div>
            
            <!-- Quiz Attempts Tab -->
            <div id="quiz-attempts" class="tab-content active">
                <div class="search-bar">
                    <input type="text" id="attemptSearch" class="search-input" placeholder="Search by name, email, quiz or access code...">
                    <button class="search-button" onclick="searchAttempts()">Search</button>
                </div>
                
                <h2>All Quiz Attempts</h2>
                <div class="attempts-table">
                    <table>
                        <thead>
                            <tr>
                                <th>USER NAME</th>
                                <th>EMAIL</th>
                                <th>LOGIN TYPE</th>
                                <th>QUIZ NAME</th>
                                <th>POINTS SCORED</th>
                                <th>ACCESS METHOD</th>
                                <th>DATE</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($attempt = $all_attempts_result->fetch_assoc()): ?>
                                <!-- ... existing table rows code ... -->
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- User Performance Tab -->
            <div id="user-performance" class="tab-content">
                <div class="search-bar">
                    <input type="text" id="userSearch" class="search-input" placeholder="Search by user name or email...">
                    <button class="search-button" onclick="searchUsers()">Search</button>
                </div>
                
                <h2>User Performance Summary</h2>
                
                <table id="user-summary-table">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Login Type</th>
                            <th>Quizzes Taken</th>
                            <th>Scheduled Quizzes</th>
                            <th>Average Points</th>
                            <th>Best Score</th>
                            <th>Last Attempt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($user_stats_result && $user_stats_result->num_rows > 0): ?>
                            <?php while($user = $user_stats_result->fetch_assoc()): ?>
                                <tr>
                                    <td data-label="User Name"><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                                    <td data-label="Email"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td data-label="Login Type">
                                        <span class="badge <?php echo $user['login_type'] === 'Gmail' ? 'badge-gmail' : 'badge-direct'; ?>">
                                            <?php echo $user['login_type']; ?>
                                        </span>
                                    </td>
                                    <td data-label="Quizzes Taken"><?php echo $user['total_attempts'] ?: 0; ?></td>
                                    <td data-label="Scheduled Quizzes">
                                        <?php if ($user['scheduled_attempts'] > 0): ?>
                                            <span class="badge badge-scheduled"><?php echo $user['scheduled_attempts']; ?></span>
                                        <?php else: ?>
                                            0
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Average Points"><span class="score <?php 
                                        $avgScore = $user['average_score'] ? number_format($user['average_score'], 1) : '0.0';
                                        if ($avgScore == 100) echo 'score-perfect';
                                        elseif ($avgScore >= 70) echo 'score-good';
                                        elseif ($avgScore >= 40) echo 'score-average';
                                        else echo 'score-poor';
                                    ?>"><?php echo $avgScore; ?>%</span></td>
                                    <td data-label="Best Score"><span class="score <?php 
                                        $highScore = $user['highest_score'] ?: '0';
                                        if ($highScore == 100) echo 'score-perfect';
                                        elseif ($highScore >= 70) echo 'score-good';
                                        elseif ($highScore >= 40) echo 'score-average';
                                        else echo 'score-poor';
                                    ?>"><?php echo $highScore; ?>%</span></td>
                                    <td data-label="Last Attempt"><?php echo $user['last_attempt'] ? '<span class="quiz-date">' . date('M d, Y H:i', strtotime($user['last_attempt'])) . '</span>' : '-'; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="no-data">No user performance data available yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Quiz Scores Tab - NEW -->
            <div id="quiz-scores" class="tab-content">
                <div class="search-bar">
                    <input type="text" id="quizSearch" class="search-input" placeholder="Search by quiz name or category...">
                    <button class="search-button" onclick="searchQuizzes()">Search</button>
                </div>
                
                <h2>Quiz Performance Summary</h2>
                
                <table id="quiz-summary-table">
                    <thead>
                        <tr>
                            <th>Quiz Name</th>
                            <th>Category</th>
                            <th>Questions</th>
                            <th>Attempts</th>
                            <th>Completion Rate</th>
                            <th>Average Points</th>
                            <th>Highest Points</th>
                            <th>Last Attempt</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($quiz_stats_result && $quiz_stats_result->num_rows > 0): ?>
                            <?php while($quiz = $quiz_stats_result->fetch_assoc()): ?>
                                <tr>
                                    <td data-label="Quiz Name"><strong><?php echo htmlspecialchars($quiz['quizname']); ?></strong></td>
                                    <td data-label="Category"><?php echo htmlspecialchars($quiz['category']); ?></td>
                                    <td data-label="Questions"><?php echo $quiz['total_questions']; ?></td>
                                    <td data-label="Attempts"><?php echo $quiz['total_attempts'] ?: 0; ?></td>
                                    <td data-label="Completion Rate">
                                        <?php 
                                            $completion_rate = $quiz['total_attempts'] > 0 
                                                ? round(($quiz['completed_attempts'] / $quiz['total_attempts']) * 100) 
                                                : 0;
                                            echo $completion_rate . '%';
                                        ?>
                                    </td>
                                    <td data-label="Average Points">
                                        <?php 
                                            $avg_points = $quiz['average_score'] ? 
                                                round(($quiz['average_score'] / 100) * $quiz['total_questions'], 1) : 0;
                                                    
                                            $avg_percentage = $quiz['average_score'] ?: 0;
                                            
                                            $scoreClass = 'score ';
                                            if ($avg_percentage == 100) {
                                                $scoreClass .= 'score-perfect';
                                            } elseif ($avg_percentage >= 70) {
                                                $scoreClass .= 'score-good';
                                            } elseif ($avg_percentage >= 40) {
                                                $scoreClass .= 'score-average';
                                            } else {
                                                $scoreClass .= 'score-poor';
                                            }
                                            
                                            echo '<span class="' . $scoreClass . '">' . $avg_points . ' / ' . $quiz['total_questions'] . '</span>'; 
                                        ?>
                                    </td>
                                    <td data-label="Highest Points">
                                        <?php 
                                            $highest_points = $quiz['highest_score'] ? 
                                                round(($quiz['highest_score'] / 100) * $quiz['total_questions']) : 0;
                                                    
                                            $highest_percentage = $quiz['highest_score'] ?: 0;
                                            
                                            $scoreClass = 'score ';
                                            if ($highest_percentage == 100) {
                                                $scoreClass .= 'score-perfect';
                                            } elseif ($highest_percentage >= 70) {
                                                $scoreClass .= 'score-good';
                                            } elseif ($highest_percentage >= 40) {
                                                $scoreClass .= 'score-average';
                                            } else {
                                                $scoreClass .= 'score-poor';
                                            }
                                            
                                            echo '<span class="' . $scoreClass . '">' . $highest_points . ' / ' . $quiz['total_questions'] . '</span>'; 
                                        ?>
                                    </td>
                                    <td data-label="Last Attempt">
                                        <?php echo $quiz['last_attempt_date'] 
                                            ? '<span class="quiz-date">' . date('M d, Y H:i', strtotime($quiz['last_attempt_date'])) . '</span>' 
                                            : '-'; 
                                        ?>
                                    </td>
                                    <td data-label="Actions">
                                        <a href="quiz_detail_stats.php?id=<?php echo $quiz['quizid']; ?>" class="badge badge-primary">View Details</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="no-data">No quiz data available yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Scheduled Quizzes Tab -->
            <div id="scheduled-quizzes" class="tab-content">
                <h2>Scheduled Quiz Statistics</h2>
                
                <?php if ($scheduled_stats_result && $scheduled_stats_result->num_rows > 0): ?>
                    <?php while($quiz = $scheduled_stats_result->fetch_assoc()): ?>
                        <div class="scheduled-quiz-card">
                            <div class="scheduled-quiz-header">
                                <div class="scheduled-quiz-title"><?php echo htmlspecialchars($quiz['quizname']); ?></div>
                            </div>
                            
                            <div class="access-code-container">
                                <span class="badge badge-scheduled">Access Code:</span>&nbsp;
                                <span class="access-code"><?php echo htmlspecialchars($quiz['access_code']); ?></span>
                            </div>
                            
                            <div>
                                <strong>Schedule:</strong> 
                                <?php echo date('M d, Y H:i', strtotime($quiz['start_time'])); ?> - 
                                <?php echo date('M d, Y H:i', strtotime($quiz['end_time'])); ?>
                            </div>
                            
                            <div class="scheduled-quiz-details">
                                <div class="detail-item">
                                    <div class="detail-value"><?php echo $quiz['total_participants']; ?></div>
                                    <div class="detail-label">Total Participants</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-value"><?php echo number_format($quiz['average_score'], 1); ?>%</div>
                                    <div class="detail-label">Average Score</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-value"><?php echo $quiz['highest_score']; ?>%</div>
                                    <div class="detail-label">Highest Score</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-value"><?php echo $quiz['lowest_score']; ?>%</div>
                                    <div class="detail-label">Lowest Score</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-value"><?php echo $quiz['completed_attempts']; ?></div>
                                    <div class="detail-label">Completed Attempts</div>
                                </div>
                            </div>
                            
                            <div style="text-align: right; margin-top: 15px;">
                                <a href="scheduled_quiz_details.php?id=<?php echo $quiz['schedule_id']; ?>" class="badge badge-primary">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-data">
                        <p>No scheduled quizzes available yet.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Difficulty Level Statistics -->
            <?php if ($userHistoryTableExists && $difficulty_stats && $difficulty_stats->num_rows > 0): ?>
                <div class="stats-section">
                    <h3>Performance by Difficulty Level</h3>
                    <div class="difficulty-stats">
                        <?php while($stat = $difficulty_stats->fetch_assoc()): ?>
                            <div class="stat-card difficulty-<?php echo htmlspecialchars($stat['difficulty_level']); ?>">
                                <h4><?php echo ucfirst(htmlspecialchars($stat['difficulty_level'])); ?> Level</h4>
                                <p>Questions Attempted: <?php echo htmlspecialchars($stat['questions_attempted']); ?></p>
                                <p>Unique Users: <?php echo htmlspecialchars($stat['unique_users']); ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php elseif ($tablesExist): ?>
                <div class="stats-section">
                    <h3>Performance by Difficulty Level</h3>
                    <div class="no-data">No difficulty level statistics available yet.</div>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
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
    
        function searchAttempts() {
            const input = document.getElementById('attemptSearch');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('attempts-table');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) { // Skip header row
                const row = rows[i];
                const nameCell = row.cells[0];
                const emailCell = row.cells[1];
                const loginTypeCell = row.cells[2];
                const quizCell = row.cells[3];
                const accessCell = row.cells[6];
                
                if (nameCell && emailCell && loginTypeCell && quizCell && accessCell) {
                    const nameText = nameCell.textContent || nameCell.innerText;
                    const emailText = emailCell.textContent || emailCell.innerText;
                    const loginTypeText = loginTypeCell.textContent || loginTypeCell.innerText;
                    const quizText = quizCell.textContent || quizCell.innerText;
                    const accessText = accessCell.textContent || accessCell.innerText;
                    
                    if (nameText.toUpperCase().indexOf(filter) > -1 || 
                        emailText.toUpperCase().indexOf(filter) > -1 ||
                        loginTypeText.toUpperCase().indexOf(filter) > -1 ||
                        quizText.toUpperCase().indexOf(filter) > -1 ||
                        accessText.toUpperCase().indexOf(filter) > -1) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                }
            }
        }
        
        function searchUsers() {
            const input = document.getElementById('userSearch');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('user-summary-table');
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
        
        function searchQuizzes() {
            const input = document.getElementById('quizSearch');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('quiz-summary-table');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) { // Skip header row
                const row = rows[i];
                const nameCell = row.cells[0];
                const categoryCell = row.cells[1];
                
                if (nameCell && categoryCell) {
                    const nameText = nameCell.textContent || nameCell.innerText;
                    const categoryText = categoryCell.textContent || categoryCell.innerText;
                    
                    if (nameText.toUpperCase().indexOf(filter) > -1 || 
                        categoryText.toUpperCase().indexOf(filter) > -1) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                }
            }
        }
        
        // Run when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners for search inputs
            document.getElementById('attemptSearch').addEventListener('keyup', searchAttempts);
            document.getElementById('userSearch').addEventListener('keyup', searchUsers);
            document.getElementById('quizSearch').addEventListener('keyup', searchQuizzes);
        });
    </script>
</body>
</html>