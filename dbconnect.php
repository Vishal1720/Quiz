<?php 
$server="localhost";
$username="root";
$pwd="";
$database="quiz";
$con=mysqli_connect($server,$username,$pwd,$database);
if(!$con)
{
    die("Connection failed".mysqli_connect_error());
}

if (session_status() === PHP_SESSION_NONE) 
    {session_start();
        if(!isset($_SESSION['status']))
$_SESSION['status']='';//initialising values if they haven't been initialises
if(!isset($_SESSION['email']))
$_SESSION['email']=null;
    }