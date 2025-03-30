<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db_connection.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
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

// Fetch organizations
$org_query = "SELECT DISTINCT organization FROM MCQs";
$org_result = $conn->query($org_query);

$organizations = [];
if ($org_result->num_rows > 0) {
    while ($row = $org_result->fetch_assoc()) {
        $organizations[] = $row['organization'];
    }
}

function fetchSubjects($organization, $conn) {
    $stmt = $conn->prepare("SELECT DISTINCT subject FROM MCQs WHERE organization = ?");
    $stmt->bind_param("s", $organization);
    $stmt->execute();
    $result = $stmt->get_result();

    $subjects = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $subjects[] = $row['subject'];
        }
    }
    return $subjects;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['organization'])) {
    $selected_org = $_POST['organization'];
    $subjects = fetchSubjects($selected_org, $conn);
    echo json_encode($subjects);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Exam | ProjectD</title>
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

        .profile-box {
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="container">
    <div class="profile-box">
        <p><strong>Full Name:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Registration ID:</strong> <?= htmlspecialchars($user['registration_number']) ?></p>
    </div>

    <h2>Select Organization and Subject</h2>
    <form id="selectForm" action="confirm.php" method="GET"> <!-- Updated action to confirm.php -->
        <label for="organization">Organization:</label>
        <select name="organization" id="organization" required>
            <option value="" disabled selected>Select Organization</option>
            <?php foreach ($organizations as $org) : ?>
                <option value="<?= htmlspecialchars($org) ?>"><?= htmlspecialchars($org) ?></option>
            <?php endforeach; ?>
        </select>
        
        <label for="subject">Subject:</label>
        <select name="subject" id="subject" required>
            <option value="" disabled selected>Select Subject</option>
        </select>
        
        <button type="submit" class="btn">Confirm Selection</button>
    </form>
</div>

<script>
    $(document).ready(function() {
        $("#organization").change(function() {
            var organization = $(this).val();
            $.post("select.php", { organization: organization }, function(data) {
                var subjects = JSON.parse(data);
                var subjectSelect = $("#subject");
                subjectSelect.empty();
                if (subjects.length > 0) {
                    $.each(subjects, function(index, subject) {
                        subjectSelect.append(new Option(subject, subject));
                    });
                } else {
                    subjectSelect.append(new Option("No subjects for this organization", ""));
                }
            });
        });
    });
</script>

</body>
</html>