<?php
session_start();
include('db.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'student') {
    header('Location: login.php?role=student');
    exit;
}

$username = $_SESSION['username'];

$query = "SELECT * FROM student WHERE username=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$student_id = $student['id'];

$sql = "SELECT c.course_name, g.grade
        FROM enrollment e
        JOIN course c ON e.course_id = c.id
        LEFT JOIN grades g ON g.enrollment_id = e.id
        WHERE e.student_id = ?";
$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("i", $student_id);
$stmt2->execute();
$courses = $stmt2->get_result();
?>

<!DOCTYPE html>
<html>
<head><title>Student Dashboard</title></head>
<body>
<h2>Welcome, <?php echo htmlspecialchars($student['fullname']); ?></h2>

<h3>Your Courses and Grades</h3>
<table border="1">
    <tr><th>Course</th><th>Grade</th></tr>
    <?php while ($row = $courses->fetch_assoc()) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['course_name']); ?></td>
        <td><?php echo htmlspecialchars($row['grade'] ?? 'N/A'); ?></td>
    </tr>
    <?php } ?>
</table>

<p><a href="logout.php">Logout</a></p>
</body>
</html>
