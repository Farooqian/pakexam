<?php
// Set PHP time zone to UTC
date_default_timezone_set('UTC');

// Database connection
include 'db_connection.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch active exams grouped by exam_id
$sql = "
SELECT 
    u.full_name AS student_name,
    u.email AS student_email,
    u.registration_number AS student_id,
    ep.exam_id,
    ep.organization,
    ep.subject,
    MIN(ep.start_time) AS start_time, -- Earliest start time for the exam
    MAX(ep.end_time) AS end_time,    -- Latest end time for the exam
    TIMESTAMPDIFF(MINUTE, MIN(ep.start_time), UTC_TIMESTAMP()) AS time_elapsed,
    COUNT(CASE WHEN ep.selected_answer IS NOT NULL THEN 1 END) AS attempted_questions, -- Count of attempted questions
    COUNT(CASE WHEN ep.selected_answer = ep.correct_answer THEN 1 END) AS correct_answers -- Count of correct answers
FROM 
    exam_progress ep
JOIN 
    users u ON ep.student_id = u.registration_number
GROUP BY 
    ep.exam_id, u.full_name, u.email, u.registration_number, ep.organization, ep.subject
HAVING 
    UTC_TIMESTAMP() BETWEEN MIN(ep.start_time) AND MAX(ep.end_time)
ORDER BY 
    start_time ASC;
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="60"> <!-- Refresh page every 60 seconds -->
    <title>Active Exams</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .no-data {
            text-align: center;
            color: red;
        }
    </style>
</head>
<body>
    <h1>Active Exams</h1>
    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Email</th>
                <th>Student ID</th>
                <th>Exam ID</th>
                <th>Organization</th>
                <th>Subject</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Time Elapsed (Minutes)</th>
                <th>Attempted Questions</th>
                <th>Correct Answers</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['student_email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['student_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['exam_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['organization']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['start_time']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['end_time']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['time_elapsed']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['attempted_questions']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['correct_answers']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='11' class='no-data'>No active exams found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>

<?php
$conn->close();
?>