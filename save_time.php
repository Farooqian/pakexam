<?php
include 'db_connection.php';

$exam_id = $_POST['exam_id'];
$remaining_time = $_POST['remaining_time'];

$end_time = time() + intval($remaining_time); // new end time
$query = $conn->prepare("UPDATE exam_progress SET end_time = FROM_UNIXTIME(?) WHERE exam_id = ?");
$query->bind_param("is", $end_time, $exam_id);
$query->execute();
