<?php
session_start();
include('db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?role=admin");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = intval($_POST['student_id']);
    $course_id = intval($_POST['course_id']);
    $grade = trim($_POST['grade']);

    // Prevent duplicate enrollment
    $check = $conn->prepare("SELECT * FROM enrollment WHERE student_id = ? AND course_id = ?");
    $check->bind_param("ii", $student_id, $course_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        // Insert into enrollment table
        $stmt1 = $conn->prepare("INSERT INTO enrollment (student_id, course_id) VALUES (?, ?)");
        $stmt1->bind_param("ii", $student_id, $course_id);
        $stmt1->execute();
    }

    // Insert or update grade
    $checkGrade = $conn->prepare("SELECT * FROM grades WHERE student_id = ? AND course_id = ?");
    $checkGrade->bind_param("ii", $student_id, $course_id);
    $checkGrade->execute();
    $resultGrade = $checkGrade->get_result();

    if ($resultGrade->num_rows > 0) {
        $update = $conn->prepare("UPDATE grades SET grade = ? WHERE student_id = ? AND course_id = ?");
        $update->bind_param("sii", $grade, $student_id, $course_id);
        $update->execute();
    } else {
        $insert = $conn->prepare("INSERT INTO grades (student_id, course_id, grade) VALUES (?, ?, ?)");
        $insert->bind_param("iis", $student_id, $course_id, $grade);
        $insert->execute();
    }

    header("Location: dashboard.php");
    exit;
}
?>
