<?php
/**
 * List All Attendance Sessions
 * 
 * This script displays all attendance sessions from the database.
 */

require_once 'db_connect.php';

// Get database connection
$pdo = getDBConnection();
$sessions = [];
$error = '';

if ($pdo === null) {
    $error = 'Database connection failed. Please check your configuration.';
} else {
    try {
        $stmt = $pdo->query("SELECT * FROM attendance_sessions ORDER BY date DESC, id DESC");
        $sessions = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = 'Error loading sessions: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Sessions - Attendance System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .status-badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
        }
        .status-open {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }
        .status-closed {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>

<div class="navbar">
  <a href="home.html">Home</a>
  <a href="list_students.php">Students</a>
  <a href="add_student.php">Add Student</a>
  <a href="create_session.php">Create Session</a>
  <a href="list_sessions.php" class="active">Sessions</a>
  <a href="reports.html">Reports</a>
  <a href="logout.html">Logout</a>
</div>

<div class="container">
  <div class="content-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
      <h2>Attendance Sessions</h2>
      <a href="create_session.php" class="btn btn-primary">➕ Create New Session</a>
    </div>
    
    <?php if ($error): ?>
        <div class="error" style="background: rgba(239, 68, 68, 0.1); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid var(--danger-color); margin-bottom: 1.5rem;">
            ⚠ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php else: ?>
        <?php if (count($sessions) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Course ID</th>
                            <th>Group ID</th>
                            <th>Date</th>
                            <th>Opened By</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($session['id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($session['course_id']); ?></td>
                                <td><?php echo htmlspecialchars($session['group_id']); ?></td>
                                <td><?php echo htmlspecialchars($session['date']); ?></td>
                                <td><?php echo htmlspecialchars($session['opened_by']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $session['status'] === 'open' ? 'status-open' : 'status-closed'; ?>">
                                        <?php echo htmlspecialchars($session['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($session['status'] === 'open'): ?>
                                        <a href="close_session.php?id=<?php echo $session['id']; ?>" 
                                           class="btn btn-small" 
                                           style="background: var(--warning-color); color: white;">
                                            🔒 Close
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-secondary); font-size: 0.875rem;">Closed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="info-box" style="background: rgba(99, 102, 241, 0.1); padding: 2rem; border-radius: var(--radius-md); border-left: 4px solid var(--primary-color); text-align: center;">
                <p style="font-size: 1.25rem; margin-bottom: 1rem;">No sessions found.</p>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Start by creating your first attendance session.</p>
                <a href="create_session.php" class="btn btn-primary">Create First Session</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

</body>
</html>

