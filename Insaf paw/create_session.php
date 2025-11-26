<?php
/**
 * Create Attendance Session
 * 
 * This script creates a new attendance session and returns the session ID.
 */

require_once 'db_connect.php';

// Initialize variables
$errors = [];
$success = false;
$sessionId = null;
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $course_id = trim($_POST['course_id'] ?? '');
    $group_id = trim($_POST['group_id'] ?? '');
    $professor_id = trim($_POST['professor_id'] ?? '');
    $date = trim($_POST['date'] ?? date('Y-m-d')); // Default to today if not provided
    
    // Validation
    if (empty($course_id)) {
        $errors['course_id'] = 'Course ID is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9\s\-_]+$/', $course_id)) {
        $errors['course_id'] = 'Course ID must contain only letters, numbers, spaces, hyphens, and underscores.';
    }
    
    if (empty($group_id)) {
        $errors['group_id'] = 'Group ID is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9\s]+$/', $group_id)) {
        $errors['group_id'] = 'Group ID must contain only letters, numbers, and spaces.';
    }
    
    if (empty($professor_id)) {
        $errors['professor_id'] = 'Professor ID is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9\s\-_]+$/', $professor_id)) {
        $errors['professor_id'] = 'Professor ID must contain only letters, numbers, spaces, hyphens, and underscores.';
    }
    
    // Validate date format
    if (!empty($date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $errors['date'] = 'Date must be in YYYY-MM-DD format.';
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        $pdo = getDBConnection();
        
        if ($pdo === null) {
            $errors['general'] = 'Database connection failed. Please check your configuration.';
        } else {
            try {
                // Insert new session
                $stmt = $pdo->prepare("INSERT INTO attendance_sessions (course_id, group_id, date, opened_by, status) VALUES (?, ?, ?, ?, 'open')");
                $stmt->execute([$course_id, $group_id, $date, $professor_id]);
                
                // Get the session ID
                $sessionId = $pdo->lastInsertId();
                
                $success = true;
                $message = "Attendance session created successfully! Session ID: {$sessionId}";
                
                // Clear form data
                $_POST = [];
                
            } catch (PDOException $e) {
                $errors['general'] = 'Error creating session: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Attendance Session - Attendance System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="navbar">
  <a href="home.html">Home</a>
  <a href="list_students.php">Students</a>
  <a href="add_student.php">Add Student</a>
  <a href="create_session.php" class="active">Create Session</a>
  <a href="reports.html">Reports</a>
  <a href="logout.html">Logout</a>
</div>

<div class="container">
  <div class="content-card">
    <h2>Create Attendance Session</h2>
    <p>Create a new attendance session for a course and group.</p>
    
    <?php if ($success): ?>
        <div class="success-message">
            ✓ <?php echo htmlspecialchars($message); ?>
        </div>
        <?php if ($sessionId): ?>
            <div class="form-card" style="margin-top: 1.5rem; background: linear-gradient(135deg, var(--primary-color), var(--primary-light)); color: white;">
                <h3 style="color: white; margin-bottom: 0.5rem;">Session ID</h3>
                <p style="font-size: 2rem; font-weight: 700; margin: 0; color: white;"><?php echo htmlspecialchars($sessionId); ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (isset($errors['general'])): ?>
        <div class="error" style="background: rgba(239, 68, 68, 0.1); padding: 1rem; border-radius: var(--radius-md); border-left: 4px solid var(--danger-color); margin-bottom: 1.5rem;">
            ⚠ <?php echo htmlspecialchars($errors['general']); ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
      <form method="POST" action="create_session.php">
        <div class="form-group">
          <label for="course_id">Course ID *</label>
          <input 
            type="text" 
            id="course_id" 
            name="course_id" 
            required 
            placeholder="Enter course ID (e.g., CS101, MATH201)"
            value="<?php echo isset($_POST['course_id']) ? htmlspecialchars($_POST['course_id']) : ''; ?>">
          <?php if (isset($errors['course_id'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['course_id']); ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="group_id">Group ID *</label>
          <input 
            type="text" 
            id="group_id" 
            name="group_id" 
            required 
            placeholder="Enter group ID (e.g., Group A, G1)"
            value="<?php echo isset($_POST['group_id']) ? htmlspecialchars($_POST['group_id']) : ''; ?>">
          <?php if (isset($errors['group_id'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['group_id']); ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="professor_id">Professor ID *</label>
          <input 
            type="text" 
            id="professor_id" 
            name="professor_id" 
            required 
            placeholder="Enter professor ID (e.g., PROF001, Dr. Smith)"
            value="<?php echo isset($_POST['professor_id']) ? htmlspecialchars($_POST['professor_id']) : ''; ?>">
          <?php if (isset($errors['professor_id'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['professor_id']); ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="date">Date *</label>
          <input 
            type="date" 
            id="date" 
            name="date" 
            required 
            value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : date('Y-m-d'); ?>">
          <?php if (isset($errors['date'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['date']); ?></span>
          <?php endif; ?>
        </div>

        <input type="submit" value="Create Session">
      </form>
    </div>
    
    <?php if ($success): ?>
        <div style="margin-top: 1.5rem; text-align: center;">
            <a href="create_session.php" class="btn btn-secondary">Create Another Session</a>
            <a href="list_sessions.php" class="btn btn-primary">View All Sessions</a>
        </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>

