<?php
// take_attendance.php - Take attendance for students

// Initialize variables
$errors = [];
$success = false;
$message = '';
$students = [];
$todayFile = '';
$attendanceExists = false;

// Get today's date in YYYY-MM-DD format
$today = date('Y-m-d');
$todayFile = "attendance_{$today}.json";

// Check if attendance for today already exists
if (file_exists($todayFile)) {
    $attendanceExists = true;
}

// Load students from students.json
$jsonFile = 'students.json';
if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $students = json_decode($jsonContent, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errors['general'] = 'Error reading students file. Please check the file format.';
        $students = [];
    }
} else {
    $errors['general'] = 'Students file not found. Please add students first.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$attendanceExists) {
    // Check if attendance for today still doesn't exist (double check)
    if (file_exists($todayFile)) {
        $attendanceExists = true;
        $errors['general'] = 'Attendance for today has already been taken.';
    } else {
        // Get attendance data from form
        $attendance = [];
        
        if (!empty($students)) {
            foreach ($students as $index => $student) {
                // Get student_id - use existing or generate from index
                $studentId = '';
                if (isset($student['student_id']) && !empty($student['student_id'])) {
                    $studentId = $student['student_id'];
                } else {
                    // Generate a temporary ID if not present
                    $studentId = 'STU' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
                }
                
                // Get status from form (default to absent if not set)
                $statusKey = 'attendance_' . $index;
                $status = isset($_POST[$statusKey]) ? $_POST[$statusKey] : 'absent';
                
                // Validate status
                if (!in_array($status, ['present', 'absent'])) {
                    $status = 'absent';
                }
                
                $attendance[] = [
                    'student_id' => $studentId,
                    'status' => $status
                ];
            }
        }
        
        // Save attendance to file
        $jsonData = json_encode($attendance, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (file_put_contents($todayFile, $jsonData) !== false) {
            $success = true;
            $presentCount = count(array_filter($attendance, function($item) {
                return $item['status'] === 'present';
            }));
            $absentCount = count($attendance) - $presentCount;
            $message = "Attendance for {$today} has been successfully saved! ({$presentCount} present, {$absentCount} absent)";
            $attendanceExists = true; // Update flag after successful save
        } else {
            $errors['general'] = 'Error saving attendance data. Please check file permissions.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Attendance - Attendance System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .attendance-list {
            margin-top: 1.5rem;
        }
        .student-row {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 0.75rem;
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            transition: all 0.2s ease;
        }
        .student-row:hover {
            border-color: var(--primary-color);
            box-shadow: var(--shadow-sm);
        }
        .student-info {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .student-id {
            font-weight: 600;
            color: var(--primary-color);
            min-width: 100px;
        }
        .student-name {
            flex: 1;
            font-weight: 500;
        }
        .student-group {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        .attendance-options {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .radio-group {
            display: flex;
            gap: 1.5rem;
        }
        .radio-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .radio-option input[type="radio"] {
            width: 1.25rem;
            height: 1.25rem;
            cursor: pointer;
        }
        .radio-option label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
        }
        .radio-option input[type="radio"]:checked + label {
            color: var(--primary-color);
        }
        .date-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            border-radius: var(--radius-md);
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .info-box {
            background: rgba(99, 102, 241, 0.1);
            padding: 1rem 1.5rem;
            border-radius: var(--radius-md);
            border-left: 4px solid var(--primary-color);
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>

<div class="navbar">
  <a href="home.html">Home</a>
  <a href="attendance.html">Attendance List</a>
  <a href="add_student.php">Add Student</a>
  <a href="reports.html">Reports</a>
  <a href="take_attendance.php" class="active">Take Attendance</a>
  <a href="logout.html">Logout</a>
</div>

<div class="container">
  <div class="content-card">
    <h2>Take Attendance</h2>
    
    <div class="date-badge">
      📅 <?php echo date('l, F j, Y'); ?> (<?php echo $today; ?>)
    </div>
    
    <?php if ($attendanceExists && !$success): ?>
        <div class="error" style="background: rgba(239, 68, 68, 0.1); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid var(--danger-color); margin-bottom: 1.5rem;">
            <strong>⚠ Attendance Already Taken</strong><br>
            Attendance for today has already been taken.
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success-message">
            ✓ <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($errors['general'])): ?>
        <div class="error" style="background: rgba(239, 68, 68, 0.1); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid var(--danger-color); margin-bottom: 1.5rem;">
            ⚠ <?php echo htmlspecialchars($errors['general']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($students)): ?>
        <div class="info-box">
            <strong>No students found.</strong> Please add students first using the "Add Student" page.
        </div>
    <?php elseif (!$attendanceExists): ?>
        <div class="info-box">
            <strong>Instructions:</strong> Mark each student as Present or Absent, then click "Submit Attendance" to save.
        </div>
        
        <form method="POST" action="take_attendance.php">
            <div class="attendance-list">
                <?php foreach ($students as $index => $student): ?>
                    <?php
                    // Get student information
                    $studentId = isset($student['student_id']) && !empty($student['student_id']) 
                        ? $student['student_id'] 
                        : 'STU' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
                    $firstName = $student['firstName'] ?? '';
                    $lastName = $student['lastName'] ?? '';
                    $fullName = trim($firstName . ' ' . $lastName);
                    if (empty($fullName) && isset($student['name'])) {
                        $fullName = $student['name'];
                    }
                    $group = $student['group'] ?? 'N/A';
                    ?>
                    <div class="student-row">
                        <div class="student-info">
                            <div class="student-id">ID: <?php echo htmlspecialchars($studentId); ?></div>
                            <div class="student-name"><?php echo htmlspecialchars($fullName); ?></div>
                            <?php if ($group !== 'N/A'): ?>
                                <div class="student-group">Group: <?php echo htmlspecialchars($group); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="attendance-options">
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input 
                                        type="radio" 
                                        id="present_<?php echo $index; ?>" 
                                        name="attendance_<?php echo $index; ?>" 
                                        value="present"
                                        checked>
                                    <label for="present_<?php echo $index; ?>">✅ Present</label>
                                </div>
                                <div class="radio-option">
                                    <input 
                                        type="radio" 
                                        id="absent_<?php echo $index; ?>" 
                                        name="attendance_<?php echo $index; ?>" 
                                        value="absent">
                                    <label for="absent_<?php echo $index; ?>">❌ Absent</label>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center;">
                <input type="submit" value="📝 Submit Attendance" class="btn btn-primary" style="width: auto; padding: 1rem 2.5rem; font-size: 1.1rem;">
            </div>
        </form>
    <?php else: ?>
        <div class="info-box">
            <strong>Attendance Complete</strong><br>
            You can view the attendance records or take attendance for another day.
        </div>
        
        <div style="margin-top: 2rem; text-align: center;">
            <a href="attendance.html" class="btn btn-primary">View Attendance List</a>
            <a href="reports.html" class="btn btn-secondary">View Reports</a>
        </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>

