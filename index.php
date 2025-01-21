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
    <link rel="stylesheet" href="nav.css">
    <title>Quiz</title>
<style>
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
nav .create,a:nth-child(2):hover~.animation {
	width: 140px;
	left: 110px;
	background-color:rgb(26, 61, 188);
}
nav .quizes,a:nth-child(3):hover~.animation {
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

.card{
border: 2px;
background-color: #f9f9f9;
min-width: fit-content;
max-width: 200px;
padding: 10px;
}
.titlecard{
	text-align: center;
}
</style>
</head>
<body>

<nav>
	<a href="#">Home</a>
	<a href="#">Quizzes</a>
    <a href="./createquiz.php">Create </a>
    <a href="./quizform.php">Insert</a>
	<a style="width:100px" href="./logout.php">Logout</a>
	<div class="animation start-home" ></div>
</nav>
<?php 
$query="SELECT * FROM category";
$res=$con->query($query);
while($row=$res->fetch_assoc())
{
	$cat=$row['categoryname'];
	
	$query2="SELECT * FROM `quizdetails` WHERE `category`='$cat'";
	$res2=$con->query($query2);

	if($res2->num_rows==0)//continuing if no quizzes are there in the category
	continue;
echo "<div class='categorycard'>";
	echo "<h1>".$row['categoryname']."</h1>";
	while($row2=$res2->fetch_assoc())
	{
		echo "<div class='card'>";
		echo "<h3 class='titlecard'> {$row2['quizname']}</h3>";
		echo "<h3>ID:{$row2['quizid']}</h3>";
		echo "</div>";
	}
	echo "</div>";
}
?>

</body>
</html>