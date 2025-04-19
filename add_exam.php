<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db_connection.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $exam_name = $_POST['exam_name'];
    $start_time = $_POST['start_time'];  // Assuming the time is in 'Y-m-d H:i' format
    $duration = $_POST['duration'];

    // Insert the new exam into the database
    $query = "INSERT INTO exams (exam_name, start_time, duration, status) VALUES (?, ?, ?, 'inactive')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $exam_name, $start_time, $duration);

    if ($stmt->execute()) {
        echo "Exam added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Exam | Admin Dashboard</title>
</head>
<body>

<h2>Add New Exam</h2>

<form method="POST">
    <label for="exam_name">Exam Name:</label><br>
    <input type="text" name="exam_name" id="exam_name" required><br><br>

    <label for="start_time">Start Time:</label><br>
    <input type="datetime-local" name="start_time" id="start_time" required><br><br>

    <label for="duration">Duration (minutes):</label><br>
    <input type="number" name="duration" id="duration" required><br><br>

    <input type="submit" value="Add Exam">
</form>

</body>
</html>
