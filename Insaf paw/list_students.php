<?php
/**
 * List All Students
 * 
 * This script displays all students from the database.
 */

require_once 'db_connect.php';

// Get database connection
$pdo = getDBConnection();
$students = [];
$error = '';

if ($pdo === null) {
    $error = 'Database connection failed. Please check your configuration.';
} else {
    try {
        $stmt = $pdo->query("SELECT * FROM students ORDER BY fullname ASC");
        $students = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = 'Error loading students: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Students - Attendance System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        .search-container {
            margin-bottom: 1.5rem;
        }
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            color: white;
            text-align: center;
            box-shadow: var(--shadow-md);
        }
        .stat-card h3 {
            color: white;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
        .stat-card p {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }
    </style>
</head>
<body>

<div class="navbar">
  <a href="home.html">Home</a>
  <a href="list_students.php" class="active">Students</a>
  <a href="add_student.php">Add Student</a>
  <a href="reports.html">Reports</a>
  <a href="logout.html">Logout</a>
</div>

<div class="container">
  <div class="content-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
      <h2>Students List</h2>
      <a href="add_student.php" class="btn btn-primary">➕ Add New Student</a>
    </div>
    
    <?php if ($error): ?>
        <div class="error" style="background: rgba(239, 68, 68, 0.1); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid var(--danger-color); margin-bottom: 1.5rem;">
            ⚠ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php else: ?>
        <?php if (count($students) > 0): ?>
            <div class="stats-cards">
                <div class="stat-card">
                    <h3>Total Students</h3>
                    <p><?php echo count($students); ?></p>
                </div>
                <?php
                // Count students by group
                $groups = [];
                foreach ($students as $student) {
                    $group = $student['group_id'];
                    $groups[$group] = ($groups[$group] ?? 0) + 1;
                }
                ?>
                <div class="stat-card" style="background: linear-gradient(135deg, var(--secondary-color), #34d399);">
                    <h3>Groups</h3>
                    <p><?php echo count($groups); ?></p>
                </div>
            </div>
            
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="🔍 Search by name, matricule, or group...">
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Matricule</th>
                            <th>Group ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['id']); ?></td>
                                <td><?php echo htmlspecialchars($student['fullname']); ?></td>
                                <td><strong><?php echo htmlspecialchars($student['matricule']); ?></strong></td>
                                <td><?php echo htmlspecialchars($student['group_id']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="update_student.php?id=<?php echo $student['id']; ?>" class="btn btn-secondary btn-small">✏️ Edit</a>
                                        <a href="delete_student.php?id=<?php echo $student['id']; ?>" 
                                           class="btn btn-small" 
                                           style="background: var(--danger-color); color: white;"
                                           onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($student['fullname']); ?>?');">🗑️ Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="info-box" style="background: rgba(99, 102, 241, 0.1); padding: 2rem; border-radius: var(--radius-md); border-left: 4px solid var(--primary-color); text-align: center;">
                <p style="font-size: 1.25rem; margin-bottom: 1rem;">No students found.</p>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Start by adding your first student.</p>
                <a href="add_student.php" class="btn btn-primary">Add First Student</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput')?.addEventListener('keyup', function() {
    const value = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(value) ? '' : 'none';
    });
});
</script>

</body>
</html>

