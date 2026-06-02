-- Parent Portal — run this in Hostinger MySQL panel after schema.sql

USE brainstorm_school;

-- Parent accounts (one parent can have multiple children)
CREATE TABLE IF NOT EXISTS parent_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(100),
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Links parents to their children (many-to-many)
CREATE TABLE IF NOT EXISTS parent_student_link (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    student_id INT NOT NULL,
    relationship VARCHAR(50) DEFAULT 'Parent',
    UNIQUE KEY unique_link (parent_id, student_id),
    FOREIGN KEY (parent_id) REFERENCES parent_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);
