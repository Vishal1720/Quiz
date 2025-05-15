<?php
include "dbconnect.php";

// Check if user is logged in and is admin
if(!isset($_SESSION['status']) || $_SESSION['status'] !== "admin") {
    header("Location: landing.php");
    exit();
}
?>

<form method="POST" action="process_question.php" class="question-form">
    <div class="form-group">
        <label for="difficulty">Difficulty Level</label>
        <select name="difficulty" id="difficulty" required class="form-control">
            <option value="beginner">Beginner</option>
            <option value="intermediate">Intermediate</option>
            <option value="advanced">Advanced</option>
            <option value="expert">Expert</option>
        </select>
    </div>
</form>