<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $to = $_POST['email'];
    $subject = "Important Notification";
    $message = "This is a message from the admin.";
    $headers = "From: mehr329@gmail.com";

    if (mail($to, $subject, $message, $headers)) {
        echo "Email sent successfully!";
    } else {
        echo "Failed to send email.";
    }
} else {
    header("Location: admin_online_users.php");
    exit();
}
?>