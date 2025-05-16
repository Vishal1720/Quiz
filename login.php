<?php
require "dbconnect.php";
require "google_config.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set error message
$error = '';

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Existing styles */
        .login-container {
            width: 100%;
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
            animation: fadeIn 0.5s ease;
        }

        .login-tabs {
            display: flex;
            max-width: 600px;
            margin: 0 auto;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 2rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            overflow: hidden;
        }

        .tab {
            padding: 1rem 2rem;
            flex: 1;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--text-muted);
            position: relative;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .tab.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 2px solid var(--primary);
            font-weight: 500;
        }

        .tab:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        .login-section {
            display: none;
            background: var(--quiz-card-bg);
            padding: 2.5rem;
            border-radius: 15px;
            border: 1px solid var(--quiz-card-border);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
            max-width: 600px;
            margin: 0 auto;
        }

        .login-section.active {
            display: block;
            animation: fadeIn 0.5s ease;
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

        .section-description {
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1rem;
            line-height: 1.6;
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

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }

        button {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, var(--primary), #4f46e5);
            color: #fff;
            font-size: 1rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.3);
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            width: 30px;
            height: 30px;
            cursor: pointer;
            box-shadow: none;
        }

        .password-toggle:hover {
            color: var(--text);
            transform: translateY(-50%);
            box-shadow: none;
        }
        
        /* Remove Google login related styles */
        .google-login-button,
        .login-divider {
            display: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Login Tabs -->
        <div class="login-tabs">
            <div class="tab active" onclick="switchTab('user')">User Login</div>
            <div class="tab" onclick="switchTab('admin')">Admin Login</div>
        </div>
        
        <?php if(!empty($error)): ?>
        <div class="error-message" style="max-width: 600px; margin: 0 auto 1.5rem;">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <!-- User Login Section -->
        <div id="user-section" class="login-section active">
            <h2 class="section-title">User Login</h2>
            <p class="section-description">Login to manage and attempt quizzes</p>
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
                
                <p style="text-align: center; margin-top: 1.5rem; color: var(--text-muted);">
                    Don't have an account? <a href="register.php" style="color: var(--primary);">Register</a>
                </p>
            </form>
        </div>

        <!-- Admin Login Section -->
        <div id="admin-section" class="login-section">
            <h2 class="section-title">Admin Login</h2>
            <p class="section-description">Login to manage all quizzes and users</p>
            <form action="./login.php" method="post">
                <input type="hidden" name="login_type" value="admin">
                <div class="form-group">
                    <label for="admin-username">Username</label>
                    <input type="text" name="username" maxlength="222" id="admin-username" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label for="admin-password">Password</label>
                    <div class="password-field">
                        <input type="password" name="password" maxlength="222" id="admin-password" placeholder="Enter your password" required>
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

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.login-section').forEach(s => s.classList.remove('active'));
            
            if (tab === 'user') {
                document.querySelector('.tab:nth-child(1)').classList.add('active');
                document.getElementById('user-section').classList.add('active');
            } else {
                document.querySelector('.tab:nth-child(2)').classList.add('active');
                document.getElementById('admin-section').classList.add('active');
            }
        }

        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = input.nextElementSibling.querySelector('svg');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = `
                    <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                `;
            } else {
                input.type = 'password';
                icon.innerHTML = `
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                `;
            }
        }
    </script>
<?php include "components/footer.php"; ?>
