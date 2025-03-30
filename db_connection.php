<?php
$servername = "localhost";
$username = "root"; // Default for Laragon
$password = ""; // Default for Laragon
$database = "project_dream"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
