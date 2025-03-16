<?php
include "dbconnect.php";

if($_SESSION['status'] !== "admin") {
    header("Location: login.php");
    exit();
}

$success = $error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete']) && !empty($_POST['quizid']) && !empty($_POST['qid'])) {
        $quizid = mysqli_real_escape_string($con, $_POST['quizid']);
        $id = mysqli_real_escape_string($con, $_POST['qid']);
        
        $stmt = $con->prepare("DELETE FROM quizes WHERE quizid=? AND id=?");
        $stmt->bind_param("ss", $quizid, $id);
        
        if ($stmt->execute()) {
            $success = "Question Deleted Successfully";
        } else {
            $error = "Error deleting question: " . $con->error;
        }
    } elseif (!empty($_POST['quizid']) && !empty($_POST['qid']) && 
        !empty($_POST['question']) && !empty($_POST['option1']) && 
        !empty($_POST['option2']) && !empty($_POST['option3']) && 
        !empty($_POST['option4']) && !empty($_POST['answer']))
    {
        $quizid = mysqli_real_escape_string($con, $_POST['quizid']);
        $id = mysqli_real_escape_string($con, $_POST['qid']);
        $question = mysqli_real_escape_string($con, $_POST['question']);
        $option1 = mysqli_real_escape_string($con, $_POST['option1']);
        $option2 = mysqli_real_escape_string($con, $_POST['option2']);
        $option3 = mysqli_real_escape_string($con, $_POST['option3']);
        $option4 = mysqli_real_escape_string($con, $_POST['option4']);
        $answer = mysqli_real_escape_string($con, $_POST['answer']);

        if (isset($_POST['update'])) {
            $stmt = $con->prepare("UPDATE quizes SET question=?, option1=?, option2=?, option3=?, option4=?, answer=? WHERE quizid=? AND id=?");
            $stmt->bind_param("ssssssss", $question, $option1, $option2, $option3, $option4, $answer, $quizid, $id);
            
            if ($stmt->execute()) {
                $success = "Question Updated Successfully";
            } else {
                $error = "Error updating question: " . $con->error;
            }
        }

        if (isset($_POST['delete'])) {
            $stmt = $con->prepare("DELETE FROM quizes WHERE quizid=? AND id=?");
            $stmt->bind_param("ss", $quizid, $id);
            
            if ($stmt->execute()) {
                $success = "Question Deleted Successfully";
            } else {
                $error = "Error deleting question: " . $con->error;
            }
        }
    } else {
        $error = "Please fill in all fields including the correct answer";
    }
}

