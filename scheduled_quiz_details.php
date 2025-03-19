<?php
include "dbconnect.php";

// Check if user is logged in and is admin
if ($_SESSION['status'] != "admin" || !isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Check if schedule_id is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: quiz_statistics.php");
    exit();
}

$schedule_id = $_GET['id'];

// Get scheduled quiz information
$quiz_info_query = "
    SELECT 
        sq.*,
        qd.quizname,
        qd.description as quizdescription,
        u.name as creator_name
    FROM scheduled_quizzes sq
    JOIN quizdetails qd ON sq.quizid = qd.quizid
    LEFT JOIN users u ON sq.created_by = u.email
    WHERE sq.schedule_id = ?";

$stmt = $con->prepare($quiz_info_query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$quiz_info_result = $stmt->get_result();

if ($quiz_info_result->num_rows == 0) {
    // Scheduled quiz not found
    header("Location: quiz_statistics.php");
    exit();
}

$quiz_info = $quiz_info_result->fetch_assoc();

// Get all attempts for this scheduled quiz
$attempts_query = "
    SELECT 
        qa.attempt_id,
        u.name,
        u.email,
        qd.quizname,
        qa.score,
        qa.total_questions,
        qa.start_time,
        qa.end_time,
        qa.status,
        (SELECT COUNT(*) FROM quiz_responses qr WHERE qr.attempt_id = qa.attempt_id AND qr.is_correct = 1) as correct_answers
    FROM quiz_attempts qa
    JOIN users u ON qa.user_email = u.email
    JOIN quizdetails qd ON qa.quizid = qd.quizid
    WHERE qa.schedule_id = ?
    ORDER BY qa.score DESC, qa.end_time DESC";
    
$stmt = $con->prepare($attempts_query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$attempts_result = $stmt->get_result();

// Calculate statistics
$total_attempts = $attempts_result->num_rows;
$avg_score = 0;
$highest_score = 0;
$lowest_score = 100;
$completed_attempts = 0;
$abandoned_attempts = 0;
$in_progress_attempts = 0;

if ($total_attempts > 0) {
    $score_sum = 0;
    $all_attempts = [];
    
    while ($attempt = $attempts_result->fetch_assoc()) {
        $all_attempts[] = $attempt;
        
        $score_sum += $attempt['score'];
        $highest_score = max($highest_score, $attempt['score']);
        $lowest_score = min($lowest_score, $attempt['score']);
        
        if ($attempt['status'] == 'completed') {
            $completed_attempts++;
        } elseif ($attempt['status'] == 'abandoned') {
            $abandoned_attempts++;
        } else {
            $in_progress_attempts++;
        }
    }
    
    $avg_score = $score_sum / $total_attempts;
    
    // Reset result pointer
    $attempts_result->data_seek(0);
}

// Get time distribution data
$time_distribution_query = "
    SELECT 
        HOUR(start_time) as hour,
        COUNT(*) as count
    FROM quiz_attempts
    WHERE schedule_id = ?
    GROUP BY HOUR(start_time)
    ORDER BY hour";
    
$stmt = $con->prepare($time_distribution_query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$time_distribution_result = $stmt->get_result();

$time_data = [];
while ($time = $time_distribution_result->fetch_assoc()) {
    $time_data[$time['hour']] = $time['count'];
}

// Convert to JSON for chart
$time_labels = [];
$time_values = [];
for ($i = 0; $i < 24; $i++) {
    $time_labels[] = sprintf("%02d:00", $i);
    $time_values[] = isset($time_data[$i]) ? $time_data[$i] : 0;
}

$time_labels_json = json_encode($time_labels);
$time_values_json = json_encode($time_values);

// Get score distribution data
$score_distribution_query = "
    SELECT 
        CASE
            WHEN score BETWEEN 0 AND 9 THEN '0-9'
            WHEN score BETWEEN 10 AND 19 THEN '10-19'
            WHEN score BETWEEN 20 AND 29 THEN '20-29'
            WHEN score BETWEEN 30 AND 39 THEN '30-39'
            WHEN score BETWEEN 40 AND 49 THEN '40-49'
            WHEN score BETWEEN 50 AND 59 THEN '50-59'
            WHEN score BETWEEN 60 AND 69 THEN '60-69'
            WHEN score BETWEEN 70 AND 79 THEN '70-79'
            WHEN score BETWEEN 80 AND 89 THEN '80-89'
            WHEN score BETWEEN 90 AND 99 THEN '90-99'
            WHEN score = 100 THEN '100'
        END as score_range,
        COUNT(*) as count
    FROM quiz_attempts
    WHERE schedule_id = ? AND status = 'completed'
    GROUP BY score_range
    ORDER BY score_range";
    
$stmt = $con->prepare($score_distribution_query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$score_distribution_result = $stmt->get_result();

$score_ranges = ['0-9', '10-19', '20-29', '30-39', '40-49', '50-59', '60-69', '70-79', '80-89', '90-99', '100'];
$score_data = array_fill_keys($score_ranges, 0);

while ($score = $score_distribution_result->fetch_assoc()) {
    if (isset($score['score_range'])) {
        $score_data[$score['score_range']] = $score['count'];
    }
}

$score_labels_json = json_encode(array_keys($score_data));
$score_values_json = json_encode(array_values($score_data));

// Get question performance data
$question_performance_query = "
    SELECT 
        qr.question_id,
        q.question_text,
        q.points,
        COUNT(DISTINCT qa.attempt_id) as total_attempts,
        SUM(CASE WHEN qr.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers,
        (SUM(CASE WHEN qr.is_correct = 1 THEN 1 ELSE 0 END) / COUNT(DISTINCT qa.attempt_id)) * 100 as success_rate
    FROM quiz_responses qr
    JOIN quiz_attempts qa ON qr.attempt_id = qa.attempt_id
    JOIN questions q ON qr.question_id = q.quesid
    WHERE qa.schedule_id = ?
    GROUP BY qr.question_id, q.question_text, q.points
    ORDER BY success_rate ASC";
    
$stmt = $con->prepare($question_performance_query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$question_performance_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduled Quiz Details</title>
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
    <link rel="stylesheet" href="nav.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .quiz-title {
            font-size: 1.8em;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .quiz-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 15px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .meta-label {
            font-weight: bold;
            color: #7f8c8d;
        }

        .quiz-description {
            margin-top: 15px;
            color: #34495e;
            line-height: 1.8;
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
        
        .active-badge {
            background: #2ecc71;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .inactive-badge {
            background: #e74c3c;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            padding: 5px 10px;
            border-radius: 4px;
            background: #f1f8fe;
            color: #2980b9;
        }
        
        .success-rate {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .rate-low {
            background: #e74c3c;
            color: white;
        }
        
        .rate-medium {
            background: #f39c12;
            color: white;
        }
        
        .rate-high {
            background: #2ecc71;
            color: white;
        }
        
        .truncate {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <?php include "components/header.php"; ?>

    <div class="container">
        <div class="quiz-header">
            <div class="quiz-title">
                <?php echo htmlspecialchars($quiz_info['quizname']); ?>
                <?php if ($quiz_info['active']): ?>
                    <span class="active-badge">Active</span>
                <?php else: ?>
                    <span class="inactive-badge">Inactive</span>
                <?php endif; ?>
            </div>
            
            <div class="quiz-meta">
                <div class="meta-item">
                    <span class="meta-label">Access Code:</span>
                    <span class="access-code"><?php echo htmlspecialchars($quiz_info['access_code']); ?></span>
                </div>
                
                <div class="meta-item">
                    <span class="meta-label">Start Time:</span>
                    <?php echo date('M d, Y H:i', strtotime($quiz_info['start_time'])); ?>
                </div>
                
                <div class="meta-item">
                    <span class="meta-label">End Time:</span>
                    <?php echo date('M d, Y H:i', strtotime($quiz_info['end_time'])); ?>
                </div>
                
                <div class="meta-item">
                    <span class="meta-label">Created By:</span>
                    <?php echo htmlspecialchars($quiz_info['creator_name'] ?? 'Unknown'); ?>
                </div>
            </div>
            
            <div class="quiz-description">
                <?php echo nl2br(htmlspecialchars($quiz_info['quizdescription'] ?? 'No description provided.')); ?>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_attempts; ?></div>
                <div class="stat-label">Total Attempts</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($avg_score, 1); ?>%</div>
                <div class="stat-label">Average Score</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $highest_score; ?>%</div>
                <div class="stat-label">Highest Score</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $lowest_score == 100 ? 0 : $lowest_score; ?>%</div>
                <div class="stat-label">Lowest Score</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $completed_attempts; ?></div>
                <div class="stat-label">Completed</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $in_progress_attempts + $abandoned_attempts; ?></div>
                <div class="stat-label">Incomplete</div>
            </div>
        </div>
        
        <div class="tabs">
            <button class="tab active" onclick="openTab(event, 'attempts')">User Attempts</button>
            <button class="tab" onclick="openTab(event, 'charts')">Statistics</button>
            <button class="tab" onclick="openTab(event, 'questions')">Question Analysis</button>
        </div>
        
        <!-- User Attempts Tab -->
        <div id="attempts" class="tab-content active">
            <div class="search-bar">
                <input type="text" id="attemptSearch" class="search-input" placeholder="Search by name or email...">
                <button class="search-button" onclick="searchAttempts()">Search</button>
            </div>
            
            <h2>All User Attempts</h2>
            
            <?php if ($total_attempts > 0): ?>
                <table id="attempts-table">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Quiz Name</th>
                            <th>Score</th>
                            <th>Correct / Total</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($attempt = $attempts_result->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($attempt['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($attempt['email']); ?></td>
                                <td><?php echo htmlspecialchars($attempt['quizname']); ?></td>
                                <td><span class="score"><?php echo $attempt['score']; ?>%</span></td>
                                <td><?php echo $attempt['correct_answers']; ?> / <?php echo $attempt['total_questions']; ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($attempt['start_time'])); ?></td>
                                <td><?php echo $attempt['end_time'] ? date('M d, Y H:i', strtotime($attempt['end_time'])) : '-'; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($attempt['status']); ?>">
                                        <?php echo ucfirst($attempt['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <p>No quiz attempts recorded yet for this scheduled quiz.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Charts Tab -->
        <div id="charts" class="tab-content">
            <h2>Quiz Statistics</h2>
            
            <?php if ($total_attempts > 0): ?>
                <div class="charts-grid">
                    <div class="chart-container">
                        <h3>Score Distribution</h3>
                        <canvas id="scoreChart"></canvas>
                    </div>
                    
                    <div class="chart-container">
                        <h3>Attempts by Hour</h3>
                        <canvas id="timeChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-container">
                    <h3>Attempt Status Distribution</h3>
                    <canvas id="statusChart"></canvas>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <p>No data available to display charts. Quiz attempts are needed to generate statistics.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Question Analysis Tab -->
        <div id="questions" class="tab-content">
            <h2>Question Performance Analysis</h2>
            
            <?php if ($question_performance_result && $question_performance_result->num_rows > 0): ?>
                <table id="question-table">
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Points</th>
                            <th>Correct Answers</th>
                            <th>Total Attempts</th>
                            <th>Success Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($question = $question_performance_result->fetch_assoc()): ?>
                            <tr>
                                <td class="truncate" title="<?php echo htmlspecialchars($question['question_text']); ?>">
                                    <?php echo htmlspecialchars($question['question_text']); ?>
                                </td>
                                <td><?php echo $question['points']; ?></td>
                                <td><?php echo $question['correct_answers']; ?></td>
                                <td><?php echo $question['total_attempts']; ?></td>
                                <td>
                                    <?php 
                                        $rate = $question['success_rate'];
                                        $rateClass = $rate < 40 ? 'rate-low' : ($rate < 70 ? 'rate-medium' : 'rate-high');
                                    ?>
                                    <span class="success-rate <?php echo $rateClass; ?>">
                                        <?php echo number_format($rate, 1); ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <p>No question performance data available yet.</p>
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
        
        function searchAttempts() {
            const input = document.getElementById('attemptSearch');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('attempts-table');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) { // Skip header row
                const nameCell = rows[i].cells[0];
                const emailCell = rows[i].cells[1];
                
                if (nameCell && emailCell) {
                    const name = nameCell.textContent || nameCell.innerText;
                    const email = emailCell.textContent || emailCell.innerText;
                    
                    if (name.toUpperCase().indexOf(filter) > -1 || 
                        email.toUpperCase().indexOf(filter) > -1) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
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
            
            // Add event listener for search input
            document.getElementById('attemptSearch').addEventListener('keyup', searchAttempts);
            
            <?php if ($total_attempts > 0): ?>
            // Score Distribution Chart
            new Chart(document.getElementById('scoreChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo $score_labels_json; ?>,
                    datasets: [{
                        label: 'Number of Attempts',
                        data: <?php echo $score_values_json; ?>,
                        backgroundColor: '#3498db',
                        borderColor: '#2980b9',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
            
            // Time Distribution Chart
            new Chart(document.getElementById('timeChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo $time_labels_json; ?>,
                    datasets: [{
                        label: 'Attempts by Hour',
                        data: <?php echo $time_values_json; ?>,
                        backgroundColor: '#2ecc71',
                        borderColor: '#27ae60',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
            
            // Status Chart
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'In Progress', 'Abandoned'],
                    datasets: [{
                        data: [<?php echo $completed_attempts; ?>, <?php echo $in_progress_attempts; ?>, <?php echo $abandoned_attempts; ?>],
                        backgroundColor: ['#2ecc71', '#f39c12', '#e74c3c']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            <?php endif; ?>
        });
    </script>
</body>
</html> 