-- Database initialization for Attendance Management System
-- MariaDB/MySQL

CREATE DATABASE IF NOT EXISTS attendance_system;
USE attendance_system;

-- Users table (students, professors, administrators)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'professor', 'admin') NOT NULL,
    student_id VARCHAR(50) UNIQUE NULL, -- Only for students
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Courses table
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    professor_id INT NOT NULL,
    FOREIGN KEY (professor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Groups table (per course)
CREATE TABLE groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    course_id INT NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Students-Groups association
CREATE TABLE students_groups (
    student_id INT NOT NULL,
    group_id INT NOT NULL,
    PRIMARY KEY (student_id, group_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
);

-- Attendance Sessions
CREATE TABLE attendance_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    group_id INT NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('open', 'closed') DEFAULT 'open',
    created_by INT NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Attendance Records
CREATE TABLE attendance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('present', 'absent') NOT NULL,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES attendance_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_record (session_id, student_id)
);

-- Justifications
CREATE TABLE justifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    session_id INT NOT NULL,
    reason TEXT NOT NULL,
    file_path VARCHAR(255) NULL, -- Path to uploaded file
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    reviewed_by INT NULL,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES attendance_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);

-- Insert sample data
-- Admins
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@univ-algiers.dz', '$2y$10$examplehashedpassword', 'admin');

-- Professors
INSERT INTO users (name, email, password, role) VALUES
('Prof. Ahmed', 'ahmed@univ-algiers.dz', '$2y$10$examplehashedpassword', 'professor'),
('Prof. Fatima', 'fatima@univ-algiers.dz', '$2y$10$examplehashedpassword', 'professor');

-- Students
INSERT INTO users (name, email, password, role, student_id) VALUES
('Student1', 'student1@univ-algiers.dz', '$2y$10$examplehashedpassword', 'student', '123456'),
('Student2', 'student2@univ-algiers.dz', '$2y$10$examplehashedpassword', 'student', '123457');

-- Courses
INSERT INTO courses (name, professor_id) VALUES
('Mathematics', 2),
('Physics', 3);

-- Groups
INSERT INTO groups (name, course_id) VALUES
('Group A', 1),
('Group B', 1),
('Group A', 2);

-- Students in groups
INSERT INTO students_groups (student_id, group_id) VALUES
(4, 1),
(5, 1),
(4, 3);
