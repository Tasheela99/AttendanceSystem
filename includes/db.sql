-- Create Database (if not exists)
CREATE DATABASE IF NOT EXISTS attendance_db;
USE attendance_db;

-- Users Table (Students/Admins/Teachers) with password field
CREATE TABLE users (
                       id INT PRIMARY KEY AUTO_INCREMENT,
                       name VARCHAR(255) NOT NULL,
                       role ENUM('student', 'teacher', 'admin') NOT NULL,
                       fingerprint_template TEXT NULL, -- AES-encrypted data
                       email VARCHAR(255) UNIQUE,
                       password VARCHAR(255) NOT NULL, -- Added password field
                       fcm_token VARCHAR(255) NULL,    -- Added field to store FCM tokens
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Attendance Records
CREATE TABLE attendance (
                            id INT PRIMARY KEY AUTO_INCREMENT,
                            user_id INT NOT NULL,
                            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                            status ENUM('present', 'absent', 'late') NOT NULL,
                            FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Notifications (Firebase logs)
CREATE TABLE notifications (
                               id INT PRIMARY KEY AUTO_INCREMENT,
                               user_id INT NOT NULL,
                               message TEXT NOT NULL,
                               timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                               FOREIGN KEY (user_id) REFERENCES users(id)
);