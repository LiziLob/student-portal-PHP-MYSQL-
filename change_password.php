<?php
session_start(); // Start the session
include('db.php'); // Include database connection

// Check if user is logged in and has 'student' role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php?role=student'); // Redirect to login if not authorized
    exit;
}

$studentEmail = $_SESSION['username']; // Get student email from session
$errors = []; // Array to store error messages
$success = ''; // Success message

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve submitted form data
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate password length
    if (strlen($newPassword) < 6) {
        $errors[] = "New password must be at least 6 characters long.";
    }

    // Validate if new password and confirmation match
    if ($newPassword !== $confirmPassword) {
        $errors[] = "New password and confirmation do not match.";
    }

    // If no validation errors, proceed to password update
    if (empty($errors)) {
        // Fetch the current password hash from the database
        $stmt = $conn->prepare("SELECT password FROM student WHERE email = ?");
        $stmt->bind_param("s", $studentEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows !== 1) {
            $errors[] = "User not found.";
        } else {
            $user = $result->fetch_assoc();

            // Verify current password using password_verify
            if (!password_verify($currentPassword, $user['password'])) {
                $errors[] = "Current password is incorrect.";
            } else {
                // Hash the new password
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                // Prepare update statement
                $updateStmt = $conn->prepare("UPDATE student SET password = ? WHERE email = ?");
                $updateStmt->bind_param("ss", $newPasswordHash, $studentEmail);

                // Execute update and check result
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

    <!-- Display errors if any -->
    <?php if ($errors): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Display success message if password changed -->
    <?php if ($success): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <!-- Password change form -->
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
