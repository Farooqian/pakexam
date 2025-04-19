<?php
include 'db_connect.php'; // Include your database connection file

// Fetch POST data
$exam_id = $_POST['exam_id'];
$student_id = $_POST['student_id'];
$question_id = $_POST['question_id'] ?? null; // Optional for specific question

// SQL Query to fetch question
$sql = "SELECT question_id, question, option1, option2, option3, option4, selected_answer, is_reviewed, start_time, end_time
        FROM exam_progress 
        WHERE exam_id = ? AND student_id = ?";

if (!empty($question_id)) {
    $sql .= " AND question_id = ?";
}

$stmt = $conn->prepare($sql);

if (!empty($question_id)) {
    $stmt->bind_param("ssi", $exam_id, $student_id, $question_id);
} else {
    $stmt->bind_param("ss", $exam_id, $student_id);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    // Convert UTC time to student's local time
    $row['start_time'] = convertToLocalTime($row['start_time']);
    $row['end_time'] = convertToLocalTime($row['end_time']);
    $data[] = $row;
}

echo json_encode($data);

function convertToLocalTime($utcTime) {
    $utcDate = new DateTime($utcTime, new DateTimeZone('UTC')); // UTC timezone
    $localTimezone = new DateTimeZone('Asia/Karachi'); // Change to student's timezone
    $utcDate->setTimezone($localTimezone);
    return $utcDate->format('Y-m-d H:i:s'); // Return local time
}
?>