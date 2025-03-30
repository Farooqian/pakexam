<?php
include 'db_connection.php';

if (isset($_POST['email'])) {
    $email = $_POST['email'];
    
    // Sanitize input
    $email = mysqli_real_escape_string($conn, $email);

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo "exists";
    } else {
        echo "available";
    }
}

mysqli_close($conn);
?>
