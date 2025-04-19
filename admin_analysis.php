<?php
// Include database connection
include 'db_connection.php';

// Query to get overall stats
$overallStatsQuery = "
    SELECT 
        (SELECT COUNT(DISTINCT student_id) FROM results) AS total_students, 
        (SELECT COUNT(DISTINCT exam_id) FROM results) AS total_exams, 
        (SELECT AVG(score) FROM results) AS avg_score
";
$overallStatsResult = $conn->query($overallStatsQuery);
$overallStats = $overallStatsResult->fetch_assoc();

// Query to get the top 5 students based on score
$topStudentsQuery = "
    SELECT u.full_name, u.registration_number, r.exam_id, r.score
    FROM results r
    JOIN users u ON r.student_id = u.registration_number
    ORDER BY r.score DESC
    LIMIT 5
";
$topStudentsResult = $conn->query($topStudentsQuery);

// Query to get subject-wise stats
$subjectStatsQuery = "
    SELECT r.subject, COUNT(DISTINCT r.exam_id) AS total_exams, AVG(r.score) AS avg_score
    FROM results r
    GROUP BY r.subject
    ORDER BY avg_score DESC
";
$subjectStatsResult = $conn->query($subjectStatsQuery);

// Query to get organization-wise stats
$organizationStatsQuery = "
    SELECT r.organization, COUNT(DISTINCT r.exam_id) AS total_exams, AVG(r.score) AS avg_score
    FROM results r
    GROUP BY r.organization
    ORDER BY avg_score DESC
";
$organizationStatsResult = $conn->query($organizationStatsQuery);

// Start HTML output
echo "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Admin Analysis Dashboard</title>
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
        h1 {
            font-size: 36px;
            margin: 0;
        }
        h2 {
            font-size: 28px;
            margin-top: 30px;
            color: #333;
        }
        .container {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .section {
            margin-bottom: 40px;
        }
        table {
            width: 100%;
            margin: 20px 0;
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
        <h1>Admin Analysis Dashboard</h1>
        
    </header>
    
    <div class='container'>
        <div class='section'>
            <h2>Overall Stats</h2>
            <table>
                <tr>
                    <th>Total Students</th>
                    <th>Total Exams</th>
                    <th>Average Score</th>
                </tr>
                <tr>
                    <td>" . $overallStats['total_students'] . "</td>
                    <td>" . $overallStats['total_exams'] . "</td>
                    <td>" . number_format($overallStats['avg_score'], 2) . "%</td>
                </tr>
            </table>
        </div>
        
        <div class='section'>
            <h2>🏆 Top 5 Students</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Reg No.</th>
                        <th>Exam ID</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>";

                if ($topStudentsResult->num_rows > 0) {
                    while ($row = $topStudentsResult->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row['full_name'] . "</td>
                                <td>" . $row['registration_number'] . "</td>
                                <td>" . $row['exam_id'] . "</td>
                                <td>" . number_format($row['score'], 2) . "%</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='no-data'>No data found for top students!</td></tr>";
                }

echo "      </tbody>
            </table>
        </div>
        
        <div class='section'>
            <h2>📚 Subject-wise Stats</h2>
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Total Exams</th>
                        <th>Average Score</th>
                    </tr>
                </thead>
                <tbody>";

                if ($subjectStatsResult->num_rows > 0) {
                    while ($row = $subjectStatsResult->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row['subject'] . "</td>
                                <td>" . $row['total_exams'] . "</td>
                                <td>" . number_format($row['avg_score'], 2) . "%</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' class='no-data'>No data found for subject-wise stats!</td></tr>";
                }

echo "      </tbody>
            </table>
        </div>

        <div class='section'>
            <h2>🏢 Organization-wise Stats</h2>
            <table>
                <thead>
                    <tr>
                        <th>Organization</th>
                        <th>Total Exams</th>
                        <th>Average Score</th>
                    </tr>
                </thead>
                <tbody>";

                if ($organizationStatsResult->num_rows > 0) {
                    while ($row = $organizationStatsResult->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row['organization'] . "</td>
                                <td>" . $row['total_exams'] . "</td>
                                <td>" . number_format($row['avg_score'], 2) . "%</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' class='no-data'>No data found for organization-wise stats!</td></tr>";
                }

echo "      </tbody>
            </table>
            
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 Rayzon Pvt Ltd | All rights reserved</p>
    </footer>
</body>
</html>

";
?>
 <br>
    <!-- Redirect to Dashboard -->
    <button onclick="window.location.href='dashboard.php';">Go to Dashboard</button></br><br></br>