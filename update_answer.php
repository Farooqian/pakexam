<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'db_connection.php';
    $question_id = $_POST['question_id'];
    $selected_answer = $_POST['selected_answer'];

    $query = "UPDATE exam_progress SET selected_answer = ? WHERE question_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $selected_answer, $question_id);

    if ($stmt->execute()) {
        echo "Answer updated successfully.";
    } else {
        echo "Failed to update the answer.";
    }
    $stmt->close();
    $conn->close();
}
?>