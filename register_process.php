<?php
include 'db_connection.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone']; // Includes country code
    $dob = $_POST['dob'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
    // Convert DOB format from DD-MM-YYYY to YYYY-MM-DD
    $dob_parts = explode("-", $dob);
    if (count($dob_parts) == 3) {
        $dob = $dob_parts[2] . "-" . $dob_parts[1] . "-" . $dob_parts[0];
    } else {
        die("<p style='color: red; text-align: center;'>❌ Invalid Date Format</p>");
    }

    // Generate Registration Number (YYMM100001 format)
    $year = date('y');
    $month = date('m');
    $query = "SELECT COUNT(*) AS count FROM users WHERE registration_number LIKE '$year$month%'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $count = $row['count'] + 1;
    $registration_number = $year . $month . str_pad(100000 + $count, 6, "0", STR_PAD_LEFT);

    // Check if Email Already Exists
    $check_email_query = "SELECT email FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_email_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo "<p style='color: blue; text-align: center;'>❌ Email already registered!</p>";
        exit;
    }
    $stmt->close();

    // Insert user data
    $sql = "INSERT INTO users (full_name, email, phone, dob, role, registration_number, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $full_name, $email, $phone, $dob, $role, $registration_number, $password);

    if ($stmt->execute()) {
        // Display success message in a nice format
        echo "
        <div style='max-width: 600px; margin: 50px auto; padding: 20px; text-align: center; background-color: #f3f4f6; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);'>
            <h2 style='color: green;'>🎉 Registration Successful!</h2>
            <p><strong>Registration ID:</strong> <span style='color: blue; font-weight: bold;'>$registration_number</span></p>
            <p>Congratulations, <strong>$full_name</strong>! Your account has been created successfully.</p>
            <hr>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Date of Birth:</strong> $dob</p>
            <p><strong>Role:</strong> $role</p>
            
            <hr>
            <p>📧 A confirmation email has been sent to your registered email.</p>
            <a href='login.php' style='display: inline-block; padding: 10px 20px; color: white; background-color: #007bff; text-decoration: none; border-radius: 5px;'>Go to Login</a>
        </div>";
    } else {
        echo "<p style='color: red; text-align: center;'>❌ Error: " . $conn->error . "</p>";
    }

    $stmt->close();
    $conn->close();
}
?>
