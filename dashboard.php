<?php
session_start();
include('db.php');

// Simple session check for admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?role=admin");
    exit;
}

// Fetch students
$students = $conn->query("SELECT id, name, email FROM student");

// Fetch courses
$courses = $conn->query("SELECT id, name FROM course");

// Fetch enrollments joined with students, courses and grades
$enrollments = $conn->query("
    SELECT 
        e.id AS enrollment_id,
        s.id AS student_id,
        s.name AS student_name,
        c.id AS course_id,
        c.name AS course_name,
        g.grade
    FROM enrollment e
    LEFT JOIN student s ON e.student_id = s.id
    LEFT JOIN course c ON e.course_id = c.id
    LEFT JOIN grades g ON e.student_id = g.student_id AND e.course_id = g.course_id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Student Portal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f6f8fa;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h1 {
            margin-bottom: 20px;
            color: #222;
            border-bottom: 2px solid #0073e6;
            padding-bottom: 8px;
        }
        h2 {
            margin-top: 40px;
            margin-bottom: 12px;
            color: #0073e6;
            border-bottom: 1px solid #ccc;
            padding-bottom: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: #fff;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        th, td {
            text-align: left;
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #0073e6;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f1f9ff;
        }
        .logout {
            float: right;
            margin-top: -40px;
            margin-bottom: 20px;
        }
        .logout a {
            text-decoration: none;
            color: #0073e6;
            font-weight: bold;
        }
        .logout a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h1>Admin Dashboard</h1>
<div class="logout"><a href="logout.php">Logout</a></div>

<h2>Registered Students</h2>
<table>
    <thead>
        <tr>
            <th>ID</th><th>Name</th><th>Email</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $students->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<h2>Courses</h2>
<table>
    <thead>
        <tr>
            <th>ID</th><th>Course Name</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $courses->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<h2>Enrollments and Grades</h2>
<table>
    <thead>
        <tr>
            <th>Enrollment ID</th>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Course ID</th>
            <th>Course Name</th>
            <th>Grade</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $enrollments->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['enrollment_id']) ?></td>
            <td><?= htmlspecialchars($row['student_id']) ?></td>
            <td><?= htmlspecialchars($row['student_name']) ?></td>
            <td><?= htmlspecialchars($row['course_id']) ?></td>
            <td><?= htmlspecialchars($row['course_name']) ?></td>
            <td><?= htmlspecialchars($row['grade'] ?? 'N/A') ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
