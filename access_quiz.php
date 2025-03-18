<?php
require 'dbconnect.php';


$error = '';
if(isset($_POST['quiz_link'])) {
    $link = $_POST['quiz_link'];
    if(preg_match('/code=([a-f0-9]{32})/', $link, $matches)) {
        $code = $matches[1];
        header("Location: scheduled_quiz.php?code=" . $code);
        exit();
    } else {
        $error = "Invalid quiz link format";
    }
}
?>

<?php include "components/header.php"; ?>
<style>
    .access-container {
        max-width: 600px;
        margin: 4rem auto;
        padding: 2rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        backdrop-filter: blur(10px);
    }
    .access-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .input-box {
        padding: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.05);
        color: white;
        font-size: 1rem;
    }
    .submit-btn {
        padding: 1rem;
        background: var(--primary);
        border: none;
        border-radius: 8px;
        color: white;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(74, 144, 226, 0.3);
    }
    .error-message {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: #ef4444;
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
    }
</style>

<div class="access-container">
    <h1 style="margin-bottom: 2rem; text-align: center;">Access Quiz</h1>
    
    <?php if($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" class="access-form">
        <div class="form-group">
            <label for="quiz_link">Enter Quiz Link or Code</label>
            <input type="text" 
                   id="quiz_link" 
                   name="quiz_link" 
                   class="input-box"
                   placeholder="Paste quiz link or enter access code"
                   required>
        </div>
        <button type="submit" class="submit-btn">Access Quiz</button>
    </form>
</div>

<?php include "components/footer.php"; ?>
