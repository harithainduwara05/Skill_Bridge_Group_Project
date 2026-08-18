-- Company Registration Database Schema
-- Run this SQL to set up the companies table

-- Create the companies table
CREATE TABLE IF NOT EXISTS companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_name VARCHAR(255) NOT NULL,
    business_email VARCHAR(255) NOT NULL UNIQUE,
    industry_sector VARCHAR(100) NOT NULL,
    contact_person VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    website VARCHAR(255),
    location VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    is_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (business_email),
    INDEX idx_status (status)
);

-- Optional: Add sample data for testing
-- INSERT INTO companies (company_name, business_email, industry_sector, contact_person, contact_number, website, location, password, status)
-- VALUES ('Test Company', 'hr@testcompany.com', 'IT & Software', 'John Doe', '+94771234567', 'www.testcompany.com', 'Colombo', '$2y$10$...hashedpassword...', 'pending');
