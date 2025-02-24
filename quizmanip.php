<?php
 include "dbconnect.php" ;
 
if($_SESSION['status']=="loggedout" || $_SESSION['status']=="" || empty($_SESSION['status'])) 
{
    header("Location: login.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['quizid']) && !empty($_POST['qid']) && 
    !empty($_POST['question']) && !empty($_POST['option1']) && 
    !empty($_POST['option2']) && !empty($_POST['option3']) && !empty($_POST['option4']))
  {  $quizid = $_POST['quizid'];
        $id=$_POST['qid'];//this is question id
        $question = $_POST['question'];
        $option1 = $_POST['option1'];
        $option2 = $_POST['option2'];
        $option3 = $_POST['option3'];
        $option4 = $_POST['option4'];

    if (isset($_POST['update'])) {  
        

        // Check if quizid is valid
        if (!empty($quizid)) {
            $query = "UPDATE quizes SET 
                        question='$question', 
                        option1='$option1', 
                        option2='$option2', 
                        option3='$option3', 
                        option4='$option4' 
                      WHERE quizid='$quizid' and id='$id'";

            if ($con->query($query)) {
                echo "<script>alert('Question Updated Successfully');</script>";
            } else {
                echo "<script>alert('Error updating question: " . $con->error . "');</script>";
            }
        } else {
            echo "<script>alert('Invalid question ID');</script>";
        }
    }

    if (isset($_POST['delete'])) {  // When "Delete" is clicked
        if (isset($quizid)) {
            $deleteQuery = "DELETE FROM quizes WHERE quizid='$quizid' and id='$id'";
            if ($con->query($deleteQuery)) {
                echo "<script>alert('Question Deleted Successfully');</script>";
            } else {
                echo "<script>alert('Error deleting question: " . $con->error . "');</script>";
            }
        } else {
            echo "<script>alert('Invalid question ID');</script>";
        }
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
nav .edit,a:nth-child(2):hover~.animation {
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
	<div class='animation edit' ></div>
</nav>

            <?php 
            $query="select * from quizdetails where email='{$_SESSION['email']}'";

            $res=$con->query($query);
            
            if($res->num_rows==0)
            {
            echo "<script>alert('First create a quiz')</script>";
            header("Location: createquiz.php");
            exit();
            }
            else{
                
            }?>
            <form method="POST" action="quizmanip.php">
                <select name="quizid" required>
            <?php
            foreach($res as $row)
            {
                echo "<option value='".$row['quizid']."'>".$row['quizname']."</option>";
            }?>
            </select>
            <input type="submit" value="Refresh" name="refresh">

            </form>
            <?php 
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['refresh'])) 
            {
                $quizid=$_POST['quizid'];
                $query="select * from quizes where quizid='$quizid'";
                $res2=$con->query($query);
                if($res2->num_rows==0)
                {
                    echo "<script>alert('No questions found')</script>";
                }
                else{
                    while($res3=$res2->fetch_assoc())
                    {?>
                    <form method='POST' action='quizmanip.php' style='max-width:100%;display:flex;flex-direction:row;'>                
        <input type="text" name="question" style="width:fit-content" value="<?= $res3['question'] ?>">
        
        <input type="text" onkeyup="changenow(<?=$res3['ID']?>,0,4)" class="<?=$res3['ID']?>"  required name="option1" style="width:fit-content" value="<?=$res3['option1']?>">
        <input type="text" onkeyup="changenow(<?=$res3['ID']?>,1,5)" class="<?=$res3['ID']?>" required name="option2" style="width:fit-content" value="<?=$res3['option2']?>">
        <input type="text" onkeyup="changenow(<?=$res3['ID']?>,2,6)" class="<?=$res3['ID']?>" required name="option3" style="width:fit-content" value="<?=$res3['option3']?>">
        <input type="text" onkeyup="changenow(<?=$res3['ID']?>,3,7)" class="<?=$res3['ID']?>" required name="option4" style="width:fit-content" value="<?=$res3['option4']?>">
        <select>
            <option value="1"  class="<?=$res3['ID']?>" <?php if($res3['answer']==$res3['option1']) echo "selected";?>><?=$res3['option1']?></option>
            <option value="2" class="<?=$res3['ID']?>" <?php if($res3['answer']==$res3['option2']) echo "selected";?>><?=$res3['option2']?></option>
            <option value="3" class="<?=$res3['ID']?>" <?php if($res3['answer']==$res3['option3']) echo "selected";?>><?=$res3['option3']?></option>
            <option value="4" class="<?=$res3['ID']?>" <?php if($res3['answer']==$res3['option4']) echo "selected";?>><?=$res3['option4']?></option>
        </select>
        <input type="hidden" required name="quizid" style="width:fit-content" value="<?=$res3['quizid']?>"> 
        <input type="hidden" required name="qid" style="width:fit-content" value="<?=$res3['ID']?>"> 
        <input type="submit" value="Update" name="update">
        <input type='submit' value='Delete' name='delete'>
        </form>
                    <?php
                    }
                }
            }
            ?>
        
            <script>
function changenow(classname,num1,num2)
{
    var x=document.getElementsByClassName(classname);
    x[num2].textContent=x[num1].value;
    console.log("in");
}

</script>
        </body>
        </html>