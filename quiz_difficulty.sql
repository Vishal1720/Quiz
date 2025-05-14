-- Add difficulty level to quizes table
ALTER TABLE quizes ADD COLUMN difficulty_level ENUM('easy', 'medium', 'intermediate', 'hard') NOT NULL DEFAULT 'easy';

-- Create table for tracking user attempts
CREATE TABLE IF NOT EXISTS user_quiz_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255),
    quizid INT,
    question_id INT,
    difficulty_level ENUM('easy', 'medium', 'intermediate', 'hard'),
    attempt_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_email) REFERENCES users(email),
    FOREIGN KEY (quizid) REFERENCES quizdetails(quizid),
    FOREIGN KEY (question_id) REFERENCES quizes(ID)
);
