<?php

define('DB_HOST', 'localhost'); 
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 's22101184_craftsy');

function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            )
        );
        return $conn;
    } catch(PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        die("Connection failed. Please try again later.");
    }
}


function initializeDatabase() {
    try {
        $conn = getDBConnection();
        

        $conn->exec("CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(100) UNIQUE NOT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('customer', 'admin') DEFAULT 'customer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        

        return true;
    } catch (PDOException $e) {
        error_log("Database initialization error: " . $e->getMessage());
        return false;
    }
}