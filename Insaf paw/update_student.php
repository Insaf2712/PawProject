<?php
/**
 * Update Student
 * 
 * This script handles updating student information.
 */

require_once 'db_connect.php';

// Initialize variables
$errors = [];
$success = false;
$message = '';
$student = null;

// Get student ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: list_students.php');
    exit;
}

// Get database connection
$pdo = getDBConnection();

if ($pdo === null) {
    $errors['general'] = 'Database connection failed. Please check your configuration.';
} else {
    // Load student data
    try {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $student = $stmt->fetch();
        
        if (!$student) {
            header('Location: list_students.php');
            exit;
        }
    } catch (PDOException $e) {
        $errors['general'] = 'Error loading student: ' . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $student) {
    // Get and sanitize form data
    $fullname = trim($_POST['fullname'] ?? '');
    $matricule = trim($_POST['matricule'] ?? '');
    $group_id = trim($_POST['group_id'] ?? '');
    
    // Validation
    if (empty($fullname)) {
        $errors['fullname'] = 'Full name is required.';
    } elseif (!preg_match('/^[a-zA-Z\s\'-]+$/', $fullname)) {
        $errors['fullname'] = 'Full name must contain only letters, spaces, hyphens, and apostrophes.';
    }
    
    if (empty($matricule)) {
        $errors['matricule'] = 'Matricule is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $matricule)) {
        $errors['matricule'] = 'Matricule must contain only letters and numbers.';
    }
    
    if (empty($group_id)) {
        $errors['group_id'] = 'Group ID is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9\s]+$/', $group_id)) {
        $errors['group_id'] = 'Group ID must contain only letters, numbers, and spaces.';
    }
    
    // If no errors, update database
    if (empty($errors)) {
        try {
            // Check if matricule already exists for another student
            $stmt = $pdo->prepare("SELECT id FROM students WHERE matricule = ? AND id != ?");
            $stmt->execute([$matricule, $id]);
            
            if ($stmt->fetch()) {
                $errors['matricule'] = 'This matricule already exists for another student.';
            } else {
                // Update student
                $stmt = $pdo->prepare("UPDATE students SET fullname = ?, matricule = ?, group_id = ? WHERE id = ?");
                $stmt->execute([$fullname, $matricule, $group_id, $id]);
                
                $success = true;
                $message = "Student information has been successfully updated!";
                
                // Reload student data
                $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
                $stmt->execute([$id]);
                $student = $stmt->fetch();
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Error updating student: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Student - Attendance System</title>
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
    <h2>Update Student</h2>
    <p>Modify student information below.</p>
    
    <?php if ($success): ?>
        <div class="success-message">
            ✓ <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($errors['general'])): ?>
        <div class="error" style="background: rgba(239, 68, 68, 0.1); padding: 1rem; border-radius: var(--radius-md); border-left: 4px solid var(--danger-color); margin-bottom: 1.5rem;">
            ⚠ <?php echo htmlspecialchars($errors['general']); ?>
        </div>
    <?php endif; ?>

    <?php if ($student): ?>
        <div class="form-card">
          <form method="POST" action="update_student.php?id=<?php echo $id; ?>">
            <div class="form-group">
              <label for="fullname">Full Name *</label>
              <input 
                type="text" 
                id="fullname" 
                name="fullname" 
                required 
                placeholder="Enter full name"
                value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : htmlspecialchars($student['fullname']); ?>">
              <?php if (isset($errors['fullname'])): ?>
                <span class="error"><?php echo htmlspecialchars($errors['fullname']); ?></span>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label for="matricule">Matricule *</label>
              <input 
                type="text" 
                id="matricule" 
                name="matricule" 
                required 
                placeholder="Enter matricule"
                value="<?php echo isset($_POST['matricule']) ? htmlspecialchars($_POST['matricule']) : htmlspecialchars($student['matricule']); ?>">
              <?php if (isset($errors['matricule'])): ?>
                <span class="error"><?php echo htmlspecialchars($errors['matricule']); ?></span>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label for="group_id">Group ID *</label>
              <input 
                type="text" 
                id="group_id" 
                name="group_id" 
                required 
                placeholder="Enter group ID"
                value="<?php echo isset($_POST['group_id']) ? htmlspecialchars($_POST['group_id']) : htmlspecialchars($student['group_id']); ?>">
              <?php if (isset($errors['group_id'])): ?>
                <span class="error"><?php echo htmlspecialchars($errors['group_id']); ?></span>
              <?php endif; ?>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <input type="submit" value="Update Student" style="flex: 1;">
                <a href="list_students.php" class="btn btn-secondary" style="padding: 0.875rem 2rem;">Cancel</a>
            </div>
          </form>
        </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>

