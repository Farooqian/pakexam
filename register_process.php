<?php
// Include database connection
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $dob = $_POST['dob'];
    $role = $_POST['role'];
    $registration_number = $_POST['registration_number'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $terms = isset($_POST['terms']) ? true : false;

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "Passwords do not match!";
        exit();
    }

    // Password validation (strong password check)
    $password_pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";

    if (!preg_match($password_pattern, $password)) {
        echo "Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one digit, and one special character (e.g., @, $, %, *).";
        exit();
    }

    // Hash the password before saving to the database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists in the database
    $email_check_query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($email_check_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "Email is already registered!";
        exit();
    }

    // Database insertion
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, dob, role, registration_number, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $full_name, $email, $phone, $dob, $role, $registration_number, $hashed_password);

    if ($stmt->execute()) {
        echo "Registration successful!";
        // Redirect to login page or dashboard
        header("Location: login.php");
        exit();
    } else {
        echo "Error during registration!";
    }

    // Close database connection
    $stmt->close();
    $conn->close();
}
?>
