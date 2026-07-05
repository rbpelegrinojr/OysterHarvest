<?php
/**
 * Database Configuration File
 * 
 * This file contains database connection parameters.
 * Update these values according to your local or production environment.
 */

// Database configuration constants
define('DB_HOST', 'localhost');           // Database host (usually 'localhost')
define('DB_USER', 'root');                // Database username
define('DB_PASS', '');                    // Database password
define('DB_NAME', 'oyster_harvest_db');   // Database name

/**
 * Establishes and returns a database connection
 * 
 * @return mysqli|null Database connection object or null on failure
 */
function getDBConnection() {
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return null;
    }
    
    // Set charset to utf8mb4 for proper Unicode support
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Closes the database connection
 * 
 * @param mysqli $conn Database connection object
 */
function closeDBConnection($conn) {
    if ($conn && !$conn->connect_error) {
        $conn->close();
    }
}

/**
 * Executes a prepared statement with error handling
 * 
 * @param mysqli $conn Database connection
 * @param string $query SQL query with placeholders
 * @param string $types Parameter types (e.g., 'ssi' for string, string, integer)
 * @param array $params Array of parameters
 * @return mysqli_stmt|false Prepared statement or false on failure
 */
function executeQuery($conn, $query, $types = '', $params = []) {
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Query preparation failed: " . $conn->error);
        return false;
    }
    
    if ($types && count($params) > 0) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Query execution failed: " . $stmt->error);
        return false;
    }
    
    return $stmt;
}
?>
