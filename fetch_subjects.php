<?php
include 'db_connection.php';

if (isset($_POST['organization'])) {
    $organization = $_POST['organization'];

    $query = "SELECT DISTINCT subject FROM MCQs WHERE organization = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $organization);
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<option value="">Select Subject</option>';
    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['subject']}'>{$row['subject']}</option>";
    }
}
?>
