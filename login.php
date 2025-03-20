<?php
require "dbconnect.php";
require "google_config.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set error message
$error = '';

// Handle OAuth callback from Google
if (isset($_GET['code'])) {
    // This is a Google OAuth callback - process the code
    $code = $_GET['code'];
    $token_url = $google_token_url;
    
    // Check if this is from registration page
    $is_registration = isset($_GET['state']) && $_GET['state'] === 'register';

    // Log the code received
    error_log("Google OAuth - Received code: " . substr($code, 0, 10) . "..." . ($is_registration ? " (from registration)" : ""));

    // Prepare the token request
    $token_data = [
        'code' => $code,
        'client_id' => $google_client_id,
        'client_secret' => $google_client_secret,
        'redirect_uri' => $google_redirect_url,
        'grant_type' => 'authorization_code'
    ];

    // Log the token request parameters
    error_log("Google OAuth - Token request parameters: " . print_r($token_data, true));

    // Initialize cURL session for token request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // Only for development - remove in production
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);      // Only for development - remove in production
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
    ]);

    // Execute the token request
    $token_response = curl_exec($ch);
    $curl_info = curl_getinfo($ch);
    $token_error = curl_error($ch);
    curl_close($ch);

    // Log curl info
    error_log("Google OAuth - cURL Info: " . print_r($curl_info, true));

    if ($token_error) {
        error_log("Google OAuth Error - cURL Error: " . $token_error);
        $error = "Google token request failed. Please try again.";
    } else {
        // Log the raw response
        error_log("Google OAuth - Raw token response: " . $token_response);

        // Parse the token response
        $token_data = json_decode($token_response, true);
        $json_error = json_last_error();

        // Check for JSON parsing errors
        if ($json_error !== JSON_ERROR_NONE) {
            error_log("Google OAuth Error - JSON parsing error: " . json_last_error_msg());
            $error = "Error processing Google response. Please try again.";
        } 
        else if (!isset($token_data['access_token'])) {
            // Enhanced error logging
            error_log("Google OAuth Error - Token Response: " . print_r($token_response, true));
            if (isset($token_data['error'])) {
                error_log("Google OAuth Error - Error Type: " . $token_data['error']);
                error_log("Google OAuth Error - Error Description: " . ($token_data['error_description'] ?? 'No description'));
            }
            $error = "Invalid response from Google. Please try again.";
        } else {
            // Access token received, now get user information
            $access_token = $token_data['access_token'];

            // Initialize cURL session for user info request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $google_userinfo_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // Only for development - remove in production
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access_token]);

            // Execute the user info request
            $userinfo_response = curl_exec($ch);
            $userinfo_error = curl_error($ch);
            curl_close($ch);

            if ($userinfo_error) {
                error_log("cURL Error in user info request: " . $userinfo_error);
                $error = "Error retrieving user information from Google. Please try again.";
            } else {
                // Parse the user info response
                $google_user = json_decode($userinfo_response, true);

                if (!isset($google_user['email'])) {
                    error_log("Invalid user info response: " . $userinfo_response);
                    $error = "Invalid user information from Google. Please try again.";
                } else {
                    // Extract user information
                    $email = $google_user['email'];
                    $name = $google_user['name'] ?? $google_user['given_name'] . ' ' . $google_user['family_name'];
                    $google_id = $google_user['sub'];
                    $profile_picture = $google_user['picture'] ?? null;

                    // Check if the user already exists in the database
                    $stmt = $con->prepare("SELECT * FROM users WHERE email = ? OR google_id = ?");
                    $stmt->bind_param("ss", $email, $google_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    // User found - update and log in
                    if ($result->num_rows > 0) {
                        $user = $result->fetch_assoc();
                        
                        // Update Google ID and profile picture if not set
                        if (empty($user['google_id']) || $user['profile_picture'] != $profile_picture) {
                            $update_stmt = $con->prepare("UPDATE users SET google_id = ?, profile_picture = ?, auth_provider = 'google' WHERE email = ?");
                            $update_stmt->bind_param("sss", $google_id, $profile_picture, $email);
                            $update_stmt->execute();
                        }
                        
                        // Set session variables
                        $_SESSION['status'] = "loggedin";
                        $_SESSION['email'] = $email;
                        $_SESSION['name'] = $name;
                        $_SESSION['profile_picture'] = $profile_picture;
                        
                        // Set a message for returning users coming from registration page
                        if (isset($is_registration) && $is_registration) {
                            $_SESSION['message'] = "Welcome back! You've logged in with your existing Google account.";
                        }
                        
                        // Redirect to index.php regardless of any redirect session
                        header("Location: index.php");
                        exit();
                    } 
                    // User not found - register new user
                    else {
                        // Generate a random 10-digit phone number for contact field (as it's required)
                        $random_contact = mt_rand(1000000000, 9999999999);
                        
                        // For Google users, we don't need a password since they authenticate through Google
                        // Generate a random password hash that won't be used for login
                        $dummy_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
                        
                        // Create the new user
                        $insert_stmt = $con->prepare("INSERT INTO users (email, name, google_id, profile_picture, contact, auth_provider, password) VALUES (?, ?, ?, ?, ?, 'google', ?)");
                        $insert_stmt->bind_param("ssssss", $email, $name, $google_id, $profile_picture, $random_contact, $dummy_password);
                        
                        if ($insert_stmt->execute()) {
                            // Set session variables
                            $_SESSION['status'] = "loggedin";
                            $_SESSION['email'] = $email;
                            $_SESSION['name'] = $name;
                            $_SESSION['profile_picture'] = $profile_picture;
                            
                            // Set a welcome message for new users
                            if (isset($is_registration) && $is_registration) {
                                $_SESSION['message'] = "Your account has been created successfully with Google!";
                            }
                            
                            // Redirect to index.php regardless of any redirect session
                            header("Location: index.php");
                            exit();
                        } else {
                            // Error registering user
                            error_log("Error registering Google user: " . $insert_stmt->error);
                            $error = "Error registering with Google. Please try again.";
                        }
                    }
                }
            }
        }
    }
}

