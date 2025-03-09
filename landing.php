<?php
include "dbconnect.php";

if(isset($_SESSION['status']) && ($_SESSION['status'] === "loggedin" || $_SESSION['status'] === "admin")) {
    header("Location: index.php");
    exit();
}

include "components/header.php";
?>
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #ec4899;
            --accent: #8b5cf6;
            --background: #0f172a;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --success: #22c55e;
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
            overflow-x: hidden;
        }

        .gradient-bg {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 0% 0%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 100% 0%, rgba(236, 72, 153, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(139, 92, 246, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 0% 100%, rgba(34, 197, 94, 0.15) 0%, transparent 50%);
            z-index: -1;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }



        .hero {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            padding: 6rem 0 4rem;
            align-items: center;
            min-height: calc(100vh - 300px);
        }

        .hero-content h1 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            line-height: 1.2;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--text), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-content p {
            font-size: 1.2rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
        }

        .cta-btn {
            padding: 1rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .primary-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--text);
        }

        .secondary-btn {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text);
        }

        .cta-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .hero-image {
            position: relative;
        }

        .hero-image img {
            width: 100%;
            height: auto;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .features {
            padding: 4rem 0;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            background: linear-gradient(135deg, var(--text), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .feature-title {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: var(--text);
        }

        .feature-description {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .hero {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 2rem;
            }

            .cta-buttons {
                justify-content: center;
            }

            .nav-links {
                gap: 1rem;
            }

            .nav-btn {
                padding: 0.5rem 1rem;
            }
        }

        @media (max-width: 480px) {
            .cta-buttons {
                flex-direction: column;
            }

            .cta-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="gradient-bg"></div>
    <div class="container">


        <main>
            <section class="hero">
                <div class="hero-content">
                    <h1>Master Your Knowledge with Interactive Quizzes</h1>
                    <p>Challenge yourself, learn new topics, and track your progress with our engaging quiz platform. Perfect for students, professionals, and lifelong learners.</p>
                    <div class="cta-buttons">
                        <a href="register.php" class="cta-btn primary-btn">
                            <i class="fas fa-user-plus"></i>
                            Get Started
                        </a>
                        <a href="login.php" class="cta-btn secondary-btn">
                            <i class="fas fa-sign-in-alt"></i>
                            Sign In
                        </a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1740&q=80" 
                         alt="Quiz Platform Interface">
                </div>
            </section>

            <section class="features">
                <h2 class="section-title">Why Choose QuizMaster?</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <i class="fas fa-bolt feature-icon"></i>
                        <h3 class="feature-title">Interactive Learning</h3>
                        <p class="feature-description">Engage with dynamic quizzes that make learning fun and effective</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-chart-line feature-icon"></i>
                        <h3 class="feature-title">Track Progress</h3>
                        <p class="feature-description">Monitor your performance and see your improvement over time</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-graduation-cap feature-icon"></i>
                        <h3 class="feature-title">Diverse Topics</h3>
                        <p class="feature-description">Explore a wide range of subjects and challenge your knowledge</p>
                    </div>
                </div>
            </section>
        </main>
    </div>
<?php include "components/footer.php"; ?>
