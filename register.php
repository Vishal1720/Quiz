<?php 
require "dbconnect.php";
if(isset($_POST['email']) && isset($_POST['name']) &&isset($_POST['contact']) &&isset($_POST['password'])) 
{
$email=$_POST['email'];
$name=$_POST['name'];
$contact=$_POST['contact'];
$pwd=$_POST['password'];

//for check if email already exists
$query="Select email from users where email='$email'";
$res=$con->query($query);
if($res->num_rows>0)
{
    echo "<script>alert('Email already exists')</script>";
}
else
{
    $query="Insert into users values('$email','$name','$contact','$pwd')";
    $res2=$con->query($query);
    if(!$res2)
    {
        echo "<script>alert('Error in registering')</script>";
    }
    else
    echo "<script>alert('Registered successfully')</script>";

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
    <link rel="stylesheet" href="form.css"> <link rel="stylesheet" href="nav.css">
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
	width: 120px;
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
   
   </style>
</head>
<body>
    <nav style="width:fit-content">
        <a href="./register.php">Register</a>
        <a href="./login.php">Login</a>
        
        <a href="#">About</a>
        
        <div class="animation start-home"></div>
        </nav>
        <form action="./register.php" id="regform" method="post" style="margin-top: 50px;font-size:15px;">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" placeholder="Email" required>
            <label for="name">Name</label>
            <input type="text" name="name" id="name" placeholder="Name" title="Only alphabets"  pattern="^[a-z A-Z \s]+$" required>
            <label for="contact">Contact</label>
            <input type="text" name="contact"  pattern="^\d{10}$" maxlength="10" placeholder="Contact info" title="Enter phone number containing 10 digits" required>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <label for="conf">Confirm Password</label>
            <input type="password" id="conf" placeholder="Confirm Password" required>
            <input type="submit" class="button" value="Register">
        </form>
        <script>
            document.getElementById("regform").addEventListener("submit",(e)=>{
                e.preventDefault();
                pwd=document.querySelector("#password").value
                cpwd=document.querySelector("#conf").value
                if(pwd===cpwd)
            {
                document.getElementById("regform").submit();
            }
            else
           alert("passwords don't match ")
        
            })
        </script>
</body>
</html>