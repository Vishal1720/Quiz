<?php
require "../dbconnect.php";

if($_SESSION['status'] !== "admin") {
    header("Location: ../login.php");
    exit();
}

$query = "
    SELECT 
        u.name as user_name,
        u.email,
        qd.quizname,
        COUNT(DISTINCT qh.question_id) as questions_attempted,
        qh.difficulty_level,
        DATE(qh.attempt_date) as attempt_date
    FROM user_quiz_history qh
    JOIN users u ON qh.user_email = u.email
    JOIN quizdetails qd ON qh.quizid = qd.quizid
    GROUP BY u.email, qd.quizid, qh.difficulty_level, DATE(qh.attempt_date)
    ORDER BY qh.attempt_date DESC
";

$attempts = $con->query($query);
?>

<?php include "../components/header.php"; ?>
<style>
    .attempts-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 2rem;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 15px;
        backdrop-filter: blur(10px);
    }

    .attempts-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 2rem;
    }

    .attempts-table th,
    .attempts-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .attempts-table th {
        background: rgba(255, 255, 255, 0.1);
        color: var(--primary-color);
    }

    .attempts-table tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .difficulty {
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.9rem;
    }

    .difficulty.easy { background: rgba(46, 204, 113, 0.2); color: #2ecc71; }
    .difficulty.medium { background: rgba(241, 196, 15, 0.2); color: #f1c40f; }
    .difficulty.intermediate { background: rgba(230, 126, 34, 0.2); color: #e67e22; }
    .difficulty.hard { background: rgba(231, 76, 60, 0.2); color: #e74c3c; }
</style>

<div class="attempts-container">
    <h2 class="section-title">Quiz Attempts Overview</h2>
    <table class="attempts-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Quiz</th>
                <th>Difficulty</th>
                <th>Questions Attempted</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while($attempt = $attempts->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($attempt['user_name']); ?></td>
                <td><?php echo htmlspecialchars($attempt['quizname']); ?></td>
                <td>
                    <span class="difficulty <?php echo $attempt['difficulty_level']; ?>">
                        <?php echo ucfirst($attempt['difficulty_level']); ?>
                    </span>
                </td>
                <td><?php echo $attempt['questions_attempted']; ?></td>
                <td><?php echo $attempt['attempt_date']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../components/footer.php"; ?>
