<?php
// Database connection
include('db_connection.php');

// Validate session variables
if (!isset($_SESSION['student_id']) || !isset($_SESSION['exam_id'])) {
    die("Error: Required session variables are missing.");
}

// Get student_id and exam_id from session
$student_id = mysqli_real_escape_string($conn, $_SESSION['student_id']);
$exam_id = mysqli_real_escape_string($conn, $_SESSION['exam_id']);

// Fetch student information from the 'users' table using 'registration_number'
$student_query = "SELECT full_name, email FROM users WHERE registration_number = '$student_id'";
$student_result = mysqli_query($conn, $student_query);

if (!$student_result) {
    error_log("Student query failed: " . mysqli_error($conn));
    die("Error fetching student information.");
}

$student_info = mysqli_fetch_assoc($student_result);
if (!$student_info) {
    $student_info = ['full_name' => 'N/A', 'email' => 'N/A']; // Default values if no record is found
}

// Fetch exam information from the 'exam_progress' table
$exam_query = "SELECT organization, subject FROM exam_progress WHERE exam_id = '$exam_id' LIMIT 1";
$exam_result = mysqli_query($conn, $exam_query);

if (!$exam_result) {
    error_log("Exam query failed: " . mysqli_error($conn));
    die("Error fetching exam information.");
}

$exam_info = mysqli_fetch_assoc($exam_result);
if (!$exam_info) {
    $exam_info = ['organization' => 'N/A', 'subject' => 'N/A']; // Default values if no record is found
}

// Step 1: Prepare Result Summary
$query = "SELECT * FROM exam_progress WHERE exam_id = '$exam_id' AND student_id = '$student_id'";
$result = mysqli_query($conn, $query);

if (!$result) {
    error_log("Exam progress query failed: " . mysqli_error($conn));
    die("Error fetching exam progress.");
}

$total_questions = mysqli_num_rows($result);
$attempted_questions = 0;
$correct_answers = 0;

while ($row = mysqli_fetch_assoc($result)) {
    if (!is_null($row['selected_answer'])) {
        $attempted_questions++;
        if ($row['selected_answer'] === $row['correct_answer']) {
            $correct_answers++;
        }
    }
}

$unattempted_questions = $total_questions - $attempted_questions;
$wrong_answers = $attempted_questions - $correct_answers;
$total_marks = 100;

// Prevent division by zero
if ($total_questions > 0) {
    $obtained_marks = ($correct_answers / $total_questions) * $total_marks;
} else {
    $obtained_marks = 0; // Default to 0 if no questions exist
}

// Step 2: Save Result Summary in 'results' Table
$insert_query = "INSERT INTO results 
    (student_id, exam_id, organization, subject, total_questions, attempted_questions, correct_answers, wrong_answers, score)
    VALUES 
    ('$student_id', '$exam_id', '{$exam_info['organization']}', '{$exam_info['subject']}', '$total_questions', '$attempted_questions', '$correct_answers', '$wrong_answers', '$obtained_marks')";

if (!mysqli_query($conn, $insert_query)) {
    error_log("Insert into results failed: " . mysqli_error($conn));
    die("Error saving result summary.");
}

// Step 3: Move Data to 'exam_final' and Empty Tables
$move_query = "INSERT INTO exam_final 
    (exam_id, question_id, question, option1, option2, option3, option4, correct_answer, student_id, selected_answer, is_reviewed, is_problematic, organization, subject, duration_minutes, start_time, end_time)
    SELECT exam_id, question_id, question, option1, option2, option3, option4, correct_answer, student_id, selected_answer, is_reviewed, is_problematic, organization, subject, duration_minutes, start_time, end_time
    FROM exam_progress WHERE exam_id = '$exam_id' AND student_id = '$student_id'";

if (!mysqli_query($conn, $move_query)) {
    error_log("Move to exam_final failed: " . mysqli_error($conn));
    die("Error transferring data to exam_final.");
}

