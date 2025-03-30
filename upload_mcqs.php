<?php
require_once 'db_connection.php'; // Ensure this file contains a working database connection

if (isset($_FILES["csv_file"]) && $_FILES["csv_file"]["error"] == 0) {
    $filename = $_FILES["csv_file"]["tmp_name"];
} else {
    die("Error: No file uploaded or file upload error.");
}

// Check if file exists
if (!file_exists($filename) || !is_readable($filename)) {
    die("Error: File not found or not readable.");
}

$handle = fopen($filename, "r");
if (!$handle) {
    die("Error: Unable to open file.");
}

// Skip the header row
fgetcsv($handle);

while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    if (count($data) < 8) {
        echo "Skipping row: Insufficient columns.<br>";
        continue;
    }

    // Assign CSV values
    $organization = trim($data[0]);
    $subject = trim($data[1]);
    $question = trim($data[2]);
    $option1 = trim($data[3]);
    $option2 = trim($data[4]);
    $option3 = trim($data[5]);
    $option4 = trim($data[6]);
    $correct_answer = strtoupper(trim($data[7])); // Keep as A, B, C, D

    // Validate correct answer (must be A, B, C, or D)
    if (!in_array($correct_answer, ['A', 'B', 'C', 'D'])) {
        echo "Skipping row: Invalid correct answer '$correct_answer'.<br>";
        continue;
    }

    // Insert into database using prepared statements
    $stmt = $conn->prepare("INSERT INTO MCQs (organization, subject, question, option1, option2, option3, option4, correct_answer) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $organization, $subject, $question, $option1, $option2, $option3, $option4, $correct_answer);

    if ($stmt->execute()) {
        echo "Row inserted successfully.<br>";
    } else {
        echo "Error inserting row: " . $stmt->error . "<br>";
    }

    $stmt->close();
}

fclose($handle);
$conn->close();
?>
