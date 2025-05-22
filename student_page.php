<?php
session_start();
include('db.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php?role=student');
    exit;
}

$studentEmail = $_SESSION['username'];

// Get student info
$stmt = $conn->prepare("SELECT * FROM student WHERE email = ?");
$stmt->bind_param("s", $studentEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Student not found.";
    exit;
}

$student = $result->fetch_assoc();
$studentId = $student['id'];

// Get enrolled courses and grades
$query = "
    SELECT c.name, c.description, c.credits, g.grade
    FROM enrollment e
    INNER JOIN course c ON e.course_id = c.id
    LEFT JOIN grades g ON g.student_id = e.student_id AND g.course_id = e.course_id
    WHERE e.student_id = ?
";
$stmt2 = $conn->prepare($query);
$stmt2->bind_param("i", $studentId);
$stmt2->execute();
$coursesResult = $stmt2->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Student Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 20px;
            color: #333;
        }
        h1, h2 {
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #2980b9;
            color: white;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
        }
        .logout {
            float: right;
            margin-top: -40px;
            color: red;
        }
        .logout a {
            text-decoration: none;
            color: red;
            font-weight: bold;
        }
        a.profile-link {
            color: #2980b9;
            text-decoration: none;
            margin-right: 15px;
        }
        a.profile-link:hover {
            text-decoration: underline;
        }
        .links {
            margin-bottom: 20px;
        }
        .profile-photo {
            max-width: 150px;
            max-height: 150px;
            border-radius: 50%;
            margin-bottom: 20px;
            object-fit: cover;
            border: 2px solid #2980b9;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($student['name']); ?></h1>
    <div class="logout"><a href="logout.php">Logout</a></div>

    <div class="links">
        <a href="edit_profile.php" class="profile-link">Edit Profile</a> | 
        <a href="change_password.php" class="profile-link">Change Password</a>
    </div>

    <?php if (!empty($student['photo']) && file_exists('uploads/' . $student['photo'])): ?>
        <img src="<?php echo 'uploads/' . htmlspecialchars($student['photo']); ?>" alt="Profile Photo" class="profile-photo" />
    <?php else: ?>
        <p><em>No profile photo uploaded.</em></p>
    <?php endif; ?>

    <h2>Your Information</h2>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
    <p><strong>Role:</strong> User</p>

    <h2>Your Enrolled Courses and Grades</h2>
    <?php if ($coursesResult->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Description</th>
                    <th>Credits</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $coursesResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['credits']); ?></td>
                        <td><?php echo htmlspecialchars($row['grade'] ?? 'N/A'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You are not enrolled in any courses yet.</p>
    <?php endif; ?>
</div>
</body>
</html>
