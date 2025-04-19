<?php
// Connect to the database
include 'db_connection.php';

// Fetch the top 10 students by score
$query = "
    SELECT u.full_name, u.registration_number, r.exam_id, r.score
    FROM results r
    JOIN users u ON r.student_id = u.registration_number
    ORDER BY r.score DESC
    LIMIT 10
";

$result = $conn->query($query);

// Start the HTML page with some basic structure
echo "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Top 10 Students</title>
    <link href='https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap' rel='stylesheet'>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #4CAF50;
            color: white;
            padding: 15px 0;
            text-align: center;
        }
        h2 {
            font-size: 28px;
            margin-top: 30px;
            color: #333;
        }
        table {
            width: 80%;
            margin: 30px auto;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        table th, table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #4CAF50;
            color: white;
        }
        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        table tr:hover {
            background-color: #f1f1f1;
        }
        .rank {
            font-weight: bold;
            color: #4CAF50;
        }
        .student-name {
            font-weight: 500;
        }
        .exam-id {
            color: #555;
        }
        .score {
            font-weight: 600;
            color: #ff5722;
        }
        .no-data {
            text-align: center;
            font-size: 18px;
            color: #ff5722;
            padding: 30px;
        }
        footer {
            text-align: center;
            padding: 20px;
            background-color: #333;
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <h1>Top 10 Students</h1>
    </header>";

if ($result->num_rows > 0) {
    // Output the top 10 students in a table
    echo "<h2>Congratulations to the Top 10 Students!</h2>";
    echo "<table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Full Name</th>
                    <th>Registration Number</th>
                    <th>Exam ID</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>";

    $rank = 1;
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td class='rank'>" . $rank++ . "</td>
                <td class='student-name'>" . $row['full_name'] . "</td>
                <td>" . $row['registration_number'] . "</td>
                <td class='exam-id'>" . $row['exam_id'] . "</td>
                <td class='score'>" . $row['score'] . "</td>
            </tr>";
    }

    echo "</tbody></table>";
} else {
    echo "<div class='no-data'>No data found for top students!</div>";
}

echo "
    <footer>
        <p>&copy; 2025 Rayzon Pvt Ltd | All rights reserved</p>
    </footer>
</body>
</html>
";
?>