$delete_progress_query = "DELETE FROM exam_progress WHERE exam_id = '$exam_id' AND student_id = '$student_id'";
if (!mysqli_query($conn, $delete_progress_query)) {
    error_log("Delete from exam_progress failed: " . mysqli_error($conn));
    die("Error deleting data from exam_progress.");
}

$delete_questions_query = "DELETE FROM exam_questions WHERE exam_id = '$exam_id'";
if (!mysqli_query($conn, $delete_questions_query)) {
    error_log("Delete from exam_questions failed: " . mysqli_error($conn));
    die("Error deleting data from exam_questions.");
}

// Step 4: Redirect After 45 Seconds
header("Refresh: 45; URL=dashboard.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Results</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #141E30, #243B55);
            color: #f3f3f3;
        }
        .container {
            width: 90%;
            margin: 30px auto;
        }
        h1, h3 {
            text-align: center;
        }
        .info-container {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            background: #1E293B;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            color: #f3f3f3;
        }
        .info-box {
            flex: 1;
            padding: 20px;
            margin: 10px;
            background: #334155;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .info-box h3 {
            margin: 0 0 10px;
            text-align: center;
            color: #FACC15;
        }
        .info-box table {
            width: 100%;
            color: #f3f3f3;
            border-collapse: collapse;
        }
        .info-box table th, .info-box table td {
            text-align: left;
            padding: 8px 12px;
        }
        .result-summary {
            padding: 20px;
            background: #1E293B;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }
        .result-summary table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .result-summary table th, .result-summary table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .result-summary table th {
            background: #FACC15;
            color: #1E293B;
        }
        .button-container {
            text-align: center;
            margin-top: 20px;
        }
        .button-container button {
            padding: 12px 25px;
            font-size: 18px;
            background: #FACC15;
            color: #1E293B;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .button-container button:hover {
            background: #FFB703;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Student and Exam Information -->
        <h1>Result Summary</h1>
        <div class="info-container">
            <!-- Student Info -->
            <div class="info-box">
                <h3>Student Info</h3>
                <table>
                    <tr>
                        <th>Full Name:</th>
                        <td><?php echo $student_info['full_name']; ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo $student_info['email']; ?></td>
                    </tr>
                    <tr>
                        <th>Student ID:</th>
                        <td><?php echo $student_id; ?></td>
                    </tr>
                </table>
            </div>
            <!-- Exam Info -->
            <div class="info-box">
                <h3>Exam Info</h3>
                <table>
                    <tr>
                        <th>Organization:</th>
                        <td><?php echo $exam_info['organization']; ?></td>
                    </tr>
                    <tr>
                        <th>Subject:</th>
                        <td><?php echo $exam_info['subject']; ?></td>
                    </tr>
                    <tr>
                        <th>Exam ID:</th>
                        <td><?php echo $exam_id; ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Result Summary -->
        <div class="result-summary">
            <h3>Exam Result</h3>
            <table>
                <tr>
                    <th>Total Questions</th>
                    <td><?php echo $total_questions; ?></td>
                </tr>
                <tr>
                    <th>Attempted</th>
                    <td><?php echo $attempted_questions; ?></td>
                </tr>
                <tr>
                    <th>Unattempted</th>
                    <td><?php echo $unattempted_questions; ?></td>
                </tr>
                <tr>
                    <th>Correct</th>
                    <td><?php echo $correct_answers; ?></td>
                </tr>
                <tr>
                    <th>Wrong</th>
                    <td><?php echo $wrong_answers; ?></td>
                </tr>
                <tr>
                    <th>Total Marks</th>
                    <td><?php echo $total_marks; ?></td>
                </tr>
                <tr>
                    <th>Obtained Marks</th>
                    <td><?php echo $obtained_marks; ?></td>
                </tr>
            </table>
        </div>

        <!-- Button for Immediate Redirect -->
        <div class="button-container">
            <button onclick="window.location.href='dashboard.php'">Go to Dashboard</button>
        </div>
    </div>
</body>
</html>