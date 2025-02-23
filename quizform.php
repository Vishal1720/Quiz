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
    <title>Quiz</title>
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
    <link rel='stylesheet' href='nav.css'>
    <link rel='stylesheet' href='form.css'>
    <style>
        select{
            width: 100%;
            padding: 5px;
            margin-bottom: 5px;
            font-size: 15px;
            text-align: center;
        }
        nav{
              width:43%;
              height:50px;
              
         }
         nav a{
            text-align: center;
         }
         a:nth-child(1) {
	width: 110px;
}
a:nth-child(2) {
	width: 130px;
    
}
a:nth-child(3) {
	width: 100px;
}
nav .home,a:nth-child(1):hover~.animation {
	width: 110px;
	left: 0;
	background-color: #1abc9c;
}
nav .quizes,a:nth-child(2):hover~.animation {
	width: 140px;
	left: 110px;
	background-color:rgb(26, 61, 188);
}
nav .create,a:nth-child(3):hover~.animation {
	width: 110px;
	left:250px;
	background-color:rgb(42, 148, 177);
}
nav .update,a:nth-child(4):hover~.animation {
	width: 120px;
	left:370px;
	background-color:rgb(70, 83, 196);
}
nav .logout,a:nth-child(5):hover~.animation {
	width: 150px;
	left:490px;
	background-color:rgb(185, 91, 51);
}
    </style>
</head>
<body>
<nav >

<a href='./index.php'>Home</a>
	<a href='./quizmanip.php'>Edit</a>
    <a href='./createquiz.php'>Create </a>
    <a href='./quizform.php'>Insert </a>
	<a style='width:100px' href='./logout.php'>Logout</a>
	<div class='animation update' ></div>
</nav>
    <form action='./quizform.php' method="POST" style="margin-top:10px;">
    <div class='quizform'>
        <label for='quizid'>Quiz Name</label>
        <select name="quizid" id="quizid" required>
            <?php 
            foreach($res as $row)
            {
                echo "<option value='".$row['quizid']."'>" . $row['quizname'] . "</option>";
            }?>
            
        </select>
        <label for='q'>Question</label>
        <input type='text' placeholder='Question' name='question' id='q' required></input>
        <div>
            <label for='option1'>Option 1</label>
        <input maxlength="40" type='text' maxlength="1000" placeholder='Enter option'
         onkeyup="change('option1','op1')"  maxlength="222" id='option1'name='option1' required>

        <label for='option2'>Option 2</label>
        <input type='text' placeholder='Enter option' 
        onkeyup="change('option2','op2')" id='option2' maxlength="222" name='option2' required>

    </div>
    <div>
        <label for='option3'>Option 3</label>
        <input type='text' placeholder='Enter option' maxlength="222" onkeyup="change('option3','op3')" 
        id='option3' name='option3' required>

        <label for='option4'>Option 4</label>
        <input type='text' placeholder='Enter option' 
        onkeyup="change('option4','op4')" id='option4' maxlength="222" name='option4' required>

    </div>
    <label for='answer'>Answer</label>
    <select name='answer' placeholder='Answer' >
        <option value='1' id="op1">Options</option>
        <option value='2' id="op2">Options</option>
        <option value='3' id="op3">Options</option>
        <option value='4' id="op4">Options</option>
    </select>
    <input type='submit' value='Submit'>
    </div>
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