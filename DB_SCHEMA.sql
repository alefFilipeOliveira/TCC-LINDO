
-- UPAMED database schema
CREATE DATABASE IF NOT EXISTS upamed_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE upamed_db;
CREATE TABLE IF NOT EXISTS patients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  age INT,
  gender VARCHAR(50),
  weight FLOAT,
  height FLOAT,
  imc FLOAT,
  imc_desc VARCHAR(100),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  filename VARCHAR(255) NOT NULL,
  risk_level VARCHAR(20),
  summary TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);
