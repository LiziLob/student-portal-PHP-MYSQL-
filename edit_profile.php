<?php
session_start();
include('db.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php?role=student');
    exit;
}

$studentEmail = $_SESSION['username'];

// Fetch current student info, including photo
$stmt = $conn->prepare("SELECT id, name, email, photo FROM student WHERE email = ?");
$stmt->bind_param("s", $studentEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Student not found.";
    exit;
}

$student = $result->fetch_assoc();
$studentId = $student['id'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['name'] ?? '');

    if (empty($newName)) {
        $message = "Name cannot be empty.";
    } else {
        // Handle photo upload if a file was sent
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES['photo']['type'], $allowedTypes)) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Create unique filename
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $newFileName = uniqid('photo_', true) . '.' . $ext;
                $uploadFile = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadFile)) {
                    // Delete old photo if exists
                    if (!empty($student['photo']) && file_exists($uploadDir . $student['photo'])) {
                        unlink($uploadDir . $student['photo']);
                    }
                    $photoPath = $newFileName;  // store only filename in DB
                } else {
                    $message = "Failed to upload photo.";
                }
            } else {
                $message = "Only JPG, PNG and GIF files are allowed.";
            }
        } else {
            // No new photo uploaded, keep old filename
            $photoPath = $student['photo'];
        }

        // Update DB only if no errors so far
        if (empty($message)) {
            $updateStmt = $conn->prepare("UPDATE student SET name = ?, photo = ? WHERE id = ?");
            $updateStmt->bind_param("ssi", $newName, $photoPath, $studentId);
            if ($updateStmt->execute()) {
                $message = "Profile updated successfully.";
                $student['name'] = $newName;
                $student['photo'] = $photoPath;
            } else {
                $message = "Error updating profile.";
            }
            $updateStmt->close();
        }
    }
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
        }
        label {
            display: block;
            margin: 15px 0 5px;
        }
        input[type="text"], input[type="email"], input[type="file"] {
            width: 100%;
            padding: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[readonly] {
            background: #eee;
        }
        button {
            margin-top: 20px;
            background: #2980b9;
            color: white;
            border: none;
            padding: 10px 18px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        .message {
            margin-top: 15px;
            font-weight: bold;
            color: green;
        }
        .error {
            color: red;
        }
        a {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #2980b9;
        }
        a:hover {
            text-decoration: underline;
        }
        .photo-preview {
            margin-top: 10px;
            max-width: 150px;
            max-height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Edit Profile</h1>

    <?php if ($message): ?>
        <p class="message <?php echo (strpos($message, 'Error') === false && strpos($message, 'Failed') === false) ? '' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="post" action="edit_profile.php" enctype="multipart/form-data">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" required value="<?php echo htmlspecialchars($student['name']); ?>" />

        <label for="email">Email (cannot be changed):</label>
        <input type="email" name="email" id="email" readonly value="<?php echo htmlspecialchars($student['email']); ?>" />

        <label for="photo">Upload Photo (JPG, PNG, GIF):</label>
        <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/gif" />

        <?php if (!empty($student['photo']) && file_exists('uploads/' . $student['photo'])): ?>
            <img src="<?php echo 'uploads/' . htmlspecialchars($student['photo']); ?>" alt="Your photo" class="photo-preview" />
        <?php else: ?>
            <p>No photo uploaded.</p>
        <?php endif; ?>

        <button type="submit">Update Profile</button>
    </form>

    <p><a href="student_page.php">Back to Dashboard</a></p>
</div>
</body>
</html>
