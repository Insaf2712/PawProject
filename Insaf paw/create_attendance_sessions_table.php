<?php
/**
 * Create Attendance Sessions Table
 * 
 * This script creates the attendance_sessions table.
 */

require_once 'db_connect.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Attendance Sessions Table</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="navbar">
  <a href="home.html">Home</a>
  <a href="list_students.php">Students</a>
  <a href="create_session.php">Create Session</a>
  <a href="list_sessions.php">Sessions</a>
  <a href="reports.html">Reports</a>
</div>

<div class="container">
  <div class="content-card">
    <h2>Create Attendance Sessions Table</h2>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
        $pdo = getDBConnection();
        
        if ($pdo === null) {
            echo '<div class="error" style="background: rgba(239, 68, 68, 0.1); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid var(--danger-color);">';
            echo 'Database connection failed. Please check your configuration.';
            echo '</div>';
        } else {
            try {
                // Create attendance_sessions table
                $sql = "CREATE TABLE IF NOT EXISTS `attendance_sessions` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `course_id` VARCHAR(100) NOT NULL,
                    `group_id` VARCHAR(50) NOT NULL,
                    `date` DATE NOT NULL,
                    `opened_by` VARCHAR(100) NOT NULL,
                    `status` ENUM('open', 'closed') DEFAULT 'open',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    INDEX `idx_course` (`course_id`),
                    INDEX `idx_group` (`group_id`),
                    INDEX `idx_date` (`date`),
                    INDEX `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                $pdo->exec($sql);
                
                echo '<div class="success-message">';
                echo '✓ Table \'attendance_sessions\' created successfully!<br>';
                echo 'Columns: id, course_id, group_id, date, opened_by, status';
                echo '</div>';
                
                echo '<div style="margin-top: 1.5rem;">';
                echo '<p><strong>Next steps:</strong></p>';
                echo '<ol style="margin-left: 1.5rem; color: var(--text-secondary);">';
                echo '<li>You can now create attendance sessions</li>';
                echo '<li>Use <a href="create_session.php">create_session.php</a> to create new sessions</li>';
                echo '<li>Use <a href="test_sessions.php">test_sessions.php</a> to insert test data</li>';
                echo '</ol>';
                echo '</div>';
                
            } catch (PDOException $e) {
                echo '<div class="error" style="background: rgba(239, 68, 68, 0.1); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid var(--danger-color);">';
                echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage());
                echo '</div>';
            }
        }
    } else {
        ?>
        <p>This script will create the attendance_sessions table with the following structure:</p>
        
        <div class="form-card" style="margin-top: 1.5rem;">
            <h3 style="margin-bottom: 1rem;">Table Structure: attendance_sessions</h3>
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
                        <td><code>course_id</code></td>
                        <td>VARCHAR(100)</td>
                        <td>Course identifier</td>
                    </tr>
                    <tr>
                        <td><code>group_id</code></td>
                        <td>VARCHAR(50)</td>
                        <td>Group identifier</td>
                    </tr>
                    <tr>
                        <td><code>date</code></td>
                        <td>DATE</td>
                        <td>Session date</td>
                    </tr>
                    <tr>
                        <td><code>opened_by</code></td>
                        <td>VARCHAR(100)</td>
                        <td>Professor/Instructor ID</td>
                    </tr>
                    <tr>
                        <td><code>status</code></td>
                        <td>ENUM('open', 'closed')</td>
                        <td>Session status (default: 'open')</td>
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
            <input type="submit" value="Create Table" class="btn btn-primary">
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

