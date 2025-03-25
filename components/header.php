<?php
function getPageTitle() {
    $currentPage = basename($_SERVER['PHP_SELF']);
    switch ($currentPage) {
        case 'index.php':
            return 'Dashboard';
        case 'login.php':
            return 'Login';
        case 'register.php':
            return 'Register';
        case 'landing.php':
            return 'Welcome';
        case 'takequiz.php':
            return 'Take Quiz';
        case 'results.php':
            return 'Results';
        case 'quizmanip.php':
            return 'Edit Quiz';
        case 'createquiz.php':
            return 'Create Quiz';
        default:
            return 'QuizMaster';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuizMaster - <?php echo getPageTitle(); ?></title>
    <link rel="shortcut icon" href="./quiz.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/Quiz/css/responsive.css">
    <style>
        /* Base Styles */
        :root {
            --primary: #4a90e2;
            --primary-dark: #357abd;
            --secondary: #ec4899;
            --accent: #8b5cf6;
            --background: #1a1a2e;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --success: #22c55e;
            --error: #ef4444;
            --header-height: 4.5rem;
            --admin-gradient: linear-gradient(135deg, #4a90e2, #8b5cf6);
            --admin-glow: 0 0 15px rgba(74, 144, 226, 0.5);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: var(--background);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .gradient-bg {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 0% 0%, rgba(74, 144, 226, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 100% 0%, rgba(236, 72, 153, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(139, 92, 246, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 0% 100%, rgba(34, 197, 94, 0.15) 0%, transparent 50%);
            z-index: -1;
        }

        .main-content {
            flex: 1;
            width: 100%;
            max-width: 1200px;
            margin: var(--header-height) auto 0;
            padding: 2rem;
        }

        /* Navbar Styles */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: auto;
            min-height: var(--header-height);
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 1rem;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        /* Admin Navbar */
        .navbar.admin-navbar {
            background: linear-gradient(to right, rgba(15, 23, 42, 0.95), rgba(26, 32, 63, 0.95));
            border-bottom: 1px solid rgba(74, 144, 226, 0.3);
            box-shadow: 0 4px 20px rgba(74, 144, 226, 0.2);
        }

        /* Brand Logo */
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            transition: all 0.3s ease;
        }

        .navbar-brand i {
            color: var(--primary);
            font-size: 1.75rem;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover i {
            transform: rotate(15deg) scale(1.1);
        }

        .admin-navbar .navbar-brand i {
            color: transparent;
            background: linear-gradient(135deg, #4a90e2, #8b5cf6);
            -webkit-background-clip: text;
            background-clip: text;
            animation: colorPulse 3s infinite alternate;
        }

        /* Navigation Links Container */
        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            overflow-x: auto;
            padding: 0.5rem;
            scrollbar-width: none;
            -ms-overflow-style: none;
            margin-left: 1rem;
            flex-wrap: nowrap;
        }

        .navbar-nav::-webkit-scrollbar {
            display: none;
        }

        /* Navigation Link */
        .nav-item {
            position: relative;
            flex-shrink: 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-muted);
            background: rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            white-space: nowrap;
            min-width: max-content;
        }

        .nav-link i {
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        .nav-link span {
            display: inline !important;
        }

        .nav-link:hover {
            color: var(--text);
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .nav-link:hover i {
            transform: scale(1.2);
        }

        .nav-link.active {
            color: var(--text);
            background: rgba(74, 144, 226, 0.2);
            border: 1px solid rgba(74, 144, 226, 0.3);
        }

        /* Admin Navigation Link */
        .admin-nav-link {
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .admin-nav-link i {
            color: var(--primary);
        }

        .admin-nav-link:hover {
            box-shadow: var(--admin-glow);
            transform: translateY(-3px);
            background: rgba(74, 144, 226, 0.15);
        }

        .admin-nav-link.active {
            background: var(--admin-gradient);
            color: white;
            box-shadow: var(--admin-glow);
            border: none;
        }

        .admin-nav-link.active i {
            color: white;
        }

        /* Shine Effect */
        .admin-nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: all 0.6s ease;
        }

        .admin-nav-link:hover::before {
            left: 100%;
        }

        /* Admin Badge */
        .admin-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--admin-gradient);
            color: white;
            font-size: 0.6rem;
            padding: 0.1rem 0.3rem;
            border-radius: 4px;
            font-weight: bold;
            opacity: 0;
            transform: scale(0);
            transition: all 0.3s ease;
        }

        .admin-nav-link:hover .admin-badge {
            opacity: 1;
            transform: scale(1);
        }

        /* Animations */
        @keyframes colorPulse {
            0% { filter: hue-rotate(0deg); }
            100% { filter: hue-rotate(30deg); }
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .navbar {
                padding: 0.5rem;
            }

            .navbar-brand {
                font-size: 1.2rem;
                min-width: max-content;
                margin-right: 0.5rem;
            }

            .navbar-brand span {
                display: inline !important;
            }

            .navbar-nav {
                flex: 1;
                justify-content: flex-start;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                padding: 0.25rem;
                gap: 0.5rem;
            }

            .nav-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.85rem;
            }

            .nav-link i {
                margin-right: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .navbar {
                flex-wrap: nowrap;
                justify-content: space-between;
            }

            .navbar-brand {
                font-size: 1.1rem;
            }

            .navbar-brand span {
                display: inline !important;
            }

            .nav-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
                min-width: max-content;
            }

            .nav-link span {
                display: inline !important;
            }

            .nav-link i {
                margin-right: 0.5rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="gradient-bg"></div>
    <nav class="navbar <?php echo (isset($_SESSION['status']) && $_SESSION['status'] === 'admin') ? 'admin-navbar' : ''; ?>">
        <a href="./index.php" class="navbar-brand">
            <i class="fas fa-brain"></i>
            <span>QuizMaster</span>
        </a>
        <div class="navbar-nav">
            <?php
                $currentPage = basename($_SERVER['PHP_SELF']);
                $isTakingQuiz = in_array($currentPage, ['takequiz.php', 'results.php']);
            ?>
            <?php if(isset($_SESSION['status']) && $_SESSION['status'] !== 'loggedout'): ?>
                <?php if(!$isTakingQuiz): ?>
                    <div class="nav-item">
                        <a href="./index.php" class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?> <?php echo ($_SESSION['status'] === 'admin') ? 'admin-nav-link' : ''; ?>">
                            <i class="fas fa-home"></i><span>Home</span>
                        </a>
                    </div>
                    <?php if($_SESSION['status'] === 'admin'): ?>
                        <div class="nav-item">
                            <a href="./createquiz.php" class="nav-link admin-nav-link <?php echo $currentPage === 'createquiz.php' ? 'active' : ''; ?>">
                                <i class="fas fa-plus-circle"></i><span>Create</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="./schedule_quiz.php" class="nav-link admin-nav-link <?php echo $currentPage === 'schedule_quiz.php' ? 'active' : ''; ?>">
                                <i class="fas fa-clock"></i><span>Schedule</span>
                                <div class="admin-badge">Admin</div>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="./quiz_statistics.php" class="nav-link admin-nav-link <?php echo $currentPage === 'quiz_statistics.php' ? 'active' : ''; ?>">
                                <i class="fas fa-chart-bar"></i><span>Statistics</span>
                                <div class="admin-badge">Admin</div>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="./quizform.php" class="nav-link admin-nav-link <?php echo $currentPage === 'quizform.php' ? 'active' : ''; ?>">
                                <i class="fas fa-list-ol"></i><span>Insert</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="./quizmanip.php" class="nav-link admin-nav-link <?php echo $currentPage === 'quizmanip.php' ? 'active' : ''; ?>">
                                <i class="fas fa-edit"></i><span>Edit</span>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="nav-item">
                        <a href="./index.php" class="nav-link <?php echo ($_SESSION['status'] === 'admin') ? 'admin-nav-link' : ''; ?>">
                            <i class="fas fa-home"></i><span>Home</span>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="nav-item">
                    <a href="./logout.php" class="nav-link <?php echo ($_SESSION['status'] === 'admin') ? 'admin-nav-link' : ''; ?>">
                        <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                    </a>
                </div>
                <?php if($_SESSION['status'] === 'loggedin'): ?>
                <div class="nav-item">
                    <a href="./access_quiz.php" class="nav-link">
                        <i class="fas fa-link"></i><span>Access Quiz</span>
                    </a>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Not logged in -->
                <?php if($currentPage === 'landing.php'): ?>
                    <div class="nav-item">
                        <a href="./login.php" class="nav-link">
                            <i class="fas fa-sign-in-alt"></i><span>Login</span>
                        </a>
                    </div>
                <?php elseif($currentPage === 'login.php'): ?>
                    <div class="nav-item">
                        <a href="./register.php" class="nav-link">
                            <i class="fas fa-user-plus"></i><span>Register</span>
                        </a>
                    </div>
                <?php elseif($currentPage === 'register.php'): ?>
                    <div class="nav-item">
                        <a href="./login.php" class="nav-link">
                            <i class="fas fa-sign-in-alt"></i><span>Login</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="nav-item">
                        <a href="./login.php" class="nav-link <?php echo $currentPage === 'login.php' ? 'active' : ''; ?>">
                            <i class="fas fa-sign-in-alt"></i><span>Login</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="./register.php" class="nav-link <?php echo $currentPage === 'register.php' ? 'active' : ''; ?>">
                            <i class="fas fa-user-plus"></i><span>Register</span>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </nav>
    <main class="main-content <?php echo basename($_SERVER['PHP_SELF']) === 'landing.php' ? 'landing-page' : ''; ?>">
