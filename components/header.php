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
    <link rel="shortcut icon" href="/Quiz/quiz.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/Quiz/nav.css">
    <link rel="stylesheet" href="/Quiz/css/responsive.css">
    <style>
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
            --header-height: 4rem;
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

        @media (max-width: 768px) {
            :root {
                --header-height: 3.5rem;
            }
            
            .main-content {
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .nav-link span {
                display: none;
            }

            .nav-link i {
                font-size: 1.2rem;
                margin: 0;
            }

            .nav-link {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="gradient-bg"></div>
    <nav>
        <a href="/Quiz/index.php" class="nav-brand">
            <i class="fas fa-brain"></i>
            <span>QuizMaster</span>
        </a>
        <div class="nav-links">
            <?php
                $currentPage = basename($_SERVER['PHP_SELF']);
                $isTakingQuiz = in_array($currentPage, ['takequiz.php', 'results.php']);
            ?>
            <?php if(isset($_SESSION['status'])): ?>
                <?php if(!$isTakingQuiz): ?>
                    <a href="/Quiz/index.php" class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i><span>Home</span>
                    </a>
                    <?php if($_SESSION['status'] === 'admin'): ?>
                        <a href="/Quiz/createquiz.php" class="nav-link <?php echo $currentPage === 'createquiz.php' ? 'active' : ''; ?>">
                            <i class="fas fa-plus-circle"></i><span>Create</span>
                        </a>
                        <a href="/Quiz/quizmanip.php" class="nav-link <?php echo $currentPage === 'quizmanip.php' ? 'active' : ''; ?>">
                            <i class="fas fa-edit"></i><span>Edit</span>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/Quiz/index.php" class="nav-link">
                        <i class="fas fa-home"></i><span>Home</span>
                    </a>
                <?php endif; ?>
               <?php if($_SESSION['status'] === 'admin' || $_SESSION['status'] === 'loggedin') { ?>
                   
                <a href="/Quiz/logout.php" class="nav-link">

                    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                </a>
            <?php } else{ ?>
                <a href="/Quiz/login.php" class="nav-link <?php echo $currentPage === 'login.php' ? 'active' : ''; ?>">
                    <i class="fas fa-sign-in-alt"></i><span>Login</span>
                </a>
                <a href="/Quiz/register.php" class="nav-link <?php echo $currentPage === 'register.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-plus"></i><span>Register</span>
                </a>
            <?php } endif;?>
        </div>
    </nav>
    <main class="main-content <?php echo basename($_SERVER['PHP_SELF']) === 'landing.php' ? 'landing-page' : ''; ?>">
