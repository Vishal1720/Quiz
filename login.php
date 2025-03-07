<?php
require "dbconnect.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(isset($_POST['login_type']) && isset($_POST['username']) && isset($_POST['password'])) 
{
    if($_POST['login_type'] === 'admin') {
        // Admin login
        if($_POST['username'] === 'admin' && $_POST['password'] === 'admin123') {
            $_SESSION['status'] = "admin";
            $_SESSION['username'] = 'admin';
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid admin credentials";
        }
    } else {
        // Regular user login
        $email = $_POST['username'];
        $pwd = $_POST['password'];
        
        // First get the hashed password for this email
        $query = "SELECT email, password FROM users WHERE email = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Verify the password
            if(password_verify($pwd, $user['password'])) {
                $_SESSION['status'] = "loggedin";
                $_SESSION['email'] = $email;
                header("Location: index.php");
                exit();
            } else {
                $error = "Incorrect password";
            }
        } else {
            $error = "Email is not registered";
            $_SESSION['status'] = 'loggedout';
        }
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

        .error-message {
            background: rgba(255, 87, 87, 0.1);
            border: 1px solid rgba(255, 87, 87, 0.3);
            color: #ff5757;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
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
            <?php if(isset($error) && $_POST['login_type'] === 'user'): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form action="./login.php" method="post">
                <input type="hidden" name="login_type" value="user">
                <label for="user-email">Email</label>
                <input type="email" name="username" maxlength="222" id="user-email" placeholder="Enter your email" required>
                <label for="user-password">Password</label>
                <input type="password" name="password" maxlength="222" id="user-password" placeholder="Enter your password" required>
                <button type="submit">Login as User</button>
            </form>
        </div>

        <!-- Admin Login Section -->
        <div class="login-section admin-section">
            <h2 class="section-title">Admin Login</h2>
            <p class="login-description">Access the admin dashboard to manage quizzes and users</p>
            <?php if(isset($error) && $_POST['login_type'] === 'admin'): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form action="./login.php" method="post">
                <input type="hidden" name="login_type" value="admin">
                <label for="admin-username">Username</label>
                <input type="text" name="username" maxlength="222" id="admin-username" placeholder="Enter admin username" required>
                <label for="admin-password">Password</label>
                <input type="password" name="password" maxlength="222" id="admin-password" placeholder="Enter admin password" required>
                <button type="submit">Login as Admin</button>
            </form>
        </div>
    </div>
</body>
</html>