// Get quizzes
$query = "SELECT quizid, quizname, category FROM quizdetails ORDER BY quizname ASC";
$quizzes = $con->query($query);
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Edit Quiz - Admin Dashboard</title>
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
    <link rel='stylesheet' href='nav.css'>
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #357abd;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
            --background-dark: #1a1a2e;
        }

        .edit-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .page-title {
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #fff, var(--primary-color));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .page-subtitle {
            color: #a0a0a0;
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

        .select-quiz {
            margin-bottom: 2rem;
            background: rgba(74, 144, 226, 0.05);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid rgba(74, 144, 226, 0.2);
        }

        .select-quiz label {
            display: block;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        .select-quiz select {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
            padding-right: 2.5rem;
        }

        .select-quiz select option {
            background: #1a1a2e;
            color: #fff;
            padding: 1rem;
        }

        .questions-container {
            display: grid;
            gap: 2rem;
        }

        .question-card {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 15px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            animation: slideUp 0.5s ease;
        }

        .question-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.8rem;
            color: #fff;
            font-size: 1.1rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
            outline: none;
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .answer-section {
            background: rgba(46, 204, 113, 0.05);
            border: 1px solid rgba(46, 204, 113, 0.2);
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
        }

        .answer-section label {
            color: var(--success-color);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }

        .btn-update {
            background: var(--primary-color);
            color: white;
        }

        .btn-delete {
            background: var(--error-color);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .no-questions {
            text-align: center;
            padding: 3rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            color: #a0a0a0;
            font-size: 1.2rem;
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
            .edit-container {
                padding: 0 1rem;
            }

            .options-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <nav>
        <a href='./index.php'>Home</a>
        <a href='./quizmanip.php' class="active">Edit</a>
        <a href='./createquiz.php'>Create</a>
        <a href='./quizform.php'>Insert</a>
        <a href='./logout.php'>Logout</a>
        <div class='animation'></div>
    </nav>

    <div class="edit-container">
        <h1 class="page-title">Edit Quiz Questions</h1>
        <p class="page-subtitle">Select a quiz and modify its questions</p>

        <?php if($success): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="select-quiz">
            <label for="quiz-select">Select Quiz to Edit</label>
            <select id="quiz-select" class="form-control" onchange="loadQuestions(this.value)">
                <option value="">Choose a quiz...</option>
                <?php while($quiz = $quizzes->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($quiz['quizid']); ?>">
                        <?php echo htmlspecialchars($quiz['quizname'] . ' (' . $quiz['category'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div id="questions-container" class="questions-container">
            <!-- Questions will be loaded here -->
        </div>
    </div>

    <script>
        function loadQuestions(quizId) {
            if (!quizId) {
                document.getElementById('questions-container').innerHTML = '';
                return;
            }
            
            fetch(`get_questions.php?quizid=${quizId}`)
                .then(response => response.json())
                .then(questions => {
                    const container = document.getElementById('questions-container');
                    
                    if (questions.length === 0) {
                        container.innerHTML = '<div class="no-questions">No questions found for this quiz. Add some questions first!</div>';
                        return;
                    }
                    
                    container.innerHTML = questions.map((q, index) => `
                        <div class="question-card">
                            <form method="POST" class="question-form">
                                <input type="hidden" name="quizid" value="${quizId}">
                                <input type="hidden" name="qid" value="${q.id}">
                                
                                <div class="form-group">
                                    <label for="question-${q.id}">Question ${index + 1}</label>
                                    <textarea name="question" id="question-${q.id}" class="form-control" required>${q.question}</textarea>
                                </div>
                                
                                <div class="options-grid">
                                    <div class="form-group">
                                        <label for="option1-${q.id}">Option 1</label>
                                        <input type="text" name="option1" id="option1-${q.id}" class="form-control" value="${q.option1}" required onkeyup="updateAnswerOptions(${q.id})">
                                    </div>
                                    <div class="form-group">
                                        <label for="option2-${q.id}">Option 2</label>
                                        <input type="text" name="option2" id="option2-${q.id}" class="form-control" value="${q.option2}" required onkeyup="updateAnswerOptions(${q.id})">
                                    </div>
                                    <div class="form-group">
                                        <label for="option3-${q.id}">Option 3</label>
                                        <input type="text" name="option3" id="option3-${q.id}" class="form-control" value="${q.option3}" required onkeyup="updateAnswerOptions(${q.id})">
                                    </div>
                                    <div class="form-group">
                                        <label for="option4-${q.id}">Option 4</label>
                                        <input type="text" name="option4" id="option4-${q.id}" class="form-control" value="${q.option4}" required onkeyup="updateAnswerOptions(${q.id})">
                                    </div>
                                </div>

                                <div class="answer-section">
                                    <label for="answer-${q.id}">Correct Answer</label>
                                    <select name="answer" id="answer-${q.id}" class="form-control" required>
                                        <option value="${q.option1}" ${q.answer === q.option1 ? 'selected' : ''}>${q.option1}</option>
                                        <option value="${q.option2}" ${q.answer === q.option2 ? 'selected' : ''}>${q.option2}</option>
                                        <option value="${q.option3}" ${q.answer === q.option3 ? 'selected' : ''}>${q.option3}</option>
                                        <option value="${q.option4}" ${q.answer === q.option4 ? 'selected' : ''}>${q.option4}</option>
                                    </select>
                                </div>

                                <div class="action-buttons">
                                    <button type="submit" name="update" class="btn btn-update">Update Question</button>
                                    <button type="submit" name="delete" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this question?')">Delete Question</button>
                                </div>
                            </form>
                        </div>
                    `).join('');
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('questions-container').innerHTML = `
                        <div class="message error">
                            Error loading questions. Please try again.
                        </div>
                    `;
                });
        }

        // Add smooth scrolling to form after submission
        if(window.location.href.includes('quizmanip.php')) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>

    <script>
        function updateAnswerOptions(questionId) {
            const option1 = document.getElementById(`option1-${questionId}`).value;
            const option2 = document.getElementById(`option2-${questionId}`).value;
            const option3 = document.getElementById(`option3-${questionId}`).value;
            const option4 = document.getElementById(`option4-${questionId}`).value;
            const answerSelect = document.getElementById(`answer-${questionId}`);
            const currentAnswer = answerSelect.value;

            // Update all options in the answer dropdown
            answerSelect.innerHTML = `
                <option value="${option1}" ${currentAnswer === option1 ? 'selected' : ''}>${option1}</option>
                <option value="${option2}" ${currentAnswer === option2 ? 'selected' : ''}>${option2}</option>
                <option value="${option3}" ${currentAnswer === option3 ? 'selected' : ''}>${option3}</option>
                <option value="${option4}" ${currentAnswer === option4 ? 'selected' : ''}>${option4}</option>
            `;
        }
    </script>