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
