<?php 
require 'dbconnect.php';

if($_SESSION['status'] !== "admin") {
    header("Location: login.php");
    exit();
}

// Get available quizzes
$query = "SELECT * FROM quizdetails ORDER BY quizname ASC";
$res = $con->query($query);

if($res->num_rows == 0) {
    header("Location: createquiz.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['quizid']) && isset($_POST['question']) && isset($_POST['options']) && isset($_POST['answer'])) {
        $quizid = mysqli_real_escape_string($con, $_POST['quizid']);
        $question = mysqli_real_escape_string($con, $_POST['question']);
        $options = array_map('mysqli_real_escape_string', array_fill(0, 4, $con), $_POST['options']);
        $answer = mysqli_real_escape_string($con, $_POST['answer']);

        $query = "INSERT INTO quizes (question, quizid, option1, option2, option3, option4, answer) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($query);
        $stmt->bind_param("sssssss", $question, $quizid, $options[0], $options[1], $options[2], $options[3], $options[$answer]);
        
        if($stmt->execute()) {
            $success = "Question added successfully!";
        } else {
            $error = "Error adding question: " . $con->error;
        }
    }
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
            max-width: 1000px;
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

        .quiz-form {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 3rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.5s ease;
        }

        .select-quiz {
            margin-bottom: 2.5rem;
            background: rgba(74, 144, 226, 0.05);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid rgba(74, 144, 226, 0.2);
        }

        .form-group {
            margin-bottom: 2rem;
        }

        label {
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

        .form-control:focus {
            border-color: var(--primary-color);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.1);
            outline: none;
        }

        select.form-control {
            color: var(--text-light);
            background-color: rgba(255, 255, 255, 0.05);
            cursor: pointer;
        }

        select.form-control option {
            background-color: #2a2a3e;
            color: #ffffff;
            padding: 10px;
        }

        .options-container {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem 0;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .options-title {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .option-group {
            background: rgba(255, 255, 255, 0.03);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .option-group:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateY(-2px);
        }

        .answer-section {
            background: rgba(46, 204, 113, 0.05);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid rgba(46, 204, 113, 0.2);
            margin-bottom: 2rem;
        }

        .answer-title {
            color: var(--success-color);
            margin-bottom: 1rem;
            font-size: 1.2rem;
            font-weight: 500;
        }

        .submit-btn {
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

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(74, 144, 226, 0.3);
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
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

        .helper-text {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 0.5rem;
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

            .quiz-form {
                padding: 2rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .options-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="page-title">Add Questions</h1>
        <p class="page-subtitle">Create engaging questions for your quiz</p>

        <?php if(isset($success)): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="quiz-form">
            <div class="select-quiz">
                <label for="quizid">Select Quiz</label>
                <select name="quizid" id="quizid" class="form-control" required>
                    <?php while($row = $res->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($row['quizid']); ?>">
                            <?php echo htmlspecialchars($row['quizname']); ?>
                            (<?php echo htmlspecialchars($row['category']); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
                <p class="helper-text">Choose the quiz you want to add questions to</p>
            </div>

            <div class="form-group">
                <label for="question">Question</label>
                <textarea name="question" id="question" class="form-control" 
                         rows="3" placeholder="Enter your question" required></textarea>
                <p class="helper-text">Make your question clear and concise</p>
            </div>

            <div class="options-container">
                <h3 class="options-title">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                    Options
                </h3>
                <div class="options-grid">
                    <?php for($i = 1; $i <= 4; $i++): ?>
                        <div class="option-group">
                            <label for="option<?php echo $i; ?>">Option <?php echo $i; ?></label>
                            <input type="text" name="options[]" 
                                   id="option<?php echo $i; ?>" 
                                   class="form-control"
                                   placeholder="Enter option <?php echo $i; ?>"
                                   required>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="answer-section">
                <h3 class="answer-title">Correct Answer</h3>
                <select name="answer" class="form-control" required>
                    <option value="">Select correct answer</option>
                    <option value="0">Option 1</option>
                    <option value="1">Option 2</option>
                    <option value="2">Option 3</option>
                    <option value="3">Option 4</option>
                </select>
                <p class="helper-text">Choose which option is the correct answer</p>
            </div>

            <button type="submit" class="submit-btn">Add Question</button>
        </form>
    </div>

    <script>
        // Function to update answer dropdown options
        function updateAnswerOptions() {
            const answerSelect = document.querySelector('select[name="answer"]');
            const options = document.querySelectorAll('input[name="options[]"]');
            
            // Clear existing options except the first placeholder
            while (answerSelect.options.length > 1) {
                answerSelect.remove(1);
            }
            
            // Add new options with only the answer text
            options.forEach((option, index) => {
                if (option.value.trim() !== '') {
                    const optionElement = new Option(
                        option.value,
                        index.toString(),
                        false,
                        false
                    );
                    answerSelect.add(optionElement);
                }
            });
        }

        // Add input event listeners to all option inputs
        document.querySelectorAll('input[name="options[]"]').forEach(input => {
            input.addEventListener('input', updateAnswerOptions);
        });
    </script>
</body>
</html>