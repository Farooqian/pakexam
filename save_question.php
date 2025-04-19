<?php
include 'db_connect.php'; // Include your database connection file

// Fetch POST data
$exam_id = $_POST['exam_id'];
$student_id = $_POST['student_id'];
$question_id = $_POST['question_id'];
$selected_answer = $_POST['selected_answer'];
$is_reviewed = isset($_POST['is_reviewed']) ? (int)$_POST['is_reviewed'] : 0;

// SQL Query to update question progress
$sql = "UPDATE exam_progress 
        SET selected_answer = ?, is_reviewed = ?, last_updated = CURRENT_TIMESTAMP 
        WHERE exam_id = ? AND student_id = ? AND question_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sisii", $selected_answer, $is_reviewed, $exam_id, $student_id, $question_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
?>