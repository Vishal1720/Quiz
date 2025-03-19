# Quiz Management System

## Description
This is a comprehensive **Quiz Management System** built using **PHP** and **MySQL** with **XAMPP**. The application allows administrators to create, manage, and customize quizzes with timed assessments, while users can register, take quizzes, and view their results.

---

## Features

### User Features
- 🔐 User registration and authentication system
- 📝 Take quizzes with customizable time limits
- ⏱️ Real-time timer with visual warnings as time runs low
- 📊 View quiz results immediately after submission
- 🔍 Browse quizzes by category
- 📱 Responsive design for desktop and mobile devices

### Admin Features
- ✏️ Create new quizzes with custom names and categories
- ⏰ Set custom time limits for each quiz (0-180 minutes)
- ➕ Add questions with multiple-choice options
- 🔄 Edit existing quizzes and questions
- 🗑️ Delete quizzes and questions
- 📂 Manage quiz categories

### Technical Features
- 🔒 Secure password hashing
- 🛡️ Protection against SQL injection
- 📱 Responsive UI with modern design
- 🌐 Session management for user authentication
- 🔄 Real-time quiz timer with automatic submission

---

## Requirements
- **XAMPP** (includes PHP 7.4+, Apache, and MySQL)
- A modern web browser (Chrome, Firefox, Safari, Edge)
- PHP 7.4 or higher
- MySQL 5.7 or higher

---

## Setup Instructions

### Step 1: Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)
2. Install and start the **Apache** and **MySQL** services via the XAMPP Control Panel

### Step 2: Clone or Download the Project Files
- Place the project folder in the `htdocs` directory of your XAMPP installation:
  - Example path: `/opt/lampp/htdocs/Quiz` (Linux) or `C:/xampp/htdocs/Quiz` (Windows)

### Step 3: Set Up the Database
1. Open phpMyAdmin in your browser: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Create a new database named `quiz`
3. Import the `quiz.sql` file:
   - Navigate to the **Import** tab in phpMyAdmin
   - Upload the `quiz.sql` file provided in the project folder

### Step 4: Run the Project
1. Start the **Apache** and **MySQL** services from the XAMPP Control Panel
2. Open your web browser
3. Navigate to the following URL: http://localhost/Quiz
4. The landing page of the quiz application should load

### Step 5: Default Admin Access
- **Email**: admin123@gmail.com
- **Password**: admin123

---

## Usage Guide

### For Users
1. **Registration**: Create a new account using the registration form
2. **Login**: Access your account using your email and password
3. **Browse Quizzes**: View available quizzes categorized by subject
4. **Take a Quiz**: Click on any quiz to start the assessment
5. **Timer**: Pay attention to the timer at the top of the quiz page
6. **Submit**: Submit your answers before the timer runs out
7. **Results**: View your score and correct answers immediately

### For Administrators
1. **Login**: Access the admin panel using admin credentials
2. **Create Quiz**: Set up a new quiz with a name, category, and time limit
3. **Add Questions**: Create questions with multiple-choice options
4. **Edit Quiz**: Modify existing quizzes and questions
5. **Delete Quiz**: Remove quizzes that are no longer needed

---

## Technical Architecture

### Database Structure
The application uses a MySQL database with the following main tables:
- **users**: Stores user credentials and profile information
- **quizdetails**: Contains quiz metadata including name, category, and timer settings
- **quizes**: Stores questions, options, and correct answers
- **category**: Manages quiz categories

### Authentication System
- Secure login/registration system with password hashing
- Session-based authentication with timeout functionality
- Role-based access control (admin vs. regular users)

### Quiz Timer Implementation
- Custom timer settings (0-180 minutes) configurable per quiz
- Real-time JavaScript timer with automatic submission
- Visual warnings as time runs low (color changes and animations)
- Session-based timer persistence to prevent refreshing issues

### Responsive Design
- Mobile-first approach using custom CSS
- Adaptive layouts for different screen sizes
- Flexbox and CSS Grid for modern layouts
- Smooth animations and transitions for better UX

---

## Styling and UI Components

### CSS Architecture
- Modular CSS files organized by functionality:
  - **nav.css**: Navigation styling
  - **form.css**: Form elements styling
  - **enhanced-style.css**: Enhanced UI components
  - **responsive.css**: Media queries for responsiveness

### UI Features
- **Color Scheme**: Dark theme with blue accents for better readability
- **Card-Based Layout**: Modern card-based design for quizzes and questions
- **Custom Form Elements**: Styled inputs, buttons, and select elements
- **Interactive Elements**: Hover effects, focus states, and animations
- **Accessibility**: High contrast text and proper focus indicators

### Components
- **Header/Footer**: Reusable components across pages
- **Navigation**: Responsive navigation with active state indicators
- **Quiz Cards**: Interactive cards for quiz selection
- **Timer Display**: Dynamic timer with visual feedback
- **Question Cards**: Clean layout for questions and options

---

