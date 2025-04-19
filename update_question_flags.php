<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'db_connection.php';
    $question_id = $_POST['question_id'];
    $column_name = $_POST['column_name'];
    $is_checked = $_POST['is_checked'];

    // Validate column name to prevent SQL injection
    if (!in_array($column_name, ['is_reviewed', 'is_problematic'])) {
        echo "Invalid column name.";
        exit;
    }

    $query = "UPDATE exam_progress SET $column_name = ? WHERE question_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $is_checked, $question_id);

    if ($stmt->execute()) {
        echo "Checkbox updated successfully.";
    } else {
        echo "Failed to update the checkbox.";
    }
    $stmt->close();
    $conn->close();
}
?>