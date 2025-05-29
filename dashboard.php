<?php
session_start();
require 'db.php';

// Restrict access to admin only
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php?role=admin");
    exit;
}


// Delete a course and related records
if ($_POST['delete_course_id'] ?? false) {
    $courseId = (int)$_POST['delete_course_id'];

    $stmt1 = $conn->prepare("DELETE FROM grades WHERE course_id = ?");
    if (!$stmt1) die("Prepare failed: " . $conn->error);
    $stmt1->bind_param("i", $courseId);
    $stmt1->execute();

    $stmt2 = $conn->prepare("DELETE FROM enrollment WHERE course_id = ?");
    if (!$stmt2) die("Prepare failed: " . $conn->error);
    $stmt2->bind_param("i", $courseId);
    $stmt2->execute();

    $stmt3 = $conn->prepare("DELETE FROM course WHERE id = ?");
    if (!$stmt3) die("Prepare failed: " . $conn->error);
    $stmt3->bind_param("i", $courseId);
    $stmt3->execute();

    header("Location: dashboard.php");
    exit;
}

// Add new course
if ($_POST['new_course_name'] ?? false) {
    $name = trim($_POST['new_course_name']);
    if ($name) {
        $stmt = $conn->prepare("INSERT INTO course (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
    }
    header("Location: dashboard.php");
    exit;
}

// Enroll student and assign/update grade
if ($_POST['student_id'] ?? false && $_POST['course_id'] ?? false && $_POST['grade'] ?? false) {
    $studentId = (int)$_POST['student_id'];
    $courseId = (int)$_POST['course_id'];
    $grade = trim($_POST['grade']);

    // Enroll student if not already
    $exists = $conn->prepare("SELECT id FROM enrollment WHERE student_id = ? AND course_id = ?");
    $exists->bind_param("ii", $studentId, $courseId);
    $exists->execute();
    if ($exists->get_result()->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO enrollment (student_id, course_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $studentId, $courseId);
        $stmt->execute();
    }

    // Update or insert grade
    $gradeStmt = $conn->prepare("SELECT id FROM grades WHERE student_id = ? AND course_id = ?");
    $gradeStmt->bind_param("ii", $studentId, $courseId);
    $gradeStmt->execute();
    if ($gradeStmt->get_result()->num_rows > 0) {
        $update = $conn->prepare("UPDATE grades SET grade = ? WHERE student_id = ? AND course_id = ?");
        $update->bind_param("sii", $grade, $studentId, $courseId);
        $update->execute();
    } else {
        $insert = $conn->prepare("INSERT INTO grades (student_id, course_id, grade) VALUES (?, ?, ?)");
        $insert->bind_param("iis", $studentId, $courseId, $grade);
        $insert->execute();
    }

    header("Location: dashboard.php");
    exit;
}

// ========== Fetch Data for Display ==========

$students = $conn->query("SELECT id, name, email FROM student");
$courses = $conn->query("SELECT id, name FROM course");
$enrollments = $conn->query("
    SELECT e.id AS enrollment_id, s.id AS student_id, s.name AS student_name,
           c.id AS course_id, c.name AS course_name, g.grade
    FROM enrollment e
    LEFT JOIN student s ON e.student_id = s.id
    LEFT JOIN course c ON e.course_id = c.id
    LEFT JOIN grades g ON e.student_id = g.student_id AND e.course_id = g.course_id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Student Portal</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        body { font-family: Arial; padding: 20px; background: #f6f8fa; color: #333; }
        h1, h2 { color: #0073e6; border-bottom: 1px solid #ccc; }
        h1 { border-color: #0073e6; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; background: white; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; }
        th { background: #0073e6; color: white; }
        tr:hover { background: #f1f9ff; }
        form { background: white; padding: 15px; margin-bottom: 20px; }
        input, select, button { padding: 8px; margin: 5px 0; }
        button { background: #0073e6; color: white; border: none; }
        button:hover { background: #005bb5; }
        .delete-btn { background: red; font-size: 0.9em; }
        .delete-btn:hover { background: darkred; }
        .logout { float: right; margin-top: -40px; }
        .logout a { color: red; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<h1>Admin Dashboard</h1>
<div class="logout"><a href="logout.php">Logout</a></div>

<!-- Assign Grade Form -->
<h2>Assign Grade to Student</h2>
<form method="POST">
    <label>Student:
        <select name="student_id" required>
            <?php foreach ($conn->query("SELECT id, name FROM student") as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Course:
        <select name="course_id" required>
            <?php foreach ($conn->query("SELECT id, name FROM course") as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Grade:
        <input type="text" name="grade" required placeholder="e.g. A+" />
    </label>
    <button type="submit">Assign</button>
</form>

<!-- Add Course -->
<h2>Add New Course</h2>
<form method="POST">
    <input type="text" name="new_course_name" required placeholder="Course name..." />
    <button type="submit">Add Course</button>
</form>

<!-- Students Table -->
<h2>Registered Students</h2>
<table>
    <tr><th>ID</th><th>Name</th><th>Email</th></tr>
    <?php while ($row = $students->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<!-- Courses Table -->
<h2>Courses</h2>
<table>
    <tr><th>ID</th><th>Course Name</th><th>Action</th></tr>
    <?php foreach ($courses as $row): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td>
            <form method="POST" onsubmit="return confirm('Delete this course?');" style="display:inline;">
                <input type="hidden" name="delete_course_id" value="<?= $row['id'] ?>">
                <button type="submit" class="delete-btn">Delete</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Enrollments Table -->
<h2>Enrollments and Grades</h2>
<table>
    <tr>
        <th>Enrollment ID</th><th>Student ID</th><th>Name</th>
        <th>Course ID</th><th>Course</th><th>Grade</th>
    </tr>
    <?php while ($row = $enrollments->fetch_assoc()): ?>
    <tr>
        <td><?= $row['enrollment_id'] ?></td>
        <td><?= $row['student_id'] ?></td>
        <td><?= htmlspecialchars($row['student_name']) ?></td>
        <td><?= $row['course_id'] ?></td>
        <td><?= htmlspecialchars($row['course_name']) ?></td>
        <td><?= htmlspecialchars($row['grade'] ?? 'N/A') ?></td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
