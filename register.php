<?php 
require "dbconnect.php";
if(isset($_POST['email']) && isset($_POST['name']) && isset($_POST['contact']) && isset($_POST['password'])) 
{
    $email = trim($_POST['email']);
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $pwd = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists using prepared statement
    $stmt = $con->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
        echo "<script>alert('Email already exists')</script>";
    } else {
        // Insert new user using prepared statement
        $stmt = $con->prepare("INSERT INTO users (email, name, password, contact) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $email, $name, $pwd, $contact);
        
        if($stmt->execute()) {
            echo "<script>
                alert('Registered successfully');
                window.location.href = 'login.php';
            </script>";
        } else {
            echo "<script>alert('Error in registering')</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Quiz System</title>
    <link rel="shortcut icon" href="quiz.png" type="image/x-icon">
    <link rel="stylesheet" href="form.css">
    <link rel="stylesheet" href="nav.css">
    <style>
        nav {
            width: 20%;
            height: 50px;
            line-height: 20px;
            min-width: 300px;
        }
        
        nav a {
            text-align: center;
            width: 50% !important;
        }

        .animation {
            width: 50%;
        }

        nav a:nth-child(1):hover ~ .animation {
            width: 50%;
            left: 0;
            background-color: #1abc9c;
        }

        nav .register, nav a:nth-child(2):hover ~ .animation {
            width: 50%;
            left: 50%;
            background-color: rgb(26, 61, 188);
        }

        .password-requirements {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .error {
            color: #ff4444;
            font-size: 0.8em;
            margin-top: 5px;
            display: none;
        }

        input:invalid + .error {
            display: block;
        }

        .password-match-error {
            display: none;
            color: #ff4444;
            font-size: 0.8em;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <nav>
        <a href="./login.php">Login</a>
        <a href="./register.php">Register</a>
        <div class="animation register"></div>
    </nav>

    <form action="./register.php" id="regform" method="post" style="margin-top: 50px;font-size:15px;">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" placeholder="Enter your email" required>
            <div class="error">Please enter a valid email address</div>
        </div>

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" placeholder="Enter your name" 
                   pattern="^[a-zA-Z\s]+$" title="Only alphabets and spaces allowed" required>
            <div class="error">Name should only contain letters and spaces</div>
        </div>

        <div class="form-group">
            <label for="contact">Contact</label>
            <input type="tel" name="contact" id="contact" pattern="^\d{10}$" maxlength="10" 
                   placeholder="Enter your phone number" title="Enter 10-digit phone number" required>
            <div class="error">Please enter a valid 10-digit phone number</div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" 
                   pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$" 
                   placeholder="Enter your password" required>
            <div class="password-requirements">
                Password must be at least 8 characters long and contain:
                <ul>
                    <li>At least one letter</li>
                    <li>At least one number</li>
                </ul>
            </div>
            <div class="error">Password must meet the requirements above</div>
        </div>

        <div class="form-group">
            <label for="conf">Confirm Password</label>
            <input type="password" id="conf" placeholder="Confirm your password" required>
            <div class="password-match-error">Passwords do not match</div>
        </div>

        <input type="submit" class="button" value="Register">
    </form>

    <script>
        const form = document.getElementById("regform");
        const password = document.getElementById("password");
        const confirm = document.getElementById("conf");
        const matchError = document.querySelector(".password-match-error");

        function validatePasswords() {
            if (password.value !== confirm.value) {
                matchError.style.display = "block";
                confirm.setCustomValidity("Passwords do not match");
            } else {
                matchError.style.display = "none";
                confirm.setCustomValidity("");
            }
        }

        password.addEventListener("input", validatePasswords);
        confirm.addEventListener("input", validatePasswords);

        form.addEventListener("submit", (e) => {
            e.preventDefault();
            validatePasswords();
            
            if (form.checkValidity() && password.value === confirm.value) {
                form.submit();
            }
        });
    </script>
</body>
</html>