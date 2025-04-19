<?php
require_once "db_connection.php";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['registration_number'])) {
    header("Location: login.php");
    exit();
}

$registration_number = $_SESSION['registration_number'];

// Fetch results
$stmt = $conn->prepare("SELECT exam_id, organization, subject, total_questions, attempted_questions, correct_answers, wrong_answers, score, result_date FROM results WHERE student_id = ?");
$stmt->bind_param("s", $registration_number);
$stmt->execute();
$result = $stmt->get_result();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=exam_results.csv');

$output = fopen('php://output', 'w');

// Column headings
fputcsv($output, ['Exam ID', 'Organization', 'Subject', 'Total', 'Attempted', 'Correct', 'Wrong', 'Score', 'Date']);

// Output rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit();
