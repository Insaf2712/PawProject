<?php
/**
 * Database Connection Test Script
 * 
 * This script tests the database connection and displays the result.
 */

// Include database connection file
require_once 'db_connect.php';

// Set content type for proper display
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .test-container {
            max-width: 600px;
            margin: 2rem auto;
        }
        .test-result {
            padding: 2rem;
            border-radius: var(--radius-lg);
            margin-top: 1.5rem;
            text-align: center;
        }
        .test-success {
            background: linear-gradient(135deg, var(--success-color), #34d399);
            color: white;
            box-shadow: var(--shadow-lg);
        }
        .test-failure {
            background: linear-gradient(135deg, var(--danger-color), #f87171);
            color: white;
            box-shadow: var(--shadow-lg);
        }
        .test-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .test-details {
            background: var(--bg-secondary);
            padding: 1.5rem;
            border-radius: var(--radius-md);
            margin-top: 1.5rem;
            text-align: left;
        }
        .test-details h3 {
            margin-bottom: 1rem;
            color: var(--text-primary);
        }
        .test-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .test-details td {
            padding: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        .test-details td:first-child {
            font-weight: 600;
            color: var(--text-primary);
            width: 40%;
        }
        .test-details td:last-child {
            color: var(--text-secondary);
            font-family: monospace;
        }
    </style>
</head>
<body>

<div class="navbar">
  <a href="home.html">Home</a>
  <a href="attendance.html">Attendance List</a>
  <a href="add_student.php">Add Student</a>
  <a href="reports.html">Reports</a>
  <a href="take_attendance.php">Take Attendance</a>
  <a href="logout.html">Logout</a>
</div>

<div class="container">
  <div class="content-card">
    <h2>Database Connection Test</h2>
    <p>Testing database connection with the configured parameters...</p>
    
    <div class="test-container">
        <?php
        // Attempt to connect to database
        $connection = getDBConnection();
        
        if ($connection !== null) {
            // Connection successful
            echo '<div class="test-result test-success">';
            echo '<div class="test-icon">✅</div>';
            echo '<h2 style="color: white; margin-bottom: 0.5rem;">Connection Successful!</h2>';
            echo '<p style="color: rgba(255,255,255,0.9);">The database connection has been established successfully.</p>';
            echo '</div>';
            
            // Try to get database information
            try {
                $stmt = $connection->query("SELECT VERSION() as version, DATABASE() as database_name");
                $dbInfo = $stmt->fetch();
                
                echo '<div class="test-details">';
                echo '<h3>Connection Details</h3>';
                echo '<table>';
                echo '<tr><td>Status</td><td style="color: var(--success-color); font-weight: 600;">✓ Connected</td></tr>';
                echo '<tr><td>Database Host</td><td>' . htmlspecialchars(DB_HOST) . '</td></tr>';
                echo '<tr><td>Database Name</td><td>' . htmlspecialchars($dbInfo['database_name'] ?? DB_NAME) . '</td></tr>';
                echo '<tr><td>Database Version</td><td>' . htmlspecialchars($dbInfo['version'] ?? 'Unknown') . '</td></tr>';
                echo '<tr><td>Username</td><td>' . htmlspecialchars(DB_USERNAME) . '</td></tr>';
                echo '<tr><td>Charset</td><td>' . htmlspecialchars(DB_CHARSET) . '</td></tr>';
                echo '</table>';
                echo '</div>';
                
            } catch (PDOException $e) {
                // Couldn't get database info, but connection is still successful
                echo '<div class="test-details">';
                echo '<p style="color: var(--text-secondary);">Note: Could not retrieve database information.</p>';
                echo '</div>';
            }
            
        } else {
            // Connection failed
            echo '<div class="test-result test-failure">';
            echo '<div class="test-icon">❌</div>';
            echo '<h2 style="color: white; margin-bottom: 0.5rem;">Connection Failed!</h2>';
            echo '<p style="color: rgba(255,255,255,0.9);">Unable to establish a connection to the database.</p>';
            echo '</div>';
            
            echo '<div class="test-details">';
            echo '<h3>Configuration Details</h3>';
            echo '<table>';
            echo '<tr><td>Status</td><td style="color: var(--danger-color); font-weight: 600;">✗ Failed</td></tr>';
            echo '<tr><td>Database Host</td><td>' . htmlspecialchars(DB_HOST) . '</td></tr>';
            echo '<tr><td>Database Name</td><td>' . htmlspecialchars(DB_NAME) . '</td></tr>';
            echo '<tr><td>Username</td><td>' . htmlspecialchars(DB_USERNAME) . '</td></tr>';
            echo '<tr><td>Port</td><td>' . htmlspecialchars(DB_PORT) . '</td></tr>';
            echo '</table>';
            echo '<p style="margin-top: 1rem; color: var(--text-secondary);">';
            echo 'Please check:<br>';
            echo '• Database server is running<br>';
            echo '• Configuration in config.php is correct<br>';
            echo '• Database exists and user has proper permissions<br>';
            echo '• Check db_errors.log for detailed error information';
            echo '</p>';
            echo '</div>';
        }
        ?>
        
        <div style="margin-top: 2rem; text-align: center;">
            <a href="home.html" class="btn btn-primary">Back to Home</a>
            <a href="test_connection.php" class="btn btn-secondary">Test Again</a>
        </div>
    </div>
  </div>
</div>

</body>
</html>