// Handle Google authentication errors
if (isset($_GET['error'])) {
    $error_type = $_GET['error'];
    switch ($error_type) {
        case 'google_auth_failed':
            $error = "Google authentication failed. Please try again.";
            break;
        case 'google_token_request_failed':
            $error = "Error obtaining Google authorization. Please try again.";
            break;
        case 'invalid_token_response':
            $error = "Invalid response from Google. Please try again.";
            break;
        case 'google_userinfo_request_failed':
            $error = "Error retrieving user information from Google. Please try again.";
            break;
        case 'invalid_userinfo_response':
            $error = "Invalid user information from Google. Please try again.";
            break;
        case 'google_register_failed':
            $error = "Error registering with Google. Please try again.";
            break;
        case 'login_required':
            $error = "Please log in to access this page.";
            break;
        default:
            $error = "An error occurred. Please try again.";
    }
}

// Create Google auth URL for sign-in button
$google_auth_params = [
    'client_id' => $google_client_id,
    'redirect_uri' => $google_redirect_url,
    'response_type' => 'code',
    'scope' => implode(' ', $google_scopes),
    'access_type' => 'online',
    'prompt' => 'select_account'
];

$google_auth_link = $google_auth_url . '?' . http_build_query($google_auth_params);

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
        
        /* New styles for Google login */
        .google-login-button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            background: #ffffff;
            color: #757575;
            font-size: 1rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }
        
        .google-login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .google-login-button img {
            height: 18px;
            margin-right: 10px;
        }
        
        .login-divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: var(--text-muted);
        }
        
        .login-divider::before,
        .login-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .login-divider span {
            padding: 0 1rem;
            font-size: 0.9rem;
        }
        
        .error-message {
            background: rgba(220, 38, 38, 0.1);
            color: #ef4444;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            border: 1px solid rgba(220, 38, 38, 0.2);
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
                
                <div class="login-divider">
                    <span>OR</span>
                </div>
                
                <a href="<?php echo htmlspecialchars($google_auth_link); ?>" class="google-login-button">
                    <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google">
                    Sign in with Google
                </a>
                
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