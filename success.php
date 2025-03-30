<?php
if (!isset($_GET['name'])) {
    header("Location: register.php");
    exit();
}

$name = $_GET['name'];
$email = $_GET['email'];
$phone = $_GET['phone'];
$dob = $_GET['dob'];
$role = $_GET['role'];
$reg_id = $_GET['reg_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            text-align: center;
            background: linear-gradient(to right, #00c6ff, #0072ff);
            color: #fff;
            margin: 0;
            padding: 0;
        }
        .container {
            background: rgba(255, 255, 255, 0.2);
            padding: 25px;
            border-radius: 10px;
            width: 350px;
            margin: 50px auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h2 {
            color: #ffeb3b;
        }
        .details {
            text-align: left;
            padding: 10px;
        }
        .btn {
            background: #ffcc00;
            color: #333;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #ffd633;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🎉 Congratulations, <?php echo $name; ?>! 🎉</h2>
        <p>You have successfully registered.</p>
        <div class="details">
            <p><strong>Email:</strong> <?php echo $email; ?></p>
            <p><strong>Phone:</strong> <?php echo $phone; ?></p>
            <p><strong>Date of Birth:</strong> <?php echo $dob; ?></p>
            <p><strong>Role:</strong> <?php echo ucfirst($role); ?></p>
            <p><strong>Registration ID:</strong> <?php echo $reg_id; ?></p>
        </div>
        <a href="login.php" class="btn">Go to Login</a>
    </div>
</body>
</html>
