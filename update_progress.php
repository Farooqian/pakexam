<?php
session_start();
include('db_connection.php');

if (isset($_POST['exam_id']) && isset($_POST['student_id']) && isset($_POST['answers'])) {
    $exam_id = $_POST['exam_id'];
    $student_id = $_POST['student_id'];
    $answers = json_decode($_POST['answers'], true);

    foreach ($answers as $question_id => $selected_answer) {
        // Check if record already exists in exam_progress
        $query = "SELECT id FROM exam_progress WHERE exam_id = '$exam_id' AND student_id = '$student_id' AND question_id = '$question_id'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            // Update the existing record
            $update_query = "UPDATE exam_progress SET selected_answer = '$selected_answer' WHERE exam_id = '$exam_id' AND student_id = '$student_id' AND question_id = '$question_id'";
            mysqli_query($conn, $update_query);
        } else {
            // Insert a new record if it doesn't exist
            $insert_query = "INSERT INTO exam_progress (exam_id, student_id, question_id, selected_answer) VALUES ('$exam_id', '$student_id', '$question_id', '$selected_answer')";
            mysqli_query($conn, $insert_query);
        }
    }
}
?>
