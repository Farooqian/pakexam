<?php

// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch the role from the session
include 'db_connection.php';

$query = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if the user is an admin
if ($user['role'] !== 'admin') {
    // Redirect if the logged-in user is not an admin
    header("Location: login.php");
    exit();
}


// Include database connection and other logic
include 'db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Questions</title>
</head>
<body>
    <h1>Upload MCQ Questions</h1>
    
    <form action="upload_mcq_csv.php" method="post" enctype="multipart/form-data">
        <label for="fileToUpload">Choose CSV file to upload:</label>
        <input type="file" name="fileToUpload" id="fileToUpload" required>
        <br><br>
        <input type="submit" value="Upload CSV" name="submit">
    </form>
    <br>
    <!-- Redirect to Dashboard -->
    <button onclick="window.location.href='dashboard.php';">Go to Dashboard</button>
</body>
</html>
