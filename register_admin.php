<?php
include('db.php');
session_start();

$message = '';
$messageClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
            header('Location: login.php?role=admin');
            exit;
        } else {
            $message = "Error executing query: " . $stmt->error;
            $messageClass = "error";
        }
    } else {
        $message = "Error preparing statement: " . $conn->error;
        $messageClass = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            padding: 20px;
        }
        .register-container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 40px 50px;
            border-radius: 15px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            width: 350px;
            text-align: center;
        }
        h2 {
            margin-bottom: 30px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin: 12px 0 24px 0;
            border: none;
            border-radius: 30px;
            font-size: 1rem;
            outline: none;
            transition: box-shadow 0.3s ease;
            background: rgba(255, 255, 255, 0.3);
            color: #fff;
        }
        input::placeholder {
            color: #ddd;
        }
        input:focus {
            box-shadow: 0 0 8px 2px #6a11cb;
            background: rgba(255, 255, 255, 0.5);
            color: #000;
        }
        button {
            width: 100%;
            padding: 12px 0;
            border: none;
            border-radius: 30px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 15px rgba(101, 44, 243, 0.4);
        }
        button:hover {
            background: #2575fc;
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(37, 117, 252, 0.6);
        }
        .message {
            margin-bottom: 15px;
            font-weight: 600;
            font-size: 1rem;
        }
        .error {
            color: #ff6b6b;
        }
        .success {
            color: #4caf50;
        }
        a.back-link {
            display: inline-block;
            margin-top: 20px;
            color: #eee;
            text-decoration: underline;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        a.back-link:hover {
            color: #fff;
        }
        @media (max-width: 400px) {
            .register-container {
                width: 90%;
                padding: 30px 25px;
            }
        }
    </style>
</head>
<body>

<div class="register-container">
    <h2>Register Admin</h2>

    <?php if ($message): ?>
        <div class="message <?= $messageClass ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
    </form>

    <a href="login.php?role=admin" class="back-link">Go to Admin Login</a>
</div>

</body>
</html>
