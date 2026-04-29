-- 2. Create the Users table
USE studyguide_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- 3. Create the Subjects table
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject_name VARCHAR(255) NOT NULL,
    difficulty VARCHAR(255)
);

-- 4. Create the Content table
CREATE TABLE IF NOT EXISTS study_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    question TEXT NOT NULL,
    answer TEXT NOT NULL
);

ALTER TABLE calendar_events 
ADD COLUMN title VARCHAR(255) NOT NULL AFTER user_id,
ADD COLUMN event_date DATE NOT NULL AFTER title,
ADD COLUMN category ENUM('project', 'quiz', 'other') DEFAULT 'other' AFTER event_date;