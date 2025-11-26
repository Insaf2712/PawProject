<?php
/**
 * Database and Table Creation Script
 * 
 * This script creates the database and students table.
 * Run this once to set up your database structure.
 */

require_once 'config.php';

// Database name - update this if needed
$dbName = 'attendance_system';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Database Table</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="navbar">
  <a href="home.html">Home</a>
  <a href="list_students.php">Students</a>
  <a href="add_student.php">Add Student</a>
  <a href="reports.html">Reports</a>
  <a href="logout.html">Logout</a>
</div>

<div class="container">
  <div class="content-card">
    <h2>Database Setup</h2>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
        try {
            // First, connect without database name to create the database
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            // Create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Select the database
            $pdo->exec("USE `{$dbName}`");
            
            // Create students table
            $sql = "CREATE TABLE IF NOT EXISTS `students` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `fullname` VARCHAR(255) NOT NULL,
                `matricule` VARCHAR(50) NOT NULL UNIQUE,
                `group_id` VARCHAR(50) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_matricule` (`matricule`),
                INDEX `idx_group` (`group_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $pdo->exec($sql);
            
            echo '<div class="success-message">';
            echo '✓ Database and table created successfully!<br>';
            echo 'Database: ' . htmlspecialchars($dbName) . '<br>';
            echo 'Table: students';
            echo '</div>';
            
            echo '<div style="margin-top: 1.5rem;">';
            echo '<p><strong>Next steps:</strong></p>';
            echo '<ol style="margin-left: 1.5rem; color: var(--text-secondary);">';
            echo '<li>Update DB_NAME in config.php to: <code>' . htmlspecialchars($dbName) . '</code></li>';
            echo '<li>You can now use the student management pages</li>';
            echo '</ol>';
            echo '</div>';
            
        } catch (PDOException $e) {
            echo '<div class="error" style="background: rgba(239, 68, 68, 0.1); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid var(--danger-color);">';
            echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage());
            echo '</div>';
        }
    } else {
        ?>
        <p>This script will create the database and students table with the following structure:</p>
        
        <div class="form-card" style="margin-top: 1.5rem;">
            <h3 style="margin-bottom: 1rem;">Table Structure: students</h3>
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Column</th>
                        <th>Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>id</code></td>
                        <td>INT(11)</td>
                        <td>Primary key, auto-increment</td>
                    </tr>
                    <tr>
                        <td><code>fullname</code></td>
                        <td>VARCHAR(255)</td>
                        <td>Student's full name</td>
                    </tr>
                    <tr>
                        <td><code>matricule</code></td>
                        <td>VARCHAR(50)</td>
                        <td>Student ID/Matricule (unique)</td>
                    </tr>
                    <tr>
                        <td><code>group_id</code></td>
                        <td>VARCHAR(50)</td>
                        <td>Group identifier</td>
                    </tr>
                    <tr>
                        <td><code>created_at</code></td>
                        <td>TIMESTAMP</td>
                        <td>Record creation timestamp</td>
                    </tr>
                    <tr>
                        <td><code>updated_at</code></td>
                        <td>TIMESTAMP</td>
                        <td>Last update timestamp</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <form method="POST" style="margin-top: 2rem;">
            <input type="hidden" name="create" value="1">
            <input type="submit" value="Create Database and Table" class="btn btn-primary">
        </form>
        <?php
    }
    ?>
    
    <div style="margin-top: 2rem; text-align: center;">
        <a href="home.html" class="btn btn-secondary">Back to Home</a>
    </div>
  </div>
</div>

</body>
</html>

