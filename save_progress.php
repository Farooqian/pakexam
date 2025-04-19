<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    include 'db_connection.php';

    $exam_id = $_SESSION['exam_id'] ?? null;
    $student_id = $_SESSION['user_id'] ?? null;

    if (!$exam_id || !$student_id) {
        echo "Error: Missing session data.";
        exit;
    }

    $question_id = $_POST['question_id'] ?? null;
    $selected_answer = $_POST['selected_answer'] ?? null;
    $column_name = $_POST['column_name'] ?? null;
    $is_checked = isset($_POST['is_checked']) ? (int)$_POST['is_checked'] : null;

    if ($question_id && $selected_answer) {
        // Save selected answer
        $query = "UPDATE exam_progress SET selected_answer = ? WHERE exam_id = ? AND student_id = ? AND question_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $selected_answer, $exam_id, $student_id, $question_id);
        if ($stmt->execute()) {
            echo "Answer saved successfully.";
        } else {
            echo "Error saving answer.";
        }
    } elseif ($question_id && $column_name) {
        // Save checkbox state
        $query = "UPDATE exam_progress SET $column_name = ? WHERE exam_id = ? AND student_id = ? AND question_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isii", $is_checked, $exam_id, $student_id, $question_id);
        if ($stmt->execute()) {
            echo "Checkbox state saved successfully.";
        } else {
            echo "Error saving checkbox state.";
        }
    } else {
        echo "Invalid request.";
    }
}
?>