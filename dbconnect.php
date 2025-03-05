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

// Set MySQL to return all results
mysqli_options($con, MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
mysqli_set_charset($con, 'utf8mb4');

if (session_status() === PHP_SESSION_NONE) 
    {session_start();
        if(!isset($_SESSION['status']))
{
$_SESSION['status']=null;//initialising values if they haven't been initialises
}
if(!isset($_SESSION['email']))
$_SESSION['email']=null;
if(!isset($_SESSION['qid']))
{
    $_SESSION['qid']=null;
}
    }