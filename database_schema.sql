CREATE DATABASE IF NOT EXISTS pwd_verification;
USE pwd_verification;

-- PWD User Table
CREATE TABLE PWD_User (
    PWD_ID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(100) NOT NULL,
    DateOfBirth DATE NOT NULL,
    Gender VARCHAR(8) NOT NULL,
    DisabilityType VARCHAR(50) NOT NULL,
    IssueDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ExpiryDate TIMESTAMP NULL,
    EncryptedKey INT,
    QR_Code VARCHAR(255),
    Status VARCHAR(50) DEFAULT 'Active'
);

-- Account Credentials
CREATE TABLE Account_Credentials (
    Account_ID INT AUTO_INCREMENT PRIMARY KEY,
    PWD_ID INT,
    Username VARCHAR(20) UNIQUE NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    AccountRole VARCHAR(20) DEFAULT 'User',
    LastLogin TIMESTAMP NULL,
    FOREIGN KEY (PWD_ID) REFERENCES PWD_User(PWD_ID)
);
