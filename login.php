<?php
require "dbconnect.php";

if(isset($_POST['email']) && isset($_POST['password'])) 
{
$email=$_POST['email'];
$pwd=$_POST['password'];
$query="Select email, role from users where email='$email' and password='$pwd' ";
$result=$con->query($query);
if($result->num_rows>0)
{
    $row = $result->fetch_assoc();
    $_SESSION['status']="loggedin";
    $_SESSION['email']=$email;
    $_SESSION['role']=$row['role'];
    if($row['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: takequiz.php");
    }
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
    <title>Quiz - Login</title>
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
    <link rel="stylesheet" href="form.css">
    <link rel="stylesheet" href="nav.css">
    <style>
        nav {
            width: 250px;
            height: 50px;
            line-height: 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 800px;
            margin: 2rem auto;
            display: flex;
            justify-content: space-between;
            gap: 2rem;
            animation: fadeIn 0.5s ease;
        }

        .login-section {
            flex: 1;
            background: rgba(255, 255, 255, 0.08);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            color: #fff;
            text-align: center;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .admin-section {
            border-left: 2px solid rgba(255, 255, 255, 0.1);
        }

        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        form {
            background: transparent;
            margin-top: 1rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-description {
            color: #a0a0a0;
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <nav class="two-items">
        <a href="./login.php" class="active">Login</a>
        <a href="./register.php">Register</a>
        <div class="animation login"></div>
    </nav>

    <div class="auth-container">
        <!-- User Login Section -->
        <div class="login-section">
            <h2 class="section-title">User Login</h2>
            <p class="login-description">Access your account to take quizzes and track your progress</p>
            <form action="./login.php" method="post">
                <label for="user-email">Email</label>
                <input type="email" name="email" maxlength="222" id="user-email" placeholder="Enter your email" required>
                <label for="user-password">Password</label>
                <input type="password" name="password" maxlength="222" id="user-password" placeholder="Enter your password" required>
                <button type="submit">Login as User</button>
            </form>
        </div>

        <!-- Admin Login Section -->
        <div class="login-section admin-section">
            <h2 class="section-title">Admin Login</h2>
            <p class="login-description">Access the admin dashboard to manage quizzes and users</p>
            <form action="./login.php" method="post">
                <label for="admin-email">Email</label>
                <input type="email" name="email" maxlength="222" id="admin-email" placeholder="Enter admin email" required>
                <label for="admin-password">Password</label>
                <input type="password" name="password" maxlength="222" id="admin-password" placeholder="Enter admin password" required>
                <button type="submit">Login as Admin</button>
            </form>
        </div>
    </div>
</body>
</html>