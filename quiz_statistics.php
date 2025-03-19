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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Quiz Statistics</title>
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

        .setup-card {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .setup-card h2 {
            color: #721c24;
            margin-top: 0;
        }

        .setup-card a {
            display: inline-block;
            background: #dc3545;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 15px;
        }

        .setup-card a:hover {
            background: #c82333;
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

        .user-name, .user-email, .total-attempts {
            color: #333 !important;
        }
        
        /* Ensure all text in table cells has good contrast */
        td {
            color: #333;
        }
        
        .score {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 4px;
            background: #f1f8fe;
            color: #2980b9;
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
        }
        
        .access-code {
            font-family: monospace;
            background: #f1f1f1;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 0.9em;
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
        
        .scheduled-quiz-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .scheduled-quiz-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .scheduled-quiz-title {
            font-size: 1.2em;
            font-weight: bold;
            color: #2c3e50;
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
            color: #3498db;
            margin-bottom: 5px;
        }
        
        .detail-label {
            color: #7f8c8d;
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
                <button class="tab" onclick="openTab(event, 'scheduled-quizzes')">Scheduled Quizzes</button>
            </div>
            
            <!-- Quiz Attempts Tab -->
            <div id="quiz-attempts" class="tab-content active">
                <div class="search-bar">
                    <input type="text" id="attemptSearch" class="search-input" placeholder="Search by name, email, quiz or access code...">
                    <button class="search-button" onclick="searchAttempts()">Search</button>
                </div>
                
                <h2>All Quiz Attempts</h2>
                
                <table id="attempts-table">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Quiz Name</th>
                            <th>Score</th>
                            <th>Correct / Total</th>
                            <th>Access Method</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($all_attempts_result && $all_attempts_result->num_rows > 0): ?>
                            <?php while ($attempt = $all_attempts_result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($attempt['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($attempt['email']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $attempt['login_type'] === 'Gmail' ? 'badge-gmail' : 'badge-direct'; ?>">
                                            <?php echo $attempt['login_type']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($attempt['quizname'] ?? 'Unknown Quiz'); ?></td>
                                    <td><span class="score"><?php echo $attempt['score']; ?>%</span></td>
                                    <td><?php echo $attempt['correct_answers']; ?> / <?php echo $attempt['total_questions']; ?></td>
                                    <td>
                                        <?php if ($attempt['access_type'] == 'Scheduled'): ?>
                                            <span class="badge badge-scheduled">Scheduled</span>
                                            <span class="access-code"><?php echo $attempt['access_code']; ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Direct</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($attempt['end_time'] ?? $attempt['start_time'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($attempt['status']); ?>">
                                            <?php echo ucfirst($attempt['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="no-data">No quiz attempts recorded yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
                            <th>Quizzes Taken</th>
                            <th>Scheduled Quizzes</th>
                            <th>Average Score</th>
                            <th>Highest Score</th>
                            <th>Last Attempt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($user_stats_result && $user_stats_result->num_rows > 0): ?>
                            <?php while($user = $user_stats_result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo $user['total_attempts'] ?: 0; ?></td>
                                    <td>
                                        <?php if ($user['scheduled_attempts'] > 0): ?>
                                            <span class="badge badge-scheduled"><?php echo $user['scheduled_attempts']; ?></span>
                                        <?php else: ?>
                                            0
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="score"><?php echo $user['average_score'] ? number_format($user['average_score'], 1) : '0.0'; ?>%</span></td>
                                    <td><span class="score"><?php echo $user['highest_score'] ?: '0'; ?>%</span></td>
                                    <td><?php echo $user['last_attempt'] ? date('M d, Y H:i', strtotime($user['last_attempt'])) : '-'; ?></td>
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
            
            <!-- Scheduled Quizzes Tab -->
            <div id="scheduled-quizzes" class="tab-content">
                <h2>Scheduled Quiz Statistics</h2>
                
                <?php if ($scheduled_stats_result && $scheduled_stats_result->num_rows > 0): ?>
                    <?php while($quiz = $scheduled_stats_result->fetch_assoc()): ?>
                        <div class="scheduled-quiz-card">
                            <div class="scheduled-quiz-header">
                                <div class="scheduled-quiz-title"><?php echo htmlspecialchars($quiz['quizname']); ?></div>
                                <div>
                                    <span class="badge badge-scheduled">Access Code:</span>
                                    <span class="access-code"><?php echo htmlspecialchars($quiz['access_code']); ?></span>
                                </div>
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
                const quizCell = row.cells[2];
                const accessCell = row.cells[5];
                
                if (nameCell && emailCell && quizCell && accessCell) {
                    const nameText = nameCell.textContent || nameCell.innerText;
                    const emailText = emailCell.textContent || emailCell.innerText;
                    const quizText = quizCell.textContent || quizCell.innerText;
                    const accessText = accessCell.textContent || accessCell.innerText;
                    
                    if (nameText.toUpperCase().indexOf(filter) > -1 || 
                        emailText.toUpperCase().indexOf(filter) > -1 ||
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
        
        // Color-code scores
        function colorScores() {
            const scores = document.querySelectorAll('.score');
            scores.forEach(score => {
                const value = parseInt(score.textContent);
                if (value === 100) {
                    score.style.backgroundColor = '#2ecc71'; // Green for perfect scores
                } else if (value >= 70) {
                    score.style.backgroundColor = '#3498db'; // Blue for good scores
                } else if (value >= 40) {
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
            
            // Add event listeners for search inputs
            document.getElementById('attemptSearch').addEventListener('keyup', searchAttempts);
            document.getElementById('userSearch').addEventListener('keyup', searchUsers);
        });
    </script>
</body>
</html>