## File Structure
- **index.php**: Main dashboard showing available quizzes
- **login.php**: User authentication
- **register.php**: New user registration
- **createquiz.php**: Admin interface for creating quizzes
- **quizform.php**: Interface for adding questions to quizzes
- **quizmanip.php**: Admin interface for editing quizzes
- **takequiz.php**: Interface for users to take quizzes with timer
- **results.php**: Display quiz results
- **dbconnect.php**: Database connection and initialization
- **components/**: Header and footer components
- **css/**: Stylesheets for the application

---

## Developers

- **Vishal** - Lead Backend Developer
- **Aneesh** - PHP Developer
- **Chirag** - Frontend Developer

---

# Quiz Application

A web-based quiz application that allows users to create, take, and manage quizzes. Features include user authentication, Google OAuth integration, and quiz scheduling.

## Prerequisites

Before you begin, ensure you have the following installed:

1. PHP 8.2 or higher
2. MySQL 5.7 or higher
3. Composer (PHP package manager)
4. Web server (Apache/Nginx)

## Installation Steps for Windows

### 1. Install PHP

1. Download PHP for Windows from the official website:
   - Go to [https://windows.php.net/download/](https://windows.php.net/download/)
   - Download the latest PHP 8.2.x ZIP package (VS16 x64 Thread Safe)

2. Set up PHP:
   - Create a folder at `C:\php`
   - Extract the downloaded ZIP contents to `C:\php`
   - Copy `php.ini-development` to `php.ini`
   - Edit `php.ini` and uncomment these extensions:
     ```ini
     extension=curl
     extension=fileinfo
     extension=gd
     extension=mbstring
     extension=mysqli
     extension=openssl
     extension=pdo_mysql
     ```

3. Add PHP to System PATH:
   - Open System Properties (Win + Pause/Break)
   - Click "Advanced system settings"
   - Click "Environment Variables"
   - Under "System Variables", find and select "Path"
   - Click "Edit"
   - Click "New"
   - Add `C:\php`
   - Click "OK" on all windows

4. Verify PHP installation:
   - Open Command Prompt
   - Run: `php -v`
   - You should see PHP version information

### 2. Install Composer

1. Download Composer:
   - Go to [https://getcomposer.org/download/](https://getcomposer.org/download/)
   - Click "Composer-Setup.exe"

2. Run the installer:
   - Double-click the downloaded "Composer-Setup.exe"
   - If prompted by Windows Security, click "Run"
   - Follow the installation wizard
   - When asked for PHP path, select `C:\php\php.exe`

3. Verify Composer installation:
   - Open Command Prompt
   - Run: `composer --version`
   - You should see Composer version information

### 3. Install MySQL

1. Download MySQL:
   - Go to [https://dev.mysql.com/downloads/installer/](https://dev.mysql.com/downloads/installer/)
   - Download "MySQL Installer for Windows"

2. Run the installer:
   - Double-click the downloaded installer
   - Choose "Developer Default" or "Server only" installation
   - Follow the installation wizard
   - Set root password when prompted
   - Complete the installation

3. Verify MySQL installation:
   - Open Command Prompt
   - Run: `mysql --version`
   - You should see MySQL version information

### 4. Install XAMPP (Alternative)

If you prefer an all-in-one solution, you can install XAMPP instead:

1. Download XAMPP:
   - Go to [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html)
   - Download XAMPP with PHP 8.2

2. Run the installer:
   - Double-click the downloaded installer
   - Follow the installation wizard
   - Choose components (Apache, MySQL, PHP)

3. Start XAMPP:
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

### 5. Project Setup

1. Clone the repository:
   ```bash
   git clone [repository-url]
   cd Quiz
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Configure database:
   - Create a new MySQL database named 'quiz'
   - Import the database schema from `database.sql`

4. Configure Google OAuth:
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create a new project
   - Enable Google+ API
   - Create OAuth 2.0 credentials
   - Add authorized redirect URI: `http://localhost/Quiz/login.php`
   - Copy Client ID and Client Secret
   - Update `google_config.php` with your credentials

5. Configure web server:
   - If using XAMPP, place project files in `C:\xampp\htdocs\Quiz`
   - If using standalone Apache, configure virtual host

6. Set file permissions:
   - Ensure web server has write permissions to:
     - `uploads/` directory
     - `logs/` directory

### 6. Access the Application

1. Start your web server:
   - If using XAMPP: Start Apache and MySQL from XAMPP Control Panel
   - If using standalone: Start Apache and MySQL services

2. Open your browser:
   - Go to `http://localhost/Quiz`
   - Default admin credentials:
     - Username: admin
     - Password: admin123

## Troubleshooting

1. If PHP is not recognized:
   - Verify PHP is in system PATH
   - Restart Command Prompt

2. If Composer fails to install:
   - Ensure PHP is properly installed
   - Check PHP version compatibility
   - Verify SSL certificates

3. If MySQL connection fails:
   - Verify MySQL service is running
   - Check database credentials
   - Ensure MySQL port (3306) is not blocked

4. If Google OAuth fails:
   - Verify redirect URI matches exactly
   - Check API credentials
   - Ensure Google+ API is enabled

## Support

For additional support or questions, please open an issue in the repository.

---
