-- Create database
CREATE DATABASE IF NOT EXISTS medlog;
USE medlog;

-- Create appointments table
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_name VARCHAR(100) NOT NULL,
    appointment_date DATE NOT NULL,
    reason TEXT
);

-- Create prescriptions table
CREATE TABLE IF NOT EXISTS prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_name VARCHAR(255) NOT NULL,
    medicine VARCHAR(255) NOT NULL,
    dosage VARCHAR(255) NOT NULL
);

-- No dummy inserts here: real-time data will be inserted by the application
