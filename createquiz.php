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
        .create-quiz-container {
            max-width: 600px;
            margin: 3rem auto;
            padding: 0 1rem;
        }

        .page-title {
            color: #fff;
            text-align: center;
            font-size: 2.2rem;
            margin-bottom: 2rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .create-form {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 15px;
            padding: 2.5rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.5s ease;
        }

        .form-group {
            margin-bottom: 1.8rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 14px;
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
        }

        .select-category {
            width: 100%;
            padding: 14px;
            background: rgba(74, 144, 226, 0.1);
            border: 1px solid rgba(74, 144, 226, 0.3);
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            margin-top: 0.5rem;
        }

        .create-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(45deg, #4a90e2, #357abd);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .create-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(74, 144, 226, 0.3);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<nav style="font-size:16px;overflow:none">
<a href="./index.php">Home</a>
<a href="./quizmanip.php">Edit</a>
<a href="./createquiz.php">Create</a>	
    <a href="./quizform.php">Insert</a>
	<a style="width:100px" href="./logout.php">Logout</a>
	<div class="animation create" ></div>
</nav>

    <div class="create-quiz-container">
        <h1 class="page-title">Create New Quiz</h1>
        
        <form action="./createquiz.php" method="POST" class="create-form">
            <div class="form-group">
                <label for="quizname">Quiz Name</label>
                <input type="text" 
                       name="quizname" 
                       id="quizname" 
                       class="form-control"
                       placeholder="Enter a unique quiz name" 
                       required>
            </div>

            <div class="form-group">
                <label for="cat">Select Category</label>
                <select name="category" id="cat" class="select-category" required>
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
            </div>

            <button type="submit" class="create-btn">Create Quiz</button>
        </form>
    </div>
</body>
</html>