<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include 'db_connection.php';

// Enable MySQL error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Check if the user has an active session (logged in)
    if (isset($_SESSION['user_regid'])) {
        // Start a database transaction
        $conn->begin_transaction();

        // Get the user registration number (to update session status)
        $user_regid = $_SESSION['user_regid'];

        // Update session status to 'inactive'
        $updateSessionStatusQuery = "UPDATE user_sessions SET session_status = 'inactive' WHERE registration_number = ? AND session_status = 'active'";
        $stmtUpdateStatus = $conn->prepare($updateSessionStatusQuery);
        $stmtUpdateStatus->bind_param("s", $user_regid);
        $stmtUpdateStatus->execute();

        // If there's an exam session, move exam data (from `exam_progress` to `exam_final`)
        if (isset($_SESSION['exam_id'])) {
            $exam_id = $_SESSION['exam_id'];
            // Move exam data
            $moveQuery = "INSERT INTO exam_final SELECT * FROM exam_progress WHERE exam_id = ?";
            $stmtMove = $conn->prepare($moveQuery);
            $stmtMove->bind_param("s", $exam_id);
            $stmtMove->execute();

            // Delete data from `exam_progress`
            $deleteQuery = "DELETE FROM exam_progress WHERE exam_id = ?";
            $stmtDelete = $conn->prepare($deleteQuery);
            $stmtDelete->bind_param("s", $exam_id);
            $stmtDelete->execute();
        }

        // Commit the transaction
        $conn->commit();

        // Destroy session data
        session_unset(); // Remove all session variables
        session_destroy(); // Destroy the session

        // Redirect to the homepage or login page
        header("Location: index.php");
        exit();
    } else {
        // If no session exists, redirect to login
        header("Location: login.php");
        exit();
    }
} catch (Exception $e) {
    // Rollback the transaction if an error occurs
    if ($conn->in_transaction) {
        $conn->rollback();
    }
    echo "Error: " . $e->getMessage();
    exit();
}
?>
