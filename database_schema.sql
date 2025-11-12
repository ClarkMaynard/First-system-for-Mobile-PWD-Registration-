-- database_schema.sql
CREATE DATABASE IF NOT EXISTS pwd_verification;
USE pwd_verification;

-- PWD User Table
CREATE TABLE IF NOT EXISTS PWD_User (
    PWD_ID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(100) NOT NULL,
    DateOfBirth DATE NOT NULL,
    Gender VARCHAR(8) NOT NULL,
    DisabilityType VARCHAR(50) NOT NULL,
    IssueDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ExpiryDate TIMESTAMP NULL,
    -- store path to uploaded ID image
    ID_Image VARCHAR(255) DEFAULT NULL,
    -- store path to generated QR image (optional)
    QR_Path VARCHAR(255) DEFAULT NULL,
    Status VARCHAR(50) DEFAULT 'Active'
);

-- Account Credentials
CREATE TABLE IF NOT EXISTS Account_Credentials (
    Account_ID INT AUTO_INCREMENT PRIMARY KEY,
    PWD_ID INT,
    Username VARCHAR(50) UNIQUE NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    AccountRole VARCHAR(20) DEFAULT 'User',
    LastLogin TIMESTAMP NULL,
    -- secure verification token encoded into QR (random)
    verification_token VARCHAR(128) NOT NULL,
    token_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (PWD_ID) REFERENCES PWD_User(PWD_ID) ON DELETE CASCADE
);

-- Recommended indexes / constraints (if not included already)
ALTER TABLE Account_Credentials
  ADD UNIQUE INDEX IF NOT EXISTS idx_username (Username(50)),
  ADD UNIQUE INDEX IF NOT EXISTS idx_token (verification_token(64));

-- Optional: enforce maximum username length and add token expiry column if desired
-- Note: some MySQL versions do not support IF NOT EXISTS in ALTER; run only if needed.

CREATE TABLE system_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  log_message VARCHAR(255) NOT NULL,
  logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO system_logs (log_message) VALUES ('User John logged in');
