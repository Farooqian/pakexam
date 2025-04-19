<?php
include 'session_handler.php';
include 'db_connection.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = '';

if (isset($_POST['upload']) && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    if (($handle = fopen($file, "r")) !== false) {
        $row = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            if ($row === 0) { $row++; continue; } // Skip header

            if (count($data) == 8) {
                list($org, $subj, $q, $a, $b, $c, $d, $correct) = array_map('trim', $data);

                if (in_array($correct, ['A','B','C','D'])) {
                    $stmt = $conn->prepare("INSERT INTO questions (organization, subject, question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssssss", $org, $subj, $q, $a, $b, $c, $d, $correct);
                    $stmt->execute();
                }
            }
            $row++;
        }
        fclose($handle);
        $message = "CSV file uploaded successfully! $row rows processed.";
    } else {
        $message = "Error opening the file.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Questions | Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef;
            padding: 30px;
        }

        .form-box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            margin: auto;
            text-align: center;
        }

        h2 {
            color: #333;
        }

        input[type=file] {
            margin-top: 20px;
            padding: 8px;
        }

        button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #1E90FF;
            color: white;
            border: none;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0066cc;
        }

        .message {
            margin-top: 20px;
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="form-box">
    <h2>Upload Questions (CSV)</h2>
    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="csv_file" accept=".csv" required><br>
        <button type="submit" name="upload">Upload File</button>
    </form>

    <p><strong>CSV Format:</strong><br>
        organization, subject, question_text, option_a, option_b, option_c, option_d, correct_answer
    </p>
</div>

</body>
</html>
