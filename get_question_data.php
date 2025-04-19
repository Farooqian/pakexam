<?php
include 'db_connection.php';

// Check if a valid question_id is passed
if (isset($_GET['id'])) {
    $question_id = $_GET['id'];

    // Fetch the question data from the database
    $query = "SELECT * FROM exam_final WHERE question_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if data is found
    if ($result->num_rows > 0) {
        // Fetch the question data
        $row = $result->fetch_assoc();
        
        // Return data as JSON
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'No data found']);
    }
} else {
    echo json_encode(['error' => 'Invalid question ID']);
}
?>
