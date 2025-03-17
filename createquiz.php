<?php
require 'dbconnect.php';

if($_SESSION['status'] !== "admin") {
    header("Location: login.php");
    exit();
}

// Ensure admin email is set
if (!isset($_SESSION['email'])) {
    $_SESSION['email'] = 'admin123@gmail.com'; // Set default admin email
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['category']) && isset($_POST['quizname'])) {
        $cat = mysqli_real_escape_string($con, $_POST['category']);
        $quizname = mysqli_real_escape_string($con, $_POST['quizname']);
        
        // Ensure timer is properly processed
        $timer = isset($_POST['timer']) ? intval($_POST['timer']) : 30;
        // Ensure timer is within valid range (0-180 minutes)
        $timer = min(max($timer, 0), 180);
        
        // Debug output
        error_log("Creating quiz with timer: " . $timer . " minutes");
        
        // Check for duplicate quizname
        $stmt = $con->prepare("SELECT quizname FROM quizdetails WHERE quizname = ?");
        $stmt->bind_param("s", $quizname);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $error = "Quiz name already exists. Please choose a different name.";
        } else {
            try {
                // Explicitly include timer in the query
                $stmt = $con->prepare("INSERT INTO quizdetails (category, quizname, email, timer) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sssi", $cat, $quizname, $_SESSION['email'], $timer);
                
                if($stmt->execute()) {
                    $_SESSION['qid'] = $con->insert_id;
                    
                    // Verify the timer was saved correctly
                    $verifyStmt = $con->prepare("SELECT timer FROM quizdetails WHERE quizid = ?");
                    $verifyStmt->bind_param("i", $_SESSION['qid']);
                    $verifyStmt->execute();
                    $verifyResult = $verifyStmt->get_result();
                    $verifyRow = $verifyResult->fetch_assoc();
                    
                    error_log("Verified timer value in database: " . $verifyRow['timer']);
                    
                    $success = "Quiz created successfully! You can now add questions.";
                    header("refresh:2;url=quizform.php");
                } else {
                    $error = "Error creating quiz: " . $con->error;
                }
            } catch (mysqli_sql_exception $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Get categories
$query = "SELECT categoryname FROM category ORDER BY categoryname ASC";
$result = $con->query($query);
$categories = [];
while($row = $result->fetch_assoc()) {
    $categories[] = $row['categoryname'];
}
?>
<?php include "components/header.php"; ?>
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #357abd;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
            --background-dark: #1a1a2e;
            --text-light: #ffffff;
            --text-muted: #a0a0a0;
        }

        body {
            background: linear-gradient(135deg, var(--background-dark) 0%, #16213e 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-light);
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .page-title {
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #fff, var(--primary-color));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .page-subtitle {
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 2.5rem;
            font-size: 1.1rem;
        }

        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }

        .success {
            background: rgba(46, 204, 113, 0.1);
            border: 1px solid rgba(46, 204, 113, 0.3);
            color: var(--success-color);
        }

        .error {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: var(--error-color);
        }

        .create-form {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 3rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.5s ease;
        }

        .form-group {
            margin-bottom: 2rem;
        }

        .form-group label {
            display: block;
            color: var(--text-light);
            margin-bottom: 0.8rem;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.2rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--text-light);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        select.form-control option {
            background-color: #2a2a3e;
            color: var(--text-light);
            padding: 10px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.1);
            outline: none;
        }

        .timer-section {
            background: rgba(74, 144, 226, 0.05);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(74, 144, 226, 0.2);
        }

        .timer-title {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .timer-input {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .timer-input input {
            width: 120px;
            padding: 0.8rem;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-light);
            font-size: 1rem;
        }

        .timer-input span {
            color: var(--text-muted);
            font-size: 1rem;
        }

        .helper-text {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .create-btn {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 12px;
            color: var(--text-light);
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .create-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(74, 144, 226, 0.3);
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }

            .create-form {
                padding: 2rem;
            }

            .timer-input {
                flex-direction: column;
                align-items: flex-start;
            }

            .timer-input input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="page-title">Create New Quiz</h1>
        <p class="page-subtitle">Set up your quiz details and timer</p>

        <?php if($success): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="create-form">
            <div class="form-group">
                <label for="quizname">Quiz Name</label>
                <input type="text" name="quizname" id="quizname" class="form-control" 
                       placeholder="Enter quiz name" required 
                       value="<?php echo isset($_POST['quizname']) ? htmlspecialchars($_POST['quizname']) : ''; ?>">
                <p class="helper-text">Choose a unique name for your quiz</p>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select name="category" id="category" class="form-control" required>
                    <option value="">Select a category</option>
                    <?php foreach($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>"
                                <?php echo (isset($_POST['category']) && $_POST['category'] === $category) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="helper-text">Choose a category for your quiz</p>
            </div>

            <div class="timer-section">
                <h3 class="timer-title">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    Quiz Timer
                </h3>
                <div class="timer-input">
                    <input type="number" name="timer" id="timer" min="0" max="180" 
                           value="<?php echo isset($_POST['timer']) ? intval($_POST['timer']) : 0; ?>"
                           placeholder="Enter time limit">
                    <span>minutes (0 for no limit, max 180)</span>
                </div>
                <p class="helper-text">Set a time limit for your quiz. Leave at 0 for no time limit.</p>
            </div>

            <button type="submit" class="create-btn">Create Quiz</button>
        </form>
    </div>

    <script>
        // Add smooth scrolling to form after submission
        if(window.location.href.includes('createquiz.php')) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Validate timer input
        document.getElementById('timer').addEventListener('input', function(e) {
            let value = parseInt(this.value);
            if (isNaN(value)) this.value = 0;
            else if (value < 0) this.value = 0;
            else if (value > 180) this.value = 180;
        });
    </script>
</body>
</html>