    </main>
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3 class="footer-title">QuizMaster</h3>
                    <p class="footer-description">
                        Empowering learning through interactive quizzes. Test your knowledge, track your progress, and grow with us.
                    </p>
                </div>
                <div class="footer-section">
                    <h3 class="footer-title">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="/Quiz/">Home</a></li>
                        <?php if(!isset($_SESSION['status'])): ?>
                            <li><a href="/Quiz/login.php">Login</a></li>
                            <li><a href="/Quiz/register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3 class="footer-title">Developers</h3>
                    <ul class="footer-links">
                        <li><a href="https://www.linkedin.com/in/vishalshetty17" target="_blank"><i class="fab fa-linkedin"></i> Vishal - Lead Backend Developer</a></li>
                        <li><a href="https://www.linkedin.com/in/aneesh-bhat" target="_blank"><i class="fab fa-linkedin"></i> Aneesh - PHP Developer</a></li>
                        <li><a href="https://www.linkedin.com/in/Chirag-S-Kotian" target="_blank"><i class="fab fa-linkedin"></i> Chirag - Frontend Developer</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> QuizMaster. All rights reserved.</p>
                <div class="social-links">
                    <a href="https://github.com/quiz-developers/quiz-master" class="social-link" target="_blank"><i class="fab fa-github"></i></a>
                    <a href="https://linkedin.com/company/quiz-master" class="social-link" target="_blank"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
    </footer>
    <style>
        .main-footer {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 3rem 0 1.5rem;
            margin-top: auto;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 2rem;
        }

        .footer-section {
            color: var(--text-muted);
        }

        .footer-title {
            color: var(--text);
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .footer-description {
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        .footer-bottom {
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-link {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 1.2rem;
        }

        .social-link:hover {
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .footer-content {
                gap: 2rem;
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
        }
    </style>
</body>
</html>
