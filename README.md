# Quiz Management System

## Description
This project is a **Quiz Management System** built using **XAMPP** with **MySQL**. It allows users to participate in quizzes, with questions and answers stored in a database.

---

## Features
- 🖥️ User-friendly quiz interface.  
- 📊 Database-driven questions and answers.  
- 🔧 Easy to set up and customize.  

---

## Requirements
- **XAMPP** (includes PHP, Apache, and MySQL).  
- A modern web browser (e.g., Chrome, Firefox).  

---

## Setup Instructions

### Step 1: Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org).
2. Install and start the **Apache** and **MySQL** services via the XAMPP Control Panel.

### Step 2: Clone or Download the Project Files
- Place the project folder in the `htdocs` directory of your XAMPP installation:
  - Example path: `C:/xampp/htdocs/quiz`.

### Step 3: Set Up the Database
1. Open phpMyAdmin in your browser: [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
2. Create a new database named `quiz`.
3. Import the `quiz.sql` file:
   - Navigate to the **Import** tab in phpMyAdmin.
   - Upload the `quiz.sql` file provided in the project folder.

### Step 4: Run the Project
1. Start the **Apache** and **MySQL** services from the XAMPP Control Panel.
2. Open your web browser.
3. Navigate to the following URL:  http://localhost/quiz (based on your file name)
4. The homepage of the quiz application should load.
