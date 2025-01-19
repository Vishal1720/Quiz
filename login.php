<?php
require "dbconnect.php";
if(isset($_POST['email']) && isset($_POST['password'])) {
$email=$_POST['email'];
$pwd=$_POST['password'];
$query="Select email from users where email='$email' and password='$pwd' ";
$result=$con->query($query);
if($result->num_rows>0)
{
    $_SESSION['status']='loggedin';
    $_SESSION['email']=$email;
    header("Location: index.php");
}
else
{
    $res2=$con->query("Select email from `users` where  `email`='$email'");
if($res2->num_rows==1)
{echo "<script>
   alert('password is not correct')</script>";}
else
{
    echo "<script>alert(' $email is not registered');</script>";}
}


}
?>