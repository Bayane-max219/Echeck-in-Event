-- Echeck-in Event Database Schema
-- MySQL 8.0.31

CREATE DATABASE IF NOT EXISTS echeck_in CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE echeck_in;

-- Users table
CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) NOT NULL UNIQUE,
    roles JSON NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- Events table
CREATE TABLE event (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organizer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATETIME NOT NULL,
    end_date DATETIME,
    location VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES user(id) ON DELETE CASCADE,
    INDEX idx_organizer (organizer_id),
    INDEX idx_start_date (start_date),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Participants table
CREATE TABLE participant (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL,
    phone VARCHAR(20),
    company VARCHAR(100),
    position VARCHAR(100),
    qr_code VARCHAR(255) NOT NULL UNIQUE,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES event(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participant_event (event_id, email),
    INDEX idx_event (event_id),
    INDEX idx_email (email),
    INDEX idx_qr_code (qr_code),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Invitations table
CREATE TABLE invitation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    participant_id INT NOT NULL,
    status VARCHAR(50) DEFAULT 'sent',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed_at TIMESTAMP NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    FOREIGN KEY (event_id) REFERENCES event(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES participant(id) ON DELETE CASCADE,
    INDEX idx_event (event_id),
    INDEX idx_participant (participant_id),
    INDEX idx_token (token),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Check-ins table
CREATE TABLE check_in (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    participant_id INT NOT NULL,
    checked_in_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    checked_in_by VARCHAR(100),
    notes VARCHAR(255),
    FOREIGN KEY (event_id) REFERENCES event(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES participant(id) ON DELETE CASCADE,
    INDEX idx_event (event_id),
    INDEX idx_participant (participant_id),
    INDEX idx_checked_in_at (checked_in_at)
) ENGINE=InnoDB;

-- Create indexes for better performance
CREATE INDEX idx_user_created_at ON user(created_at);
CREATE INDEX idx_event_created_at ON event(created_at);
CREATE INDEX idx_participant_created_at ON participant(created_at);
CREATE INDEX idx_invitation_sent_at ON invitation(sent_at);

-- Create views for common queries
CREATE VIEW event_statistics AS
SELECT 
    e.id as event_id,
    e.title,
    e.organizer_id,
    COUNT(DISTINCT p.id) as total_participants,
    COUNT(DISTINCT c.id) as checked_in_count,
    CASE 
        WHEN COUNT(DISTINCT p.id) > 0 
        THEN ROUND((COUNT(DISTINCT c.id) / COUNT(DISTINCT p.id)) * 100, 2)
        ELSE 0 
    END as attendance_rate
FROM event e
LEFT JOIN participant p ON e.id = p.event_id
LEFT JOIN check_in c ON p.id = c.participant_id
GROUP BY e.id, e.title, e.organizer_id;

CREATE VIEW participant_status_summary AS
SELECT 
    e.id as event_id,
    e.title as event_title,
    p.status,
    COUNT(*) as count
FROM event e
JOIN participant p ON e.id = p.event_id
GROUP BY e.id, e.title, p.status;

-- Insert default admin user (password: admin123)
INSERT INTO user (email, roles, password, first_name, last_name) VALUES 
('admin@echeck-in.com', '["ROLE_ADMIN"]', '$2y$13$QQQQQQQQQQQQQQQQQQQQQOeH6vyyCLZ9PGA6XvzeRcAXTR2hdSM.', 'Admin', 'User');