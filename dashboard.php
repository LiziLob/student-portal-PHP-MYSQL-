<?php
session_start();
include('db.php');

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?role=admin");
    exit;
}

// Delete course
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_course_id'])) {
    $delete_course_id = intval($_POST['delete_course_id']);

    // Delete grades related to the course
    $stmtDelGrades = $conn->prepare("DELETE FROM grades WHERE course_id = ?");
    $stmtDelGrades->bind_param("i", $delete_course_id);
    $stmtDelGrades->execute();

    // Delete enrollments related to the course
    $stmtDelEnrollments = $conn->prepare("DELETE FROM enrollment WHERE course_id = ?");
    $stmtDelEnrollments->bind_param("i", $delete_course_id);
    $stmtDelEnrollments->execute();

    // Delete the course itself
    $stmtDelCourse = $conn->prepare("DELETE FROM course WHERE id = ?");
    $stmtDelCourse->bind_param("i", $delete_course_id);
    $stmtDelCourse->execute();

    header("Location: dashboard.php");
    exit;
}

// Add student to course + grade
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_id'], $_POST['course_id'], $_POST['grade'])) {
    $student_id = intval($_POST['student_id']);
    $course_id = intval($_POST['course_id']);
    $grade = trim($_POST['grade']);

    // Prevent duplicate enrollment
    $check = $conn->prepare("SELECT * FROM enrollment WHERE student_id = ? AND course_id = ?");
    $check->bind_param("ii", $student_id, $course_id);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows === 0) {
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

// Add new course
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_course_name'])) {
    $course_name = trim($_POST['new_course_name']);
    if (!empty($course_name)) {
        $stmt = $conn->prepare("INSERT INTO course (name) VALUES (?)");
        $stmt->bind_param("s", $course_name);
        $stmt->execute();
    }

    header("Location: dashboard.php");
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
            color: red;
            font-weight: bold;
        }
        form {
            background: #fff;
            padding: 15px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        form input, form select {
            padding: 8px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        form button {
            padding: 8px 15px;
            background: #0073e6;
            color: white;
            border: none;
            cursor: pointer;
        }
        form button:hover {
            background: #005bb5;
        }
        form button.delete-btn {
            background: red;
            padding: 5px 10px;
            font-size: 0.9em;
        }
        form button.delete-btn:hover {
            background: darkred;
        }
    </style>
</head>
<body>

<h1>Admin Dashboard</h1>
<div class="logout"><a href="logout.php">Logout</a></div>

<h2>Add Student to Course + Grade</h2>
<form method="POST" action="dashboard.php">
    <label for="student_id">Student:</label>
    <select name="student_id" required>
        <?php
        $studentOptions = $conn->query("SELECT id, name FROM student");
        while ($s = $studentOptions->fetch_assoc()):
        ?>
        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
        <?php endwhile; ?>
    </select>

    <label for="course_id">Course:</label>
    <select name="course_id" required>
        <?php
        $courseOptions = $conn->query("SELECT id, name FROM course");
        while ($c = $courseOptions->fetch_assoc()):
        ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endwhile; ?>
    </select>

    <label for="grade">Grade:</label>
    <input type="text" name="grade" placeholder="e.g. A+" required />
    <button type="submit">Assign</button>
</form>

<h2>Add New Course</h2>
<form action="dashboard.php" method="POST">
    <input type="text" name="new_course_name" required placeholder="Enter course name..." />
    <button type="submit">Add Course</button>
</form>

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
            <th>ID</th><th>Course Name</th><th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $courseTable = $conn->query("SELECT id, name FROM course");
        while ($row = $courseTable->fetch_assoc()):
        ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td>
                <form method="POST" action="dashboard.php" onsubmit="return confirm('Are you sure you want to delete this course?');" style="display:inline;">
                    <input type="hidden" name="delete_course_id" value="<?= $row['id'] ?>">
                    <button type="submit" class="delete-btn">Delete</button>
                </form>
            </td>
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
