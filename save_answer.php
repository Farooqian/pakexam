<?php
include 'db_connection.php';

$exam_id = $_POST['exam_id'];
$question_id = $_POST['question_id'];
$answer = $_POST['answer'];
$review = $_POST['review'];
$problematic = $_POST['problematic'];

$query = $conn->prepare("UPDATE exam_questions 
    SET selected_option = ?, is_reviewed = ?, is_problematic = ? 
    WHERE exam_id = ? AND question_id = ?");
$query->bind_param("sisss", $answer, $review, $problematic, $exam_id, $question_id);
$query->execute();
