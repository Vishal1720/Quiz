<?php
require 'dbconnect.php';

if($_SESSION['status'] !== "admin") {
    http_response_code(403);
    exit('Unauthorized');
}

if(!isset($_GET['quizid'])) {
    http_response_code(400);
    exit('Quiz ID required');
}

$quizid = mysqli_real_escape_string($con, $_GET['quizid']);
$query = "SELECT * FROM quizes WHERE quizid = ? ORDER BY id ASC";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $quizid);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
while($row = $result->fetch_assoc()) {
    $questions[] = [
        'id' => $row['ID'],
        'question' => htmlspecialchars($row['question']),
        'option1' => htmlspecialchars($row['option1']),
        'option2' => htmlspecialchars($row['option2']),
        'option3' => htmlspecialchars($row['option3']),
        'option4' => htmlspecialchars($row['option4']),
        'answer' => htmlspecialchars($row['answer'])
    ];
}

header('Content-Type: application/json');
echo json_encode($questions);
