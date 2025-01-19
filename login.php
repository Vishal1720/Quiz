<?php
require "dbconnect.php";

if(isset($_POST['email']) && isset($_POST['password'])) 
{
    echo "In the Form";
$email=$_POST['email'];
$pwd=$_POST['password'];
$query="Select email from users where email='$email' and password='$pwd' ";
$result=$con->query($query);
if($result->num_rows>0)
{
    echo "Login Successfull";
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
    <style>
         
        form {
    max-width: 400px;
    margin: 0 auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #f9f9f9;
}
label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}
input {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button {
    width: 100%;
    padding: 10px;
    background-color: #34495e;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

button:hover {
    background-color: #2c3e50;
}
    </style>
   <link rel="stylesheet" href="nav.css">
</head>
<body>
    <nav style="width:fit-content">
    <a href="./login.php">Login</a>
    <a href="./register.php">Register</a>
	<a href="#">ABout</a>
    
	<div class="animation start-home"></div>
    </nav>
    <form action="./login.php" method="post" style="margin-top: 50px;font-size:15px;">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="Username" required>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Password" required>
        <button>Login</button>
    </form>
</body>
</html>