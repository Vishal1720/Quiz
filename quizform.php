<?php 
require 'dbconnect.php';
if($_SESSION['status']=="loggedout" || $_SESSION['status']=="" || empty($_SESSION['status'])) 
{
    header("Location: login.php");
    exit();
}
$query="select * from quizdetails where email='{$_SESSION['email']}'";

$res=$con->query($query);

if($res->num_rows==0)
{
echo "<script>alert('First create a quiz')</script>";
header("Location: createquiz.php");
exit();
}
else{
    
}

if($_SERVER['REQUEST_METHOD']=='POST')
{
    if(isset($_POST['quizid'])   && isset($_POST['option1']) && 
    isset($_POST['option2']) && isset($_POST['option3']) && isset($_POST['option4']) && isset($_POST['answer']))
{
    $quizid=$_POST['quizid'];
    $question=$_POST['question'];
    $option1=$_POST['option1'];
    $option2=$_POST['option2'];
    $option3=$_POST['option3'];
    $option4=$_POST['option4'];
    $answer=$_POST['answer'];

    $query="INSERT INTO `quizes` ( `question`, `quizid`, `option1`, `option2`, `option3`, `option4`, `answer`) 
    VALUES ( '$question', '$quizid', '$option1', '$option2', '$option3', '$option4', '$answer');";
    $res=$con->query($query);
    if(!$res)
    {
        echo "<script>alert('Error in inserting')</script>";
    }
    else{
        echo "<script>alert('Inserted question successfully');window.location.href='http://localhost/quiz/quizform.php';</script>";
    }

}
}
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Insert Question</title>
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
    <link rel='stylesheet' href='nav.css'>
    <link rel='stylesheet' href='form.css'>
    <style>
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .page-title {
            color: #fff;
            text-align: center;
            font-size: 2rem;
            margin: 2rem 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .quiz-form {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 15px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
        }

        .select-quiz {
            margin-bottom: 2rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .options-container {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1rem;
        }

        .option-group {
            margin-bottom: 1rem;
        }

        .answer-select {
            margin-top: 1.5rem;
            width: 100%;
            padding: 12px;
            background: rgba(74, 144, 226, 0.1);
            border: 1px solid rgba(74, 144, 226, 0.3);
            border-radius: 8px;
            color: #fff;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav>
        <a href='./index.php'>Home</a>
        <a href='./quizmanip.php'>Edit</a>
        <a href='./createquiz.php'>Create </a>
        <a href='./quizform.php'>Insert </a>
        <a style='width:100px' href='./logout.php'>Logout</a>
        <div class='animation update' ></div>
    </nav>

    <div class="container">
        <h1 class="page-title">Insert New Question</h1>
        
        <form method="POST" action="quizform.php" class="quiz-form">
            <div class="select-quiz">
                <label for="quizid">Select Quiz</label>
                <select name="quizid" id="quizid" class="form-control" required>
                    <?php 
                    foreach($res as $row)
                    {
                        echo "<option value='".$row['quizid']."'>" . $row['quizname'] . "</option>";
                    }?>
                </select>
            </div>

            <div class="form-group">
                <label for="q">Question</label>
                <input type="text" placeholder="Enter your question" name="question" id="q" class="form-control" required>
            </div>

            <div class="options-container">
                <h3 style="color: #fff; margin-bottom: 1rem;">Options</h3>
                <div class="form-grid">
                    <div class="option-group">
                        <label for="option1">Option 1</label>
                        <input type="text" maxlength="40" placeholder="Enter option" 
                               onkeyup="change('option1','op1')" id="option1" 
                               name="option1" class="form-control" required>
                    </div>

                    <div class="option-group">
                        <label for="option2">Option 2</label>
                        <input type="text" placeholder="Enter option" 
                               onkeyup="change('option2','op2')" id="option2" 
                               name="option2" class="form-control" required>
                    </div>

                    <div class="option-group">
                        <label for="option3">Option 3</label>
                        <input type="text" placeholder="Enter option" 
                               onkeyup="change('option3','op3')" id="option3" 
                               name="option3" class="form-control" required>
                    </div>

                    <div class="option-group">
                        <label for="option4">Option 4</label>
                        <input type="text" placeholder="Enter option" 
                               onkeyup="change('option4','op4')" id="option4" 
                               name="option4" class="form-control" required>
                    </div>
                </div>

                <label for="answer">Correct Answer</label>
                <select name="answer" id="answer" class="answer-select">
                    <option value="1" id="op1">Option 1</option>
                    <option value="2" id="op2">Option 2</option>
                    <option value="3" id="op3">Option 3</option>
                    <option value="4" id="op4">Option 4</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 1.5rem;">
                Add Question
            </button>
        </form>
    </div>

    <script>
        function change(inpid,option){
            var box = document.getElementById(inpid).value;
            var val = document.getElementById(option);
            val.value=box;
            val.textContent=box;
        }
    </script>
</body>
</html>