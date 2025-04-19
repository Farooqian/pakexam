<?php
include 'db_connection.php'; // Ensure database connection is available

if (!function_exists('addQuestionsToProgress')) {
    function addQuestionsToProgress($exam_id, $student_id) {
        global $conn;
        $query = "INSERT INTO exam_progress (exam_id, question_id, question, option1, option2, option3, option4, correct_answer, student_id, selected_answer, last_updated)
                  SELECT exam_id, question_id, question, option1, option2, option3, option4, correct_answer, student_id, NULL, NOW()
                  FROM exam_questions WHERE exam_id = ? AND student_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $exam_id, $student_id);
       
        if (!$stmt->execute()) {
            error_log("Error executing query (addQuestionsToProgress): " . $stmt->error);
        } else {
            error_log("Inserted questions into exam_progress for exam_id: " . $exam_id);
        }
        $stmt->close();
    }
}

// Call function with actual values (Example usage)
$exam_id = "250310000201"; // Replace with actual exam ID
$student_id = 5; // Replace with actual student ID from session or database
addQuestionsToProgress($exam_id, $student_id);
?>
