<?php
/**
 * Add Student to Database
 * 
 * This script handles adding new students to the database.
 */

require_once 'db_connect.php';

// Initialize variables
$errors = [];
$success = false;
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    
    // If no errors, insert into database
    if (empty($errors)) {
        $pdo = getDBConnection();
        
        if ($pdo === null) {
            $errors['general'] = 'Database connection failed. Please check your configuration.';
        } else {
            try {
                // Check if matricule already exists
                $stmt = $pdo->prepare("SELECT id FROM students WHERE matricule = ?");
                $stmt->execute([$matricule]);
                
                if ($stmt->fetch()) {
                    $errors['matricule'] = 'This matricule already exists. Please use a different one.';
                } else {
                    // Insert new student
                    $stmt = $pdo->prepare("INSERT INTO students (fullname, matricule, group_id) VALUES (?, ?, ?)");
                    $stmt->execute([$fullname, $matricule, $group_id]);
                    
                    $success = true;
                    $message = "Student '{$fullname}' (Matricule: {$matricule}) has been successfully added to group '{$group_id}'!";
                    
                    // Clear form data
                    $_POST = [];
                }
            } catch (PDOException $e) {
                $errors['general'] = 'Error saving student: ' . $e->getMessage();
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
    <title>Add Student - Attendance System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="navbar">
  <a href="home.html">Home</a>
  <a href="list_students.php">Students</a>
  <a href="add_student.php" class="active">Add Student</a>
  <a href="reports.html">Reports</a>
  <a href="logout.html">Logout</a>
</div>

<div class="container">
  <div class="content-card">
    <h2>Add New Student</h2>
    <p>Use the form below to register a new student in the attendance system.</p>
    
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

    <div class="form-card">
      <form method="POST" action="add_student.php">
        <div class="form-group">
          <label for="fullname">Full Name *</label>
          <input 
            type="text" 
            id="fullname" 
            name="fullname" 
            required 
            placeholder="Enter full name (e.g., John Doe)"
            value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>">
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
            placeholder="Enter matricule (e.g., STU001)"
            value="<?php echo isset($_POST['matricule']) ? htmlspecialchars($_POST['matricule']) : ''; ?>">
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
            placeholder="Enter group ID (e.g., Group A, CS101)"
            value="<?php echo isset($_POST['group_id']) ? htmlspecialchars($_POST['group_id']) : ''; ?>">
          <?php if (isset($errors['group_id'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['group_id']); ?></span>
          <?php endif; ?>
        </div>

        <input type="submit" value="Add Student">
      </form>
    </div>
    
    <?php if ($success): ?>
        <div style="margin-top: 1.5rem; text-align: center;">
            <a href="add_student.php" class="btn btn-secondary">Add Another Student</a>
            <a href="list_students.php" class="btn btn-primary">View All Students</a>
        </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
