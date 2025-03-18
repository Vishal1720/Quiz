<?php
require 'dbconnect.php';

if($_SESSION['status'] !== "admin") {
    header("Location: login.php");
    exit();
}

// Initialize messages
$success = isset($_SESSION['schedule_success']) ? $_SESSION['schedule_success'] : '';
$error = isset($_SESSION['schedule_error']) ? $_SESSION['schedule_error'] : '';

// Clear session messages
unset($_SESSION['schedule_success']);
unset($_SESSION['schedule_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quizid = mysqli_real_escape_string($con, $_POST['quizid']); 
    
    // Convert input times to server timezone
    $start_datetime = new DateTime($_POST['start_time']);
    $end_datetime = new DateTime($_POST['end_time']);
    
    $start_time = $start_datetime->format('Y-m-d H:i:s');
    $end_time = $end_datetime->format('Y-m-d H:i:s');
    
    if ($start_datetime && $end_datetime) {
        try {
            // Validate times
            $now = new DateTime();
            if ($start_datetime < $now) {
                throw new Exception("Start time cannot be in the past");
            }
            if ($end_datetime <= $start_datetime) {
                throw new Exception("End time must be after start time");
            }
            
            // Validate quiz exists
            $quizCheck = $con->prepare("SELECT quizid FROM quizdetails WHERE quizid = ?");
            $quizCheck->bind_param("i", $quizid);
            $quizCheck->execute();
            if ($quizCheck->get_result()->num_rows === 0) {
                throw new Exception("Invalid quiz selected");
            }

            // Check if quiz has questions
            $questionCheck = $con->prepare("SELECT COUNT(*) as count FROM quizes WHERE quizid = ?");
            $questionCheck->bind_param("i", $quizid);
            $questionCheck->execute();
            $count = $questionCheck->get_result()->fetch_assoc()['count'];
            if ($count === 0) {
                throw new Exception("Quiz has no questions");
            }

            // Check if table exists, if not create it
            $tableCheck = $con->query("SHOW TABLES LIKE 'scheduled_quizzes'");
            if ($tableCheck->num_rows == 0) {
                // Include dbconnect again to create table
                require 'dbconnect.php';
            }
            
            $access_code = bin2hex(random_bytes(16)); // Generate unique access code
            
            $stmt = $con->prepare("INSERT INTO scheduled_quizzes (quizid, start_time, end_time, access_code) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                // Debugging: Log statement preparation error
                error_log("Failed to prepare statement: " . $con->error);
                throw new Exception("Failed to prepare statement: " . $con->error);
            }
            
            $stmt->bind_param("isss", $quizid, $start_time, $end_time, $access_code);
            
            if($stmt->execute()) {
                $quiz_url = "http://" . $_SERVER['HTTP_HOST'] . "/Quiz/scheduled_quiz.php?code=" . $access_code;
                // Debugging: Log the generated quiz URL
                error_log("Quiz URL: $quiz_url");
                $_SESSION['schedule_success'] = "<div class='share-info'>
                    <p><i class='fas fa-check-circle'></i> Quiz scheduled successfully!</p>
                    <div class='share-box'>
                        <p><strong>Share this link with students:</strong></p>
                        <p><strong>Start time:</strong> " . $start_datetime->format('F j, Y, g:i a') . "</p>
                        <p><strong>End time:</strong> " . $end_datetime->format('F j, Y, g:i a') . "</p>
                        <div class='copy-section'>
                            <input type='text' id='quiz-link' value='{$quiz_url}' readonly>
                            <button type='button' onclick='copyLink()' class='copy-btn'>
                                <i class='fas fa-copy'></i> Copy Link
                            </button>
                        </div>
                        <div id='copy-alert' class='copy-alert'></div>
                    </div>
                </div>";
            } else {
                // Debugging: Log execution error
                error_log("Failed to execute statement: " . $stmt->error);
                throw new Exception("Failed to execute statement: " . $stmt->error);
            }
            
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
            
        } catch (Exception $e) {
            $_SESSION['schedule_error'] = "Error: " . $e->getMessage();
            error_log("Schedule Quiz Error: " . $e->getMessage());
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        $_SESSION['schedule_error'] = "Invalid date format provided";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Get available quizzes
$query = "SELECT * FROM quizdetails ORDER BY quizname ASC";
$quizzes = $con->query($query);
?>

<?php include "components/header.php"; ?>
<style>
    .schedule-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        backdrop-filter: blur(10px);
    }
    .schedule-form {
        display: grid;
        gap: 1.5rem;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .datetime-input {
        padding: 0.5rem;
        border-radius: 5px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }
    .submit-btn {
        padding: 1rem;
        border: none;
        border-radius: 5px;
        background: var(--primary);
        color: white;
        cursor: pointer;
        font-size: 1rem;
    }
    .success-message {
        background: rgba(46, 204, 113, 0.1);
        padding: 1.5rem;
        border-radius: 10px;
        border: 1px solid rgba(46, 204, 113, 0.2);
        margin-bottom: 2rem;
        animation: fadeIn 0.5s ease;
    }
    .error-message {
        background: rgba(231, 76, 60, 0.1);
        border: 1px solid rgba(231, 76, 60, 0.2);
        color: #e74c3c;
        padding: 1.5rem;
        border-radius: 10px;
        margin-bottom: 2rem;
        animation: fadeIn 0.5s ease;
    }
    .share-info {
        background: rgba(46, 204, 113, 0.1);
        padding: 1.5rem;
        border-radius: 10px;
        border: 1px solid rgba(46, 204, 113, 0.2);
        margin-bottom: 2rem;
        animation: fadeIn 0.5s ease;
    }
    .share-box {
        margin-top: 1rem;
        background: rgba(255, 255, 255, 0.05);
        padding: 1rem;
        border-radius: 8px;
    }
    .copy-section {
        display: flex;
        gap: 1rem;
        margin-top: 0.5rem;
    }
    #quiz-link {
        flex: 1;
        padding: 0.5rem;
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        color: white;
        font-family: monospace;
    }
    .copy-btn {
        padding: 0.5rem 1rem;
        background: var(--primary);
        border: none;
        border-radius: 4px;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }
    .copy-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }
    .copy-alert {
        margin-top: 0.5rem;
        font-size: 0.9rem;
        color: var(--success-color);
        height: 20px;
    }
    .alert-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        animation: slideIn 0.5s ease;
    }
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .fa-check-circle {
        color: #2ecc71;
        margin-right: 0.5rem;
    }
</style>

<div class="schedule-container">
    <h1>Schedule Quiz</h1>
    
    <div id="alert-container" class="alert-container"></div>
    
    <?php if(!empty($success)): ?>
        <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if(!empty($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" class="schedule-form">
        <div class="form-group">
            <label for="quizid">Select Quiz</label>
            <select name="quizid" id="quizid" required>
                <?php while($quiz = $quizzes->fetch_assoc()): ?>
                    <option value="<?php echo $quiz['quizid']; ?>">
                        <?php echo htmlspecialchars($quiz['quizname']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="start_time">Start Time</label>
            <input type="datetime-local" name="start_time" id="start_time" class="datetime-input" required>
        </div>
        
        <div class="form-group">
            <label for="end_time">End Time</label>
            <input type="datetime-local" name="end_time" id="end_time" class="datetime-input" required>
        </div>
        
        <button type="submit" class="submit-btn">Schedule Quiz</button>
    </form>
</div>

<?php include "components/footer.php"; ?>

<script>
function copyLink() {
    const linkInput = document.getElementById('quiz-link');
    const copyAlert = document.getElementById('copy-alert');
    
    linkInput.select();
    linkInput.setSelectionRange(0, 99999); // For mobile devices
    
    navigator.clipboard.writeText(linkInput.value).then(() => {
        copyAlert.textContent = 'Link copied to clipboard!';
        copyAlert.style.color = 'var(--success-color)';
        setTimeout(() => {
            copyAlert.textContent = '';
        }, 3000);
    }).catch(err => {
        copyAlert.textContent = 'Failed to copy link';
        copyAlert.style.color = 'var(--error-color)';
    });
}

// Form submission feedback
document.querySelector('.schedule-form').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('.submit-btn');
    submitBtn.textContent = 'Scheduling...';
    submitBtn.disabled = true;
});
</script>
</body>
</html>
