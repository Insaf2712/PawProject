<?php
/**
 * Close Attendance Session
 * 
 * This script updates the status of an attendance session to "closed".
 */

require_once 'db_connect.php';

// Initialize variables
$errors = [];
$success = false;
$message = '';
$session = null;

// Get session ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: list_sessions.php');
    exit;
}

// Get database connection
$pdo = getDBConnection();

if ($pdo === null) {
    $errors['general'] = 'Database connection failed. Please check your configuration.';
} else {
    // Load session data
    try {
        $stmt = $pdo->prepare("SELECT * FROM attendance_sessions WHERE id = ?");
        $stmt->execute([$id]);
        $session = $stmt->fetch();
        
        if (!$session) {
            header('Location: list_sessions.php');
            exit;
        }
    } catch (PDOException $e) {
        $errors['general'] = 'Error loading session: ' . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_close']) && $session) {
    try {
        // Update session status to closed
        $stmt = $pdo->prepare("UPDATE attendance_sessions SET status = 'closed' WHERE id = ?");
        $stmt->execute([$id]);
        
        $success = true;
        $message = "Session #{$id} has been successfully closed!";
        
        // Reload session data
        $stmt = $pdo->prepare("SELECT * FROM attendance_sessions WHERE id = ?");
        $stmt->execute([$id]);
        $session = $stmt->fetch();
        
    } catch (PDOException $e) {
        $errors['general'] = 'Error closing session: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Close Session - Attendance System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.875rem;
        }
        .status-open {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border: 2px solid var(--success-color);
        }
        .status-closed {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
            border: 2px solid var(--danger-color);
        }
    </style>
</head>
<body>

<div class="navbar">
  <a href="home.html">Home</a>
  <a href="list_students.php">Students</a>
  <a href="add_student.php">Add Student</a>
  <a href="create_session.php">Create Session</a>
  <a href="reports.html">Reports</a>
  <a href="logout.html">Logout</a>
</div>

<div class="container">
  <div class="content-card">
    <h2>Close Attendance Session</h2>
    
    <?php if ($success): ?>
        <div class="success-message">
            ✓ <?php echo htmlspecialchars($message); ?>
        </div>
        <div style="margin-top: 2rem; text-align: center;">
            <a href="list_sessions.php" class="btn btn-primary">Back to Sessions List</a>
        </div>
    <?php elseif ($error = ($errors['general'] ?? '')): ?>
        <div class="error" style="background: rgba(239, 68, 68, 0.1); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid var(--danger-color);">
            ⚠ <?php echo htmlspecialchars($error); ?>
        </div>
        <div style="margin-top: 2rem; text-align: center;">
            <a href="list_sessions.php" class="btn btn-primary">Back to Sessions List</a>
        </div>
    <?php elseif ($session): ?>
        <?php if ($session['status'] === 'closed'): ?>
            <div class="info-box" style="background: rgba(245, 158, 11, 0.1); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid var(--warning-color); margin-bottom: 1.5rem;">
                <strong>⚠ This session is already closed.</strong>
            </div>
        <?php else: ?>
            <p>Are you sure you want to close this attendance session? Once closed, the session cannot be reopened.</p>
        <?php endif; ?>
        
        <div class="form-card" style="margin-top: 1.5rem;">
            <h3 style="margin-bottom: 1rem;">Session Information</h3>
            <table style="width: 100%;">
                <tr>
                    <td style="font-weight: 600; padding: 0.5rem 0;">Session ID:</td>
                    <td style="padding: 0.5rem 0;"><strong><?php echo htmlspecialchars($session['id']); ?></strong></td>
                </tr>
                <tr>
                    <td style="font-weight: 600; padding: 0.5rem 0;">Course ID:</td>
                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($session['course_id']); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: 600; padding: 0.5rem 0;">Group ID:</td>
                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($session['group_id']); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: 600; padding: 0.5rem 0;">Date:</td>
                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($session['date']); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: 600; padding: 0.5rem 0;">Opened By:</td>
                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($session['opened_by']); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: 600; padding: 0.5rem 0;">Status:</td>
                    <td style="padding: 0.5rem 0;">
                        <span class="status-badge <?php echo $session['status'] === 'open' ? 'status-open' : 'status-closed'; ?>">
                            <?php echo strtoupper($session['status']); ?>
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        
        <?php if ($session['status'] === 'open'): ?>
            <form method="POST" action="close_session.php?id=<?php echo $id; ?>" style="margin-top: 2rem;">
                <input type="hidden" name="confirm_close" value="1">
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <input type="submit" value="🔒 Close Session" class="btn" style="background: var(--warning-color); color: white; padding: 0.875rem 2rem;">
                    <a href="list_sessions.php" class="btn btn-secondary" style="padding: 0.875rem 2rem;">Cancel</a>
                </div>
            </form>
        <?php else: ?>
            <div style="margin-top: 2rem; text-align: center;">
                <a href="list_sessions.php" class="btn btn-primary">Back to Sessions List</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

</body>
</html>

