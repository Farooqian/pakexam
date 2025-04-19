<?php
// Include database connection file
include('db_connection.php');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure that the necessary data is passed from the client side
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data (JSON)
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate the data to ensure required fields are present
    if (isset($data['question_id'], $data['is_reviewed'], $data['is_problematic'], $_SESSION['exam_id'], $_SESSION['user_id'])) {
        // Get the question ID, reviewed and problematic values
        $question_id = $data['question_id'];
        $is_reviewed = $data['is_reviewed'] ? 1 : 0;
        $is_problematic = $data['is_problematic'] ? 1 : 0;

        // Prepare the SQL query to update the exam_progress table
        $query = "UPDATE exam_progress 
                  SET is_reviewed = ?, is_problematic = ? 
                  WHERE question_id = ? AND exam_id = ? AND student_id = ?";
        
        // Prepare and bind parameters to prevent SQL injection
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiisi", $is_reviewed, $is_problematic, $question_id, $_SESSION['exam_id'], $_SESSION['user_id']);
        
        // Execute the statement
        if ($stmt->execute()) {
            // Send a success response if the query executed successfully
            echo json_encode(["status" => "success"]);
        } else {
            // Send an error response if the query failed
            echo json_encode(["status" => "error", "message" => "Failed to update checkbox state."]);
        }
        
        // Close the statement
        $stmt->close();
    } else {
        // Send an error response if the data is invalid
        echo json_encode(["status" => "error", "message" => "Invalid data received."]);
    }
} else {
    // Send an error response if the request is not POST
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

// Close the database connection
$conn->close();
?>
