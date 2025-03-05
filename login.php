<?php
require "dbconnect.php";

if(isset($_POST['email']) && isset($_POST['password'])) 
{
$email=$_POST['email'];
$pwd=$_POST['password'];
$query="Select email from users where email='$email' and password='$pwd' ";
$result=$con->query($query);
if($result->num_rows>0)
{
    $_SESSION['status']="loggedin";
    $_SESSION['email']=$email;
    header("Location: index.php");
    exit();
}
else
{
    $res2=$con->query("Select email from `users` where  `email`='$email'");
    $_SESSION['status']='loggedout';
if($res2->num_rows>=1)
{
    echo "<script>
   alert('password is not correct')</script>";
}
else
{
    echo "<script>alert(' $email is not registered');</script>";}
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
    <link rel="stylesheet" href="form.css">
   <link rel="stylesheet" href="nav.css">
   <style>
        nav {
            width: 250px;
            height: 50px;
            line-height: 20px;
        }
        /* Remove existing nav styles */
   </style>
   <style>
    body {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .auth-container {
        width: 100%;
        max-width: 400px;
        margin: 2rem auto;
        animation: fadeIn 0.5s ease;
    }

    form {
        background: rgba(255, 255, 255, 0.08);
        margin-top: 2rem;
    }

    .form-title {
        color: #fff;
        text-align: center;
        font-size: 1.8rem;
        margin-bottom: 2rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
</head>
<body>
    <nav class="two-items">
        <a href="./login.php" class="active">Login</a>
        <a href="./register.php">Register</a>
        <div class="animation login"></div>
    </nav>
    <form action="./login.php" method="post" style="margin-top: 50px;font-size:15px;">
        <label for="email">Email</label>
        <input type="email" name="email" maxlength="222" id="email" placeholder="Enter registered email" required>
        <label for="password">Password</label>
        <input type="password" name="password" maxlength="222" id="password" placeholder="Enter Password" required>
        <button>Login</button>
    </form>
</body>
</html>