<?php
/**
 * Database Connection File
 * 
 * This file handles database connections using PDO with proper error handling.
 * It includes try/catch blocks and optional error logging.
 */

// Include configuration file
require_once 'config.php';

/**
 * Establishes a database connection using PDO
 * 
 * @return PDO|null Returns PDO connection object on success, null on failure
 */
function getDBConnection() {
    try {
        // Build DSN (Data Source Name)
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        // PDO options for better error handling and security
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];
        
        // Create PDO connection
        $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
        
        return $pdo;
        
    } catch (PDOException $e) {
        // Log error to file
        logDatabaseError($e);
        
        // Handle error based on configuration
        if (DB_SHOW_ERRORS) {
            // In development: show detailed error
            error_log("Database Connection Error: " . $e->getMessage());
        } else {
            // In production: show generic message
            error_log("Database connection failed. Please contact administrator.");
        }
        
        return null;
    }
}

/**
 * Logs database errors to a file
 * 
 * @param PDOException $exception The exception to log
 * @return void
 */
function logDatabaseError($exception) {
    $logFile = 'db_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $errorMessage = "[{$timestamp}] Database Error: " . $exception->getMessage() . "\n";
    $errorMessage .= "File: " . $exception->getFile() . " Line: " . $exception->getLine() . "\n";
    $errorMessage .= "Stack Trace:\n" . $exception->getTraceAsString() . "\n";
    $errorMessage .= str_repeat("-", 80) . "\n\n";
    
    // Append to log file
    file_put_contents($logFile, $errorMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Test database connection
 * 
 * @return bool Returns true if connection is successful, false otherwise
 */
function testConnection() {
    $conn = getDBConnection();
    if ($conn !== null) {
        return true;
    }
    return false;
}

