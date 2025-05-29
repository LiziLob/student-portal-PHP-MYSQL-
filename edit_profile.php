<?php
session_start();
include('db.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php?role=student');
    exit;
}

$studentEmail = $_SESSION['username'];
$message = '';


$stmt = $conn->prepare("SELECT id, name, email, photo FROM student WHERE email = ?");
$stmt->bind_param("s", $studentEmail);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    die("Student not found.");
}


$studentId = $student['id'];



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['name']);
    $photoPath = $student['photo'];

    if ($newName === '') {
        $message = "Name cannot be empty.";
    } else {
        // Handle photo upload if provided
        if (!empty($_FILES['photo']['name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = $_FILES['photo']['type'];

            if (in_array($fileType, $allowedTypes)) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);


                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $newFileName = uniqid('photo_', true) . '.' . $ext;
                $uploadPath = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
                    if ($photoPath && file_exists($uploadDir . $photoPath)) {
                        unlink($uploadDir . $photoPath);
                    }
                    $photoPath = $newFileName;
                } else {
                    $message = "Failed to upload photo.";
                }
            } else {
                $message = "Only JPG, PNG, and GIF files are allowed.";
            }
        }
        // Update only if no error
        if (!$message) {
            $update = $conn->prepare("UPDATE student SET name = ?, photo = ? WHERE id = ?");
            $update->bind_param("ssi", $newName, $photoPath, $studentId);

            if ($update->execute()) {
                $message = "Profile updated successfully.";
                $student['name'] = $newName;
                $student['photo'] = $photoPath;
            } else {
                $message = "Error updating profile.";
            }

            $update->close();
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Profile</title>
    <style>
        body {
            font-family: Arial;
            background: #f0f0f0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            background: white;
            padding: 20px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
        input[type="text"], input[type="email"], input[type="file"] {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
        }
        input[readonly] {
            background: #eee;
        }
        button {
            background: #2980b9;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            cursor: pointer;
        }
        .message {
            margin-top: 10px;
            font-weight: bold;
            color: green;
        }
        .error {
            color: red;
        }
        img {
            margin-top: 10px;
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Profile</h2>

    <?php if ($message): ?>
        <p class="message <?php echo (strpos($message, 'Error') === false && strpos($message, 'Failed') === false) ? '' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label>Name:</label>
        <input type="text" name="name" required value="<?php echo htmlspecialchars($student['name']); ?>">

        <label>Email:</label>
        <input type="email" value="<?php echo htmlspecialchars($student['email']); ?>" readonly>

        <label>Upload Photo:</label>
        <input type="file" name="photo" accept="image/*">

        <?php if (!empty($student['photo']) && file_exists('uploads/' . $student['photo'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($student['photo']); ?>" alt="Photo">
        <?php else: ?>
            <p>No photo uploaded.</p>
        <?php endif; ?>

        <button type="submit">Update Profile</button>
    </form>

    <p><a href="student_page.php">← Back to Dashboard</a></p>
</div>
</body>
</html>
