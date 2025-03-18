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
            $_SESSION['email'] = 'admin123@gmail.com';
            if(isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: " . $redirect);
                exit();
            }
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
                if(isset($_SESSION['redirect_after_login'])) {
                    $redirect = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    header("Location: " . $redirect);
                    exit();
                }
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

if(isset($_GET['redirect'])) {
    $redirect = htmlspecialchars($_GET['redirect']);
    // Store redirect URL in session
    $_SESSION['redirect_after_login'] = $redirect;
}
?>
<?php include "components/header.php"; ?>
    <style>
        .auth-container {
            width: 100%;
            max-width: 1000px;
            margin: 2rem auto;
            display: flex;
            justify-content: space-between;
            gap: 3rem;
            animation: fadeIn 0.5s ease;
            padding: 0 1rem;
        }

        .login-section {
            flex: 1;
            background: var(--quiz-card-bg);
            padding: 2.5rem;
            border-radius: 15px;
            border: 1px solid var(--quiz-card-border);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .login-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 0% 0%, rgba(99, 102, 241, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(236, 72, 153, 0.1) 0%, transparent 50%);
            z-index: 0;
        }

        .login-section > * {
            position: relative;
            z-index: 1;
        }

        .section-title {
            font-size: 2rem;
            margin-bottom: 1rem;
            text-align: center;
            background: linear-gradient(135deg, #fff, var(--primary));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }

        .admin-section {
            border-left: 2px solid var(--quiz-card-border);
        }

        form {
            background: transparent;
            margin-top: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text);
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--quiz-card-border);
            border-radius: 8px;
            color: var(--text);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted);
            padding: 5px;
            background: none;
            border: none;
            width: auto;
        }

        .password-toggle:hover {
            color: var(--text);
            transform: translateY(-50%);
            box-shadow: none;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }

        button {
            width: 100%;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 8px;
            color: var(--text);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.3);
        }

        .login-description {
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1rem;
            line-height: 1.6;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #ef4444;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
                gap: 2rem;
            }

            .admin-section {
                border-left: none;
                border-top: 2px solid var(--quiz-card-border);
                padding-top: 2rem;
            }
        }
    </style>
</head>
<body>


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
                <div class="form-group">
                    <label for="user-email">Email</label>
                    <input type="email" name="username" maxlength="222" id="user-email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="user-password">Password</label>
                    <div class="password-field">
                        <input type="password" name="password" maxlength="222" id="user-password" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('user-password')">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>
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
                <div class="form-group">
                    <label for="admin-username">Username</label>
                    <input type="text" name="username" maxlength="222" id="admin-username" placeholder="Enter admin username" required>
                </div>
                <div class="form-group">
                    <label for="admin-password">Password</label>
                    <div class="password-field">
                        <input type="password" name="password" maxlength="222" id="admin-password" placeholder="Enter admin password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('admin-password')">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit">Login as Admin</button>
            </form>
        </div>
    </div>
<?php include "components/footer.php"; ?>
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('svg');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }
    </script>