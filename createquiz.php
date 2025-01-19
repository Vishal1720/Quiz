<?php
 include "dbconnect.php" ;
if($_SESSION['status']=="loggedout" || $_SESSION['status']=="" || empty($_SESSION['status'])) 
{
    header("Location: login.php");
    exit();
}

// logic for first step in creating quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
if(isset($_POST['category']) && isset($_POST['quizname']) )
{
    $email=$_SESSION['email'];
    $cat=$_POST['category'];
    $quizname=$_POST['quizname'];
    $query="INSERT INTO `quizdetails` (`category`, `quizname`, `email`) 
    VALUES ( '$cat', '$quizname', '$email')";
    $res=$con->query($query);
    if($res)
    {
    $res2=$con->query("Select quizid from quizdetails where quizname = '$quizname'");
    if($res2->num_rows>0)
    {
    $res2=$res2->fetch_assoc();
        $_SESSION['qid']=$res2['quizid'];
        header("Location:quizform.php");
        exit();
    }
    else{
        echo "Unable to fetch category id ";
    }
    }
    else{
        echo "<script>alert('Error occured');</script>";
    }

}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="form.css">
    <style>
        form{
            margin-top: 30px;
        }
        select{
            padding: 10px;
            border-radius:10px ;
        }
    </style>
</head>
<body>

<nav style="width:fit-content;font-size:16px;overflow:none">
	<a href="#">Home</a>
	<a href="#">Quizzes</a>
    <a href="./quizformtemplate.html">Create Quiz</a>
    <a href="./">Contact</a>
	<a style="width:100px" href="./logout.php">logout</a>
	<div class="animation start-home" ></div>
</nav>

    <form action="./createquiz.php" method="POST">
        <label for="quizname">Quizname</label>
        <input type="text" name="quizname" id="quizname" placeholder="Quiz Name (Should be unique)" required>
        <label for="cat">Category</label>
        <select name="category" id="cat" required>
            <option value="Programming">Programming</option>
            <option value="Programming">Literature</option>
        </select>
        <input type="submit" value="Create Quiz">
    </form>
</body>
</html>