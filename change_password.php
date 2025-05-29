<?php
session_start();
include('db.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php?role=student');
    exit;
}

$email = $_SESSION['username'];
$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($new) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($new !== $confirm) $errors[] = "New passwords do not match.";

    if (!$errors) {
        $stmt = $conn->prepare("SELECT password FROM student WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (!password_verify($current, $row['password'])) {
                $errors[] = "Current password is incorrect.";
            } else {
                $newHash = password_hash($new, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE student SET password = ? WHERE email = ?");
                $update->bind_param("ss", $newHash, $email);
                if ($update->execute()) $success = "Password changed successfully.";
                else $errors[] = "Error updating password.";
            }
        } else {
            $errors[] = "User not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 20px; }
        .container { max-width: 500px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; }
        input, button { width: 100%; padding: 10px; margin-top: 10px; border-radius: 4px; }
        button { background: #2980b9; color: white; border: none; }
        button:hover { background: #1f6391; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
<div class="container">
    <h2>Change Password</h2>

    <?php if ($errors): ?>
        <div class="error"><ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="password" name="current_password" placeholder="Current Password" required>
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
        <button type="submit">Update Password</button>
    </form>

    <p><a href="student_page.php">← Back to Student Page</a></p>
</div>
</body>
</html>
