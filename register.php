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
<?php include "components/header.php"; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .register-container {
            width: 100%;
            max-width: 600px;
            margin: 2rem auto;
            padding: 0 1rem;
            animation: fadeIn 0.5s ease;
        }

        .register-section {
            background: var(--quiz-card-bg);
            padding: 2.5rem;
            border-radius: 15px;
            border: 1px solid var(--quiz-card-border);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .register-section::before {
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

        .register-section > * {
            position: relative;
            z-index: 1;
        }

        .section-title {
            font-size: 2rem;
            margin-bottom: 1rem;
            text-align: center;
            background: linear-gradient(135deg, #fff, var(--primary));
            -webkit-background-clip: text;
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

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }

        .error, .password-match-error {
            color: #ef4444;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            display: none;
            padding: 0.5rem;
            border-radius: 4px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        input:invalid + .error {
            display: block;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .password-requirements {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 0.5rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
        }

        .password-requirements ul {
            margin: 0.5rem 0 0 1.5rem;
            padding: 0;
        }

        .password-requirements li {
            margin-bottom: 0.25rem;
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
            margin-top: 1rem;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.3);
        }

        .password-container {
            position: relative;
            width: 100%;
        }

        .password-container input {
            width: 100%;
            padding-right: 40px;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }

        .toggle-password:hover {
            color: #333;
        }

        .separator {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        .separator::before,
        .separator::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--quiz-card-border);
        }
        
        .separator span {
            padding: 0 1rem;
        }
        
        .social-login {
            margin-top: 0.5rem;
        }
        
        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.75rem 1.5rem;
            background: #ffffff;
            color: #333333;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }
        
        .google-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .google-btn i {
            margin-right: 0.75rem;
            font-size: 1.2rem;
            color: #4285F4;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-section">
            <h2 class="section-title">Create Account</h2>
            <p class="section-description">Join our quiz platform and start testing your knowledge</p>
            <form action="./register.php" id="regform" method="post">
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
            <div class="password-container">
                <input type="password" name="password" id="password" 
                       pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$" 
                       placeholder="Enter your password" required>
                <i class="toggle-password fas fa-eye" onclick="togglePassword('password')"></i>
            </div>
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
            <div class="password-container">
                <input type="password" id="conf" placeholder="Confirm your password" required>
                <i class="toggle-password fas fa-eye" onclick="togglePassword('conf')"></i>
            </div>
            <div class="password-match-error">Passwords do not match</div>
        </div>

        <button type="submit">Create Account</button>
            </form>

            <div class="separator">
                <span>OR</span>
            </div>
            
            <div class="social-login">
                <a href="<?php
                // Include Google config if not already included
                if (!function_exists('isset_google_config')) {
                    require_once "google_config.php";
                }
                
                // Generate the Google auth URL
                $auth_params = [
                    'client_id' => $google_client_id,
                    'redirect_uri' => $google_redirect_url,
                    'response_type' => 'code',
                    'scope' => implode(' ', $google_scopes),
                    'access_type' => 'online',
                    'state' => 'register',  // Add state parameter to indicate this is from registration
                    'prompt' => 'select_account'
                ];
                
                echo $google_auth_url . '?' . http_build_query($auth_params);
                ?>" class="google-btn">
                    <i class="fab fa-google"></i> Continue with Google
                </a>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById("regform");
        const password = document.getElementById("password");
        const confirm = document.getElementById("conf");
        const matchError = document.querySelector(".password-match-error");
        const inputs = form.querySelectorAll('input');

        function validatePasswords() {
            if (password.value !== confirm.value) {
                matchError.style.display = "block";
                confirm.setCustomValidity("Passwords do not match");
            } else {
                matchError.style.display = "none";
                confirm.setCustomValidity("");
            }
        }

        // Add validation feedback for all inputs
        inputs.forEach(input => {
            input.addEventListener('invalid', () => {
                const error = input.nextElementSibling;
                if (error && error.classList.contains('error')) {
                    error.style.display = 'block';
                }
            });

            input.addEventListener('input', () => {
                const error = input.nextElementSibling;
                if (error && error.classList.contains('error')) {
                    if (input.validity.valid) {
                        error.style.display = 'none';
                    }
                }
            });
        });

        password.addEventListener("input", validatePasswords);
        confirm.addEventListener("input", validatePasswords);

        form.addEventListener("submit", (e) => {
            e.preventDefault();
            validatePasswords();
            
            // Check form validity and show all errors
            if (!form.checkValidity()) {
                inputs.forEach(input => {
                    if (!input.validity.valid) {
                        const error = input.nextElementSibling;
                        if (error && error.classList.contains('error')) {
                            error.style.display = 'block';
                        }
                    }
                });
            }
            
            if (form.checkValidity() && password.value === confirm.value) {
                form.submit();
            }
        });

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling;
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
<?php include "components/footer.php"; ?>