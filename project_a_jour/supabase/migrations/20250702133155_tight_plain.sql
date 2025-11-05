-- Migration: Initial Schema
-- Created: 2024-01-01
-- Description: Create initial database schema for Echeck-in Event

-- Create users table
CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) NOT NULL UNIQUE,
    roles JSON NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Create events table
CREATE TABLE IF NOT EXISTS event (
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
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Create participants table
CREATE TABLE IF NOT EXISTS participant (
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
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Create invitations table
CREATE TABLE IF NOT EXISTS invitation (
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
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB;

-- Create check-ins table
CREATE TABLE IF NOT EXISTS check_in (
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