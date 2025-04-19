<?php
include 'db_connection.php';

function completeExam($exam_id, $student_id) {
    global $conn;

    // Compare and generate result
    $query = "SELECT ep.question_id, ep.selected_answer, eq.correct_answer
              FROM exam_progress ep
              JOIN exam_questions eq ON ep.exam_id = eq.exam_id AND ep.question_id = eq.question_id AND ep.student_id = eq.student_id
              WHERE ep.exam_id = ? AND ep.student_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $exam_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $total_questions = 0;
    $correct_answers = 0;
    $incorrect_answers = 0;

    while ($row = $result->fetch_assoc()) {
        $total_questions++;
        if ($row['selected_answer'] === $row['correct_answer']) {
            $correct_answers++;
        } else {
            $incorrect_answers++;
        }
    }

    // Update results in exams table
    $update_query = "UPDATE exams SET total_questions = ?, correct = ?, incorrect = ? WHERE exam_id = ? AND user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("iiisi", $total_questions, $correct_answers, $incorrect_answers, $exam_id, $student_id);
    if (!$stmt->execute()) {
        error_log("Error executing query (update exams): " . $stmt->error);
    } else {
        error_log("Updated exams table for exam_id: " . $exam_id);
    }

    // Move data to exam_final and clear exam_progress
    $final_query = "INSERT INTO exam_final (exam_id, question_id, question, option1, option2, option3, option4, correct_answer, selected_answer, student_id)
                    SELECT ep.exam_id, ep.question_id, ep.question, ep.option1, ep.option2, ep.option3, ep.option4, ep.correct_answer, ep.selected_answer, ep.student_id
                    FROM exam_progress ep
                    WHERE ep.exam_id = ? AND ep.student_id = ?";
    $stmt = $conn->prepare($final_query);
    $stmt->bind_param("si", $exam_id, $student_id);
    if (!$stmt->execute()) {
        error_log("Error executing query (insert into exam_final): " . $stmt->error);
    } else {
        error_log("Inserted data into exam_final for exam_id: " . $exam_id);
    }

    $clear_query = "DELETE FROM exam_progress WHERE exam_id = ? AND student_id = ?";
    $stmt = $conn->prepare($clear_query);
    $stmt->bind_param("si", $exam_id, $student_id);
    if (!$stmt->execute()) {
        error_log("Error executing query (clear exam_progress): " . $stmt->error);
    } else {
        error_log("Cleared exam_progress for exam_id: " . $exam_id);
    }

    $stmt->close();
}
?>