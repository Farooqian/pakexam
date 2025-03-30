<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db_connection.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['organization']) || !isset($_GET['subject'])) {
    header("Location: select.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$query = "SELECT full_name, email, registration_number FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found!";
    exit();
}

$organization = htmlspecialchars($_GET['organization']);
$subject = htmlspecialchars($_GET['subject']);
$session_id = session_id();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Details | ProjectD</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #1E90FF, #FF69B4);
            color: white;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 800px;
            margin: 50px auto;
            color: black;
        }

        .details-box {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
            text-align: center;
            font-size: 16px;
            margin-top: 20px;
            background: #1E90FF;
            color: white;
        }

        .btn:hover {
            background: #FF69B4;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Confirm Your Details</h2>
    <div class="details-box">
        <p><strong>Name:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Student ID:</strong> <?= htmlspecialchars($user['registration_number']) ?></p>
        <p><strong>Subject:</strong> <?= $subject ?></p>
        <p><strong>Organization:</strong> <?= $organization ?></p>
        <p><strong>Session ID:</strong> <?= $session_id ?></p>
    </div>

    <form action="exam.php" method="GET">
        <input type="hidden" name="organization" value="<?= $organization ?>">
        <input type="hidden" name="subject" value="<?= $subject ?>">
        <button type="submit" class="btn">Confirm and Start Exam</button>
    </form>
</div>

</body>
</html>