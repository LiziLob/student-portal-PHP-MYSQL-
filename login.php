<?php
session_start();
include('db.php');

$role = isset($_GET['role']) ? $_GET['role'] : 'student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($role == 'admin') {
        $query = "SELECT * FROM admins WHERE username=? AND password=?";
    } else {
        $query = "SELECT * FROM student WHERE username=? AND password=?";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        if ($role == 'admin') {
            header('Location: dashboard.php');
        } else {
            header('Location: student_page.php');
        }
        exit;
    } else {
        $error = "Invalid login credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo ucfirst($role); ?> Login</title>
    <style>
        /* Reset and base styles */
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
        .login-container {
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
        input[type="text"]::placeholder,
        input[type="password"]::placeholder {
            color: #ddd;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            box-shadow: 0 0 8px 2px #6a11cb;
            background: rgba(255, 255, 255, 0.5);
            color: #000;
        }
        button {
            width: 100%;
            padding: 12px 0;
            border: none;
            border-radius: 30px;
            background: #6a11cb;
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
        .error-msg {
            color: #ff6b6b;
            margin-bottom: 15px;
            font-weight: 600;
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
            .login-container {
                width: 90%;
                padding: 30px 25px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2><?php echo ucfirst($role); ?> Login</h2>
        <?php if (!empty($error)) { ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>
        <form method="post" autocomplete="off">
            <input type="text" name="username" placeholder="Username" required autofocus />
            <input type="password" name="password" placeholder="Password" required />
            <button type="submit">Login</button>
        </form>
        <a href="index.php" class="back-link">Back to main page</a>
    </div>
</body>
</html>
