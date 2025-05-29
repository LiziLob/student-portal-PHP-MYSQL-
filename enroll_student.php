<?php
session_start();
include('db.php');

// Ensure only admin can access
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php?role=admin");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = (int)$_POST['student_id'];
    $course_id = (int)$_POST['course_id'];
    $grade = trim($_POST['grade']);

    // Enroll student if not already enrolled
    $stmt = $conn->prepare("SELECT 1 FROM enrollment WHERE student_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $student_id, $course_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $enroll = $conn->prepare("INSERT INTO enrollment (student_id, course_id) VALUES (?, ?)");
        $enroll->bind_param("ii", $student_id, $course_id);
        $enroll->execute();
        $enroll->close();
    }
    $stmt->close();

    // Insert or update grade
    $stmt = $conn->prepare("SELECT 1 FROM grades WHERE student_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $student_id, $course_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $query = "UPDATE grades SET grade = ? WHERE student_id = ? AND course_id = ?";
    } else {
        $query = "INSERT INTO grades (grade, student_id, course_id) VALUES (?, ?, ?)";
    }
    $stmt->close();

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $grade, $student_id, $course_id);
    $stmt->execute();
    $stmt->close();

    header("Location: dashboard.php");
    exit;
}
?>
