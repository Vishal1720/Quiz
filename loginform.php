<?php  
require "login.php";
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
    <a href="./loginform.php">Login</a>
    <a href="./register.php">Register</a>
	<a href="#">ABout</a>
    
	<div class="animation start-home"></div>
    </nav>
    <form action="./loginform.php" method="post" style="margin-top: 50px;font-size:15px;">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="Username" required>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Password" required>
        <button>Login</button>
    </form>
</body>
</html>