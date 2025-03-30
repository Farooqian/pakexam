<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_regid = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($email_or_regid) || empty($password)) {
        $_SESSION['error'] = "All fields are required!";
        header("Location: login.php");
        exit();
    }

    // Prepare SQL Query (support both email & registration ID)
    $query = "SELECT id, full_name, email, password, registration_number FROM users WHERE email = ? OR registration_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email_or_regid, $email_or_regid);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["full_name"];
            $_SESSION["user_email"] = $user["email"];
            $_SESSION["user_regid"] = $user["registration_number"];

            header("Location: dashboard.php"); // Redirect to dashboard
            exit();
        } else {
            $_SESSION['error'] = "Invalid password!";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "No account found with that email or registration ID!";
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
