<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db_connection.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['exam_id'])) {
    $exam_id = $_GET['exam_id'];
    $new_status = $_GET['status'];  // 'active' or 'inactive'

    // Update the exam status in the database
    $query = "UPDATE exams SET status = ? WHERE exam_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $new_status, $exam_id);

    if ($stmt->execute()) {
        echo "Exam status updated successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!-- Assuming this is part of the active exams page, you can have links like this -->
<a href="update_exam_status.php?exam_id=1&status=active">Start Exam</a>
<a href="update_exam_status.php?exam_id=1&status=inactive">Stop Exam</a>
