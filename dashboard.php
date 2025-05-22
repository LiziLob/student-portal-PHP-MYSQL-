<?php
session_start();
if (!isset($_SESSION['student_id'])) {
  header("Location: login.php");
  exit;
}
?>

<h2>Welcome, <?= $_SESSION['student_name'] ?></h2>
<p><a href="logout.php">Logout</a></p>
