<?php
require 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($name && $email && $password) {
        $hashedPassword = password_hash(password: $password, algo: PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO student (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);

        if ($stmt->execute()) {
            header("Location: login.php?role=student");
            exit;
        } else {
            $error = "Registration failed. Try again.";
        }
    } else {
        $error = "Please fill all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Register</title>
<style>
  body {
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: Arial, sans-serif;
    color: #fff;
    margin: 0;
  }
  .container {
    background: rgba(255,255,255,0.15);
    padding: 40px;
    border-radius: 15px;
    width: 320px;
    text-align: center;
    box-shadow: 0 8px 32px rgba(31,38,135,0.37);
  }
  input, button {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border-radius: 30px;
    border: none;
    font-size: 1rem;
  }
  input {
    background: rgba(255,255,255,0.3);
    color: #fff;
    outline: none;
  }
  input::placeholder {
    color: #ddd;
  }
  input:focus {
    background: rgba(255,255,255,0.6);
    color: #000;
  }
  button {
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    color: white;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(101,44,243,0.4);
    transition: background 0.3s ease;
  }
  button:hover {
    background: #2575fc;
  }
  .error {
    color: #ff6b6b;
    font-weight: bold;
    margin-bottom: 15px;
  }
  a {
    color: #eee;
    text-decoration: underline;
    font-size: 0.9rem;
    display: inline-block;
    margin-top: 20px;
  }
  a:hover {
    color: #fff;
  }
</style>
</head>
<body>
  <div class="container">
    <h2>Register</h2>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <input type="text" name="name" placeholder="Name" required autofocus />
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Register</button>
    </form>

    <a href="index.php">Back to main page</a>
  </div>
</body>
</html>
