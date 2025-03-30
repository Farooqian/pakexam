<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["csv_file"])) {
    $file = $_FILES["csv_file"]["tmp_name"];

    if (($handle = fopen($file, "r")) !== FALSE) {
        fgetcsv($handle); // Skip the header row

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $full_name = mysqli_real_escape_string($conn, $data[0]);
            $email = mysqli_real_escape_string($conn, $data[1]);
            $phone = mysqli_real_escape_string($conn, $data[2]);
            $dob = date("Y-m-d", strtotime(str_replace("-", "/", $data[3]))); // Convert DD-MM-YYYY to YYYY-MM-DD
            $role = mysqli_real_escape_string($conn, $data[4]);
            $password = password_hash(mysqli_real_escape_string($conn, $data[5]), PASSWORD_BCRYPT); // Hash password

            $query = "INSERT INTO users (full_name, email, phone, dob, role, password) VALUES 
                      ('$full_name', '$email', '$phone', '$dob', '$role', '$password')";

            mysqli_query($conn, $query);
        }
        fclose($handle);
        echo "CSV file imported successfully!";
    } else {
        echo "Error opening file.";
    }
} else {
    echo "No file uploaded.";
}
?>
