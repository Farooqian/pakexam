<?php
session_start();
unset($_SESSION['exam_id']);
unset($_SESSION['logout_timer_start']);
unset($_SESSION['results_saved']);
// Add more if needed
echo "Session cleared";
?>
