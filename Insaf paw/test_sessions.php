<?php
/**
 * Test Script - Insert Sample Sessions
 * 
 * This script inserts 2-3 test sessions into the database.
 */

require_once 'db_connect.php';

// Get database connection
$pdo = getDBConnection();

if ($pdo === null) {
    die("Database connection failed. Please check your configuration.\n");
}

// Sample sessions data
$testSessions = [
    [
        'course_id' => 'CS101',
        'group_id' => 'Group A',
        'date' => date('Y-m-d'),
        'opened_by' => 'PROF001',
        'status' => 'open'
    ],
    [
        'course_id' => 'MATH201',
        'group_id' => 'Group B',
        'date' => date('Y-m-d', strtotime('+1 day')),
        'opened_by' => 'PROF002',
        'status' => 'open'
    ],
    [
        'course_id' => 'PHYS301',
        'group_id' => 'Group A',
        'date' => date('Y-m-d', strtotime('-1 day')),
        'opened_by' => 'PROF001',
        'status' => 'closed'
    ]
];

echo "<!DOCTYPE html>\n";
echo "<html lang='en'>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "    <title>Test Sessions - Attendance System</title>\n";
echo "    <link rel='stylesheet' href='styles.css'>\n";
echo "</head>\n";
echo "<body>\n";

echo "<div class='navbar'>\n";
echo "  <a href='home.html'>Home</a>\n";
echo "  <a href='list_sessions.php'>Sessions</a>\n";
echo "  <a href='create_session.php'>Create Session</a>\n";
echo "  <a href='reports.html'>Reports</a>\n";
echo "</div>\n";

echo "<div class='container'>\n";
echo "  <div class='content-card'>\n";
echo "    <h2>Test Sessions Insertion</h2>\n";
echo "    <p>Inserting test sessions into the database...</p>\n";

$insertedCount = 0;
$errors = [];

try {
    $stmt = $pdo->prepare("INSERT INTO attendance_sessions (course_id, group_id, date, opened_by, status) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($testSessions as $session) {
        try {
            $stmt->execute([
                $session['course_id'],
                $session['group_id'],
                $session['date'],
                $session['opened_by'],
                $session['status']
            ]);
            
            $sessionId = $pdo->lastInsertId();
            $insertedCount++;
            
            echo "<div class='success-message' style='margin-top: 1rem;'>\n";
            echo "  ✓ Session #{$sessionId} inserted successfully<br>\n";
            echo "  Course: {$session['course_id']}, Group: {$session['group_id']}, Date: {$session['date']}, Status: {$session['status']}\n";
            echo "</div>\n";
            
        } catch (PDOException $e) {
            $errors[] = "Error inserting session ({$session['course_id']} - {$session['group_id']}): " . $e->getMessage();
        }
    }
    
    if (count($errors) > 0) {
        echo "<div class='error' style='background: rgba(239, 68, 68, 0.1); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid var(--danger-color); margin-top: 1rem;'>\n";
        echo "  <strong>Errors:</strong><br>\n";
        foreach ($errors as $error) {
            echo "  • " . htmlspecialchars($error) . "<br>\n";
        }
        echo "</div>\n";
    }
    
    echo "<div style='margin-top: 2rem; padding: 1.5rem; background: rgba(99, 102, 241, 0.1); border-radius: var(--radius-md); border-left: 4px solid var(--primary-color);'>\n";
    echo "  <strong>Summary:</strong><br>\n";
    echo "  Total sessions to insert: " . count($testSessions) . "<br>\n";
    echo "  Successfully inserted: {$insertedCount}<br>\n";
    echo "  Errors: " . count($errors) . "\n";
    echo "</div>\n";
    
} catch (PDOException $e) {
    echo "<div class='error' style='background: rgba(239, 68, 68, 0.1); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid var(--danger-color);'>\n";
    echo "  <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "\n";
    echo "</div>\n";
}

echo "<div style='margin-top: 2rem; text-align: center;'>\n";
echo "  <a href='list_sessions.php' class='btn btn-primary'>View All Sessions</a>\n";
echo "  <a href='create_session.php' class='btn btn-secondary'>Create New Session</a>\n";
echo "</div>\n";

echo "  </div>\n";
echo "</div>\n";
echo "</body>\n";
echo "</html>\n";

