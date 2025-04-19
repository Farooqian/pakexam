<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'db_connection.php';

    // Get the raw POST data
    $data = json_decode(file_get_contents('php://input'), true);
    file_put_contents('log.txt', print_r($data, true), FILE_APPEND);

    if (isset($data['question_id'], $data['selected_answer'], $data['reviewed'], $data['problematic'])) {
        $question_id = $data['question_id'];
        $selected_answer = $data['selected_answer'];
        $reviewed = $data['reviewed'] ? 1 : 0;
        $problematic = $data['problematic'] ? 1 : 0;
        $exam_id = $_SESSION['exam_id'];  // Ensure the exam ID is in the session
        $student_id = $_SESSION['student_id'];  // Ensure the student ID is in the session

        // Update the exam progress table
        $query = "INSERT INTO exam_progress (exam_id, student_id, question_id, selected_answer, is_reviewed, is_problematic)
                  VALUES (?, ?, ?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE selected_answer = VALUES(selected_answer), is_reviewed = VALUES(is_reviewed), is_problematic = VALUES(is_problematic)";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssii", $exam_id, $student_id, $question_id, $selected_answer, $reviewed, $problematic);

        if ($stmt->execute()) {
            echo "State saved successfully.";
        } else {
            echo "Error saving state: " . $stmt->error;
        }
    } else {
        echo "Invalid input data.";
    }
} else {
    echo "Invalid request method.";
}
?>
