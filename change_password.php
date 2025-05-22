<?php
session_start();
include('db.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php?role=student');
    exit;
}

$studentEmail = $_SESSION['username'];
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate new password length
    if (strlen($newPassword) < 6) {
        $errors[] = "New password must be at least 6 characters long.";
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = "New password and confirmation do not match.";
    }

    if (empty($errors)) {
        // Fetch current password hash from DB
        $stmt = $conn->prepare("SELECT password FROM student WHERE email = ?");
        $stmt->bind_param("s", $studentEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            $errors[] = "User not found.";
        } else {
            $user = $result->fetch_assoc();

            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                $errors[] = "Current password is incorrect.";
            } else {
                // Hash new password and update
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE student SET password = ? WHERE email = ?");
                $updateStmt->bind_param("ss", $newPasswordHash, $studentEmail);

                if ($updateStmt->execute()) {
                    $success = "Password changed successfully.";
                } else {
                    $errors[] = "Failed to update password. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Change Password</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
        .container { max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; }
        input[type=password] {
            width: 100%; padding: 10px; margin: 8px 0; box-sizing: border-box;
            border: 1px solid #ccc; border-radius: 4px;
        }
        button {
            background-color: #2980b9; color: white; padding: 10px 20px;
            border: none; border-radius: 4px; cursor: pointer;
        }
        button:hover {
            background-color: #1f6391;
        }
        .error { color: red; }
        .success { color: green; }
        a { text-decoration: none; color: #2980b9; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <h2>Change Password</h2>

    <?php if ($errors): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label for="current_password">Current Password:</label>
        <input type="password" id="current_password" name="current_password" required />

        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required />

        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required />

        <button type="submit">Change Password</button>
    </form>

    <p><a href="student_page.php">Back to Dashboard</a></p>
</div>
</body>
</html>
