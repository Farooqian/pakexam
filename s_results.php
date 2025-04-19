<?php
require_once "db_connection.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['registration_number'])) {
    header("Location: login.php");
    exit();
}

$registration_number = $_SESSION['registration_number'];

// Get student info
$stmt_user = $conn->prepare("SELECT * FROM users WHERE registration_number = ?");
$stmt_user->bind_param("s", $registration_number);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_info = $result_user->fetch_assoc();

// Get exam results
$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = ?");
$stmt->bind_param("s", $registration_number);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Exam Results - Rayzon</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #f5f7fa, #e1f5fe);
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 30px;
            background: rgb(220, 251, 237);
            border-radius: 12px;
            box-shadow: 0 10px 50px rgb(189, 1, 252);
            border-left: 8px  solid #fc00f8;
            border-right: 8px  solid #fc00f8;
            border-top: 8px  solid #fc00f8;
            border-bottom: 8px  solid #fc00f8;
        }

        h2 {
            text-align: center;
            color: #00796b;
            margin-bottom: 20px;
        }

        .info {
            margin-bottom: 30px;
            font-size: 16px;
            color: #333;
            border-left: 3px  solid #2e5a1a;
            border-right: 3px  solid #2e5a1a;
            border-top: 3px  solid #2e5a1a;
            border-bottom: 3px  solid #2e5a1a;
        }

        .info p {
            margin: 6px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: center;
        }

        th {
            background: linear-gradient(to right,rgb(186, 83, 245),rgb(116, 5, 128));
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f1fdfd;
        }

        .download-buttons {
            margin-top: 30px;
            text-align: center;
        }

        .btn-download {
            display: inline-block;
            padding: 10px 20px;
            margin: 8px;
            background: linear-gradient(135deg, #ff6f00, #f57c00);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-download:hover {
            background: linear-gradient(135deg, #e65100, #bf360c);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .no-result {
            text-align: center;
            padding: 30px;
            color: #999;
        }

        .navigation-links {
            margin-top: 40px;
            text-align: center;
        }
        .view-btn {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    background: linear-gradient(45deg, #42a5f5, #1e88e5);
    color: white;
    font-weight: bold;
    text-decoration: none;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    transition: background 0.3s ease;
}
.view-btn:hover {
    background: linear-gradient(45deg, #1976d2, #0d47a1);
}

        .navigation-links a {
            text-decoration: none;
            padding: 10px 20px;
            background-color: #00796b;
            color: white;
            border-radius: 20px;
            margin: 0 10px;
            font-weight: bold;
            transition: background 0.3s;
        }

        .navigation-links a:hover {
            background-color: #004d40;
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Your Exam Results <p><?= htmlspecialchars($user_info['full_name']) ?></p></h2>

        <?php if ($user_info): ?>
            <div class="info">
                <p><strong>Full Name:</strong> <?= htmlspecialchars($user_info['full_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user_info['email']) ?></p>
                <p><strong>Registration ID:</strong> <?= htmlspecialchars($user_info['registration_number']) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Exam ID</th>
                        <th>Organization</th>
                        <th>Subject</th>
                        <th>Total</th>
                        <th>Attempted</th>
                        <th>Correct</th>
                        <th>Wrong</th>
                        <th>Score</th>
                        <th>Date</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['exam_id']) ?></td>
                        <td><?= htmlspecialchars($row['organization']) ?></td>
                        <td><?= htmlspecialchars($row['subject']) ?></td>
                        <td><?= htmlspecialchars($row['total_questions']) ?></td>
                        <td><?= htmlspecialchars($row['attempted_questions']) ?></td>
                        <td><?= htmlspecialchars($row['correct_answers']) ?></td>
                        <td><?= htmlspecialchars($row['wrong_answers']) ?></td>
                        <td><?= htmlspecialchars($row['score']) ?></td>
                        <td><?= htmlspecialchars($row['result_date']) ?></td>
                        <td>
    <a href="question_details.php?exam_id=<?= htmlspecialchars($row['exam_id']) ?>" class="view-btn">
        🔍 View
    </a>
</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="download-buttons">
            <a href="download_csv.php" class="btn-download">
    <img src="icons/csv.png" alt="CSV" style="height:20px; vertical-align:middle; margin-right:8px;">
    Download CSV
</a>
<a href="download_excel.php" class="btn-download">
    <img src="icons/excel.png" alt="Excel" style="height:20px; vertical-align:middle; margin-right:8px;">
    Download Excel
</a>

<a href="download_pdf.php" class="btn-download">
    <img src="icons/pdf.png" alt="PDF" style="height:20px; vertical-align:middle; margin-right:8px;">
    Download PDF
</a>

<a href="download_word.php" class="btn-download">
    <img src="icons/word.png" alt="Word" style="height:20px; vertical-align:middle; margin-right:8px;">
    Download Word
</a>

            </div>
        <?php else: ?>
            <div class="no-result">No exam results found.</div>

            <div class="download-buttons">
                <a href="dashboard.php" class="btn-download">Dashboard</a>
                <a href="javascript:history.back()" class="btn-download">Back</a>
            </div>
        <?php endif; ?>

        <div class="navigation-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="javascript:history.back()">Back</a>
        </div>
    </div>
</body>
</html>
