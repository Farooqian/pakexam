<?php
// Include database connection
include 'db_connection.php';

// Query to get all unique organizations
$orgQuery = "SELECT DISTINCT organization FROM results";
$orgResult = $conn->query($orgQuery);

// Query to get all unique subjects
$subjectQuery = "SELECT DISTINCT subject FROM results";
$subjectResult = $conn->query($subjectQuery);

// Initialize variables for selected organization and subject
$selectedOrganization = '';
$selectedSubject = '';
$statsResult = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selectedOrganization = $_POST['organization'];
    $selectedSubject = $_POST['subject'];

    // Query to get stats based on selected organization and subject or all results
    if ($selectedOrganization == 'All' && $selectedSubject == 'All') {
        // Fetch all data for all organizations and subjects
        $statsQuery = "
            SELECT r.exam_id, r.score, r.student_id, u.full_name, r.organization, r.subject
            FROM results r
            JOIN users u ON r.student_id = u.registration_number
            ORDER BY r.score DESC
        ";
    } elseif ($selectedOrganization == 'All' && $selectedSubject != 'All') {
        // Fetch data for all organizations but filter by selected subject
        $statsQuery = "
            SELECT r.exam_id, r.score, r.student_id, u.full_name, r.organization, r.subject
            FROM results r
            JOIN users u ON r.student_id = u.registration_number
            WHERE r.subject = '$selectedSubject'
            ORDER BY r.score DESC
        ";
    } elseif ($selectedOrganization != 'All' && $selectedSubject == 'All') {
        // Fetch data for selected organization but filter by all subjects
        $statsQuery = "
            SELECT r.exam_id, r.score, r.student_id, u.full_name, r.organization, r.subject
            FROM results r
            JOIN users u ON r.student_id = u.registration_number
            WHERE r.organization = '$selectedOrganization'
            ORDER BY r.score DESC
        ";
    } else {
        // Fetch data based on both selected organization and subject
        $statsQuery = "
            SELECT r.exam_id, r.score, r.student_id, u.full_name, r.organization, r.subject
            FROM results r
            JOIN users u ON r.student_id = u.registration_number
            WHERE r.organization = '$selectedOrganization' AND r.subject = '$selectedSubject'
            ORDER BY r.score DESC
        ";
    }
    $statsResult = $conn->query($statsQuery);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Stats</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
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
        .form-section {
            margin-bottom: 30px;
            text-align: center;
        }
        .form-section select, .form-section button {
            padding: 10px;
            font-size: 16px;
            margin: 10px;
        }
        .stats-section {
            margin-top: 30px;
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
    </style>
</head>
<body>

    <header>
        <h1>Exam Stats - View by Organization & Subject</h1>
    </header>

    <div class="container">

        <!-- Dropdowns for Organization and Subject -->
        <div class="form-section">
            <form method="POST" action="">
                <select name="organization" required>
                    <option value="All">Select Organization (or All)</option>
                    <option value="All" <?php echo ($selectedOrganization == 'All') ? 'selected' : ''; ?>>All</option>
                    <?php while ($orgRow = $orgResult->fetch_assoc()) { ?>
                        <option value="<?php echo $orgRow['organization']; ?>" <?php echo ($orgRow['organization'] == $selectedOrganization) ? 'selected' : ''; ?>>
                            <?php echo $orgRow['organization']; ?>
                        </option>
                    <?php } ?>
                </select>

                <select name="subject" required>
                    <option value="All">Select Subject (or All)</option>
                    <option value="All" <?php echo ($selectedSubject == 'All') ? 'selected' : ''; ?>>All</option>
                    <?php while ($subjectRow = $subjectResult->fetch_assoc()) { ?>
                        <option value="<?php echo $subjectRow['subject']; ?>" <?php echo ($subjectRow['subject'] == $selectedSubject) ? 'selected' : ''; ?>>
                            <?php echo $subjectRow['subject']; ?>
                        </option>
                    <?php } ?>
                </select>

                <button type="submit">View Stats</button>
            </form>
        </div>

        <!-- Exam Stats Table -->
        <?php if ($statsResult && $statsResult->num_rows > 0): ?>
            <div class="stats-section">
                <h2>Stats for <?php echo ($selectedOrganization == 'All' ? 'All Organizations' : $selectedOrganization) . " - " . ($selectedSubject == 'All' ? 'All Subjects' : $selectedSubject); ?></h2>
                <table>
                    <thead>
                        <tr>
                            <th>Exam ID</th>
                            <th>Student Name</th>
                            <th>Score</th>
                            <th>Organization</th>
                            <th>Subject</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $statsResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['exam_id']; ?></td>
                                <td><?php echo $row['full_name']; ?></td>
                                <td><?php echo $row['score']; ?></td>

                                <td><?php echo $row['organization']; ?></td>
                                <td><?php echo $row['subject']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <div class="no-data">No data found for the selected organization and subject!</div>
        <?php endif; ?>
        <br>
    <!-- Redirect to Dashboard -->
    <button onclick="window.location.href='dashboard.php';">Go to Dashboard</button>
    </div>

</body>
</html>
