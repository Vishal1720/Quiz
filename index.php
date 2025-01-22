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
.categorycard{
	background-color:#2E2D4D;
	display: flex;
	padding: 20px;
	margin: auto;
	margin-top: 15px;
	border-radius: 14px;
	width:50%;
	border: 0.1px solid whitesmoke;
}
.card{
border: 1px solid black;
border-radius: 15px;
background-color:#337357;
filter: blur(0.2px);

width: 150px;
height: 150px;
padding: 10px;
margin:5px;
cursor: pointer;
}
.titlecard{
	text-align: center;
	color: white;
	text-shadow: 1px 1px 1px black;
	font-size: 19px;
	
}
.categorytitle{
	text-align: center;
	color: white;
	text-shadow: 1.2px 1.2px 1px black;
}
input[type='submit']{
	background-color:rgb(125, 106, 201);
	padding: 10px 20px;
	text-align: center;
	font-size: 16px;
	border-radius: 5px;
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
	echo "<h1 class='categorytitle'>".$row['categoryname']."</h1>";
echo "<div class='categorycard'>";
	
	while($row2=$res2->fetch_assoc())
	{
		echo "<div class='card'>";
		echo "<div style='height:75px;display:flex;justify-content:center;align-items:center;'><h3 class='titlecard'> {$row2['quizname']}</h3></div>";
		echo "<form  method='GET' action='/takequiz.php'>
		<input type='hidden' name='quizid' value='{$row2['quizid']}'>
		<div style='height:75px;'>
		<input type='submit' style='width:100%;padding:7px;font-size:14px;' value='Take Quiz'>
		</div>
		</form>";
		echo "</div>";
	}
	echo "</div>";
}
?>

</body>
</html>