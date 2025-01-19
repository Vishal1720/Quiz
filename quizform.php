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
        echo "<script>alert('Inserted question successfully')</script>";
    }

}
}
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Document</title>
    <link rel='stylesheet' href='nav.css'>
    <link rel='stylesheet' href='form.css'>
    <style>
        select{
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body>
<nav style='width:fit-content;font-size:16px;overflow:none'>
<a href='./quizform.php'>Update Quiz</a>
<a href='#'>Home</a>
	<a href='#'>Quizzes</a>
    
  
    <a href='./createquiz.php'>Create Quiz</a>
	<a style='width:100px' href='./logout.php'>logout</a>
	<div class='animation start-home' ></div>
</nav>
    <form action='./quizform.php' method="POST" style="margin-top:10px;">
    <div class='quizform'>
        <label for='quizid'>Quiz Name</label>
        <select name="quizid" id="quizid" required>
            <?php 
            foreach($res as $row)
            {
                echo "<option value='" . $row['quizid'] . "'>" . $row['quizname'] . "</option>";
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