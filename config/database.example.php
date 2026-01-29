<?php
/**
 * EXAMPLE DATABASE CONFIGURATION
 * Copy this file to database.php and update with your credentials
 */

// Database Configuration for InfinityFree
// These are EXAMPLE values - Replace with your actual credentials

define('DB_HOST', 'localhost'); // Usually 'localhost' for InfinityFree
define('DB_USER', 'epiz_xxxxxxx'); // Your InfinityFree database username
define('DB_PASS', 'your_password_here'); // Your InfinityFree database password  
define('DB_NAME', 'epiz_xxxxx_databasename'); // Your database name from InfinityFree

// IMPORTANT: How to find your credentials on InfinityFree:
// 1. Log in to InfinityFree Control Panel
// 2. Go to MySQL Databases
// 3. You'll see your database name, username, and password
// 4. Database Host is usually 'localhost'

// MySQLi Connection
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to UTF-8
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// PDO Connection Alternative (Optional)
function getPDOConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}

// Test connection on include (optional - comment out in production)
// $conn = getConnection();
// echo "Connected successfully!";
// $conn->close();
?>
