<?php
// Start output buffering to prevent "headers already sent" issues
ob_start();

// Start the session if it's not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if required session variables are set
$exam_id = $_SESSION['exam_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$exam_id || !$user_id) {
    echo "Error: Missing session data (exam_id or user_id).";
    exit;
}

// Include database connection
include 'db_connection.php';

// Save exam state as completed (example query)
$query = "UPDATE exam_progress SET end_time = NOW() WHERE exam_id = ? AND student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $exam_id, $user_id);

if (!$stmt->execute()) {
    echo "Error: Failed to update exam status.";
    exit;
}

// Redirect to the results page
header("Location: results.php");
exit;
?>