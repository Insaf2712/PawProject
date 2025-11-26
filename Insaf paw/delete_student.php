<?php
/**
 * Delete Student
 * 
 * This script handles deleting a student from the database.
 */

require_once 'db_connect.php';

// Get student ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: list_students.php');
    exit;
}

// Get database connection
$pdo = getDBConnection();
$success = false;
$error = '';
$student = null;

if ($pdo === null) {
    $error = 'Database connection failed. Please check your configuration.';
} else {
    // Load student data first
    try {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $student = $stmt->fetch();
        
        if (!$student) {
            header('Location: list_students.php');
            exit;
        }
    } catch (PDOException $e) {
        $error = 'Error loading student: ' . $e->getMessage();
    }
    
    // Handle deletion if confirmed
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && $student) {
        try {
            $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$id]);
            $success = true;
        } catch (PDOException $e) {
            $error = 'Error deleting student: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Student - Attendance System</title>
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
    <?php if ($success): ?>
        <h2>Student Deleted</h2>
        <div class="success-message">
            ✓ Student has been successfully deleted!
        </div>
        <div style="margin-top: 2rem; text-align: center;">
            <a href="list_students.php" class="btn btn-primary">Back to Students List</a>
        </div>
    <?php elseif ($error): ?>
        <h2>Error</h2>
        <div class="error" style="background: rgba(239, 68, 68, 0.1); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid var(--danger-color);">
            ⚠ <?php echo htmlspecialchars($error); ?>
        </div>
        <div style="margin-top: 2rem; text-align: center;">
            <a href="list_students.php" class="btn btn-primary">Back to Students List</a>
        </div>
    <?php elseif ($student): ?>
        <h2>Delete Student</h2>
        <p>Are you sure you want to delete this student? This action cannot be undone.</p>
        
        <div class="form-card" style="margin-top: 1.5rem;">
            <h3 style="margin-bottom: 1rem;">Student Information</h3>
            <table style="width: 100%;">
                <tr>
                    <td style="font-weight: 600; padding: 0.5rem 0;">ID:</td>
                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($student['id']); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: 600; padding: 0.5rem 0;">Full Name:</td>
                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($student['fullname']); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: 600; padding: 0.5rem 0;">Matricule:</td>
                    <td style="padding: 0.5rem 0;"><strong><?php echo htmlspecialchars($student['matricule']); ?></strong></td>
                </tr>
                <tr>
                    <td style="font-weight: 600; padding: 0.5rem 0;">Group ID:</td>
                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($student['group_id']); ?></td>
                </tr>
            </table>
        </div>
        
        <form method="POST" action="delete_student.php?id=<?php echo $id; ?>" style="margin-top: 2rem;">
            <input type="hidden" name="confirm_delete" value="1">
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <input type="submit" value="🗑️ Confirm Delete" class="btn" style="background: var(--danger-color); color: white; padding: 0.875rem 2rem;">
                <a href="list_students.php" class="btn btn-secondary" style="padding: 0.875rem 2rem;">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
  </div>
</div>

</body>
</html>

