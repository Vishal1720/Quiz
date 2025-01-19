<?php include "dbconnect.php" ;
if($_SESSION['status']=="loggedout" || $_SESSION['status']=="" || empty($_SESSION['status'])) 
{
    header("Location: login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
    <title>Document</title>

</head>
<body>
<link rel="stylesheet" href="nav.css">
<nav style="width:fit-content">
	<a href="#">Home</a>
	<a href="#">Quizzes</a>
    <a href="#">Contact</a>
	<a href="./logout.php">logout</a>
	
	<div class="animation start-home"></div>
</nav>


</body>
</html>