<?php
require "dbconnect.php";
session_unset();//reset all session variables
session_destroy();
header("Location: login.php");
?>