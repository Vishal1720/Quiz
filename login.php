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
    <title>Document</title>
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
    <link rel="stylesheet" href="form.css">
   <link rel="stylesheet" href="nav.css">
   <style>
         nav{
              width:25%;
              height:50px;
              line-height:20px;
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
	width: 120px;
}
nav a:nth-child(1):hover~.animation {
	width: 110px;
	left: 0;
	background-color: #1abc9c;
}
nav a:nth-child(2):hover~.animation {
	width: 150px;
	left: 110px;
	background-color:rgb(26, 61, 188);
}
nav a:nth-child(3):hover~.animation {
	width: 120px;
	left:260px;
	background-color:rgb(42, 148, 177);
}
   </style>
</head>
<body>
    <nav >
    <a href="./login.php">Login</a>
    <a href="./register.php">Register</a>
	<a href="#">About</a>
    
	<div class="animation start-home"></div>
    </nav>
    <form action="./login.php" method="post" style="margin-top: 50px;font-size:15px;">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="Enter registered email" required>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Enter Password" required>
        <button>Login</button>
    </form>
</body>
</html>