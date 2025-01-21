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
    //check for duplicate quizname
    $query="Select quizname from quizdetails where quizname='$quizname'";
    if($con->query($query)->num_rows>0)
    {
        echo "<script>alert('Quizname already exists');
        window.location.href='./createquiz.php';</script>";
        
        exit();
    }


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
    <title>Quiz</title>
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
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
#cat{
    width: 100%;
    text-align: center;
    font-size: 16px;
}
    </style>
</head>
<body>

<nav style="font-size:16px;overflow:none">
<a href="./index.php">Home</a>
<a href="#">Quizzes</a>
<a href="./createquiz.php">Create</a>	
    <a href="./quizform.php">Insert</a>
	<a style="width:100px" href="./logout.php">Logout</a>
	<div class="animation create" ></div>
</nav>

    <form action="./createquiz.php" method="POST">
        <label for="quizname">Quizname</label>
        <input type="text" name="quizname" id="quizname" placeholder="Quiz Name (Should be unique)" required>
        <label for="cat">Category</label>
       
        <select name="category" id="cat" required>
        <?php 
        $query="Select categoryname from category";
        $res=$con->query($query);
        while($row=$res->fetch_assoc())
        {
            $cat=$row['categoryname'];
            echo "<option  value='$cat'>$cat</option>";
        }
        ?>
        </select>
        <input type="submit" value="Create Quiz">
    </form>
</body>
</html>