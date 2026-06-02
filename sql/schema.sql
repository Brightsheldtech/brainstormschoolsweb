-- Brainstorm School Database Schema
-- Run this in your Hostinger MySQL panel

CREATE DATABASE IF NOT EXISTS brainstorm_school CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE brainstorm_school;

-- Users (Admin & Teachers)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher') DEFAULT 'teacher',
    phone VARCHAR(20),
    subject VARCHAR(100),
    profile_photo VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Classes
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    section VARCHAR(10),
    teacher_id INT,
    academic_year VARCHAR(20) DEFAULT '2024/2025',
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Students
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    class_id INT,
    date_of_birth DATE,
    gender ENUM('male', 'female') NOT NULL,
    parent_name VARCHAR(100),
    parent_phone VARCHAR(20),
    parent_email VARCHAR(100),
    parent_whatsapp VARCHAR(20),
    address TEXT,
    photo VARCHAR(255),
    status ENUM('active', 'graduated', 'transferred') DEFAULT 'active',
    admission_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
);

-- Subjects
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20),
    class_id INT,
    teacher_id INT,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Results
CREATE TABLE results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    class_id INT NOT NULL,
    term ENUM('First Term', 'Second Term', 'Third Term') NOT NULL,
    academic_year VARCHAR(20) DEFAULT '2024/2025',
    ca1 DECIMAL(5,2) DEFAULT 0,
    ca2 DECIMAL(5,2) DEFAULT 0,
    exam DECIMAL(5,2) DEFAULT 0,
    total DECIMAL(5,2) GENERATED ALWAYS AS (ca1 + ca2 + exam) STORED,
    grade VARCHAR(5),
    remark VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_result (student_id, subject_id, term, academic_year),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

-- Attendance
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL,
    marked_by INT,
    note TEXT,
    UNIQUE KEY unique_attendance (student_id, date),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Admission Applications
CREATE TABLE admissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_no VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female') NOT NULL,
    class_applying VARCHAR(50) NOT NULL,
    previous_school VARCHAR(100),
    parent_name VARCHAR(100) NOT NULL,
    parent_phone VARCHAR(20) NOT NULL,
    parent_email VARCHAR(100),
    parent_whatsapp VARCHAR(20),
    address TEXT,
    how_heard VARCHAR(100),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- News
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    content LONGTEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(255),
    category VARCHAR(50) DEFAULT 'News',
    author_id INT,
    is_published TINYINT(1) DEFAULT 0,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Events
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME,
    end_date DATE,
    venue VARCHAR(200),
    featured_image VARCHAR(255),
    is_published TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Gallery
CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    category VARCHAR(50) DEFAULT 'General',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contact Messages
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed: Default admin account (password: password — CHANGE THIS IMMEDIATELY after first login)
INSERT INTO users (full_name, email, password, role) VALUES
('Administrator', 'admin@brainstormschool.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Seed: Sample classes
INSERT INTO classes (name, section, academic_year) VALUES
('JSS 1', 'A', '2024/2025'),
('JSS 2', 'A', '2024/2025'),
('JSS 3', 'A', '2024/2025'),
('Primary 1', 'A', '2024/2025'),
('Primary 2', 'A', '2024/2025'),
('Primary 3', 'A', '2024/2025'),
('Primary 4', 'A', '2024/2025'),
('Primary 5', 'A', '2024/2025'),
('Primary 6', 'A', '2024/2025');
