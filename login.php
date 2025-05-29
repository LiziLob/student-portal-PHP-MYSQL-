<?php
session_start();
include('db.php');

$role = $_GET['role'] ?? 'student'; 
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];  
    $password = $_POST['password'];  

    if ($role === 'admin') {
        $query = "SELECT * FROM admins WHERE username = ?"; 
    } else {
        $query = "SELECT * FROM student WHERE email = ?"; 
    }

    $stmt = $conn->prepare($query);     
    $stmt->bind_param("s", $username);    
    $stmt->execute();                      
    $result = $stmt->get_result();         

    if ($result->num_rows === 1) {         
        $user = $result->fetch_assoc();   

        if (password_verify($password, $user['password'])) { 
            $_SESSION['username'] = ($role === 'admin') ? $username : $user['email']; 
            $_SESSION['role'] = $role;
            header('Location: ' . (($role === 'admin') ? 'dashboard.php' : 'student_page.php')); // გადამისამართება
            exit;
        } else {
            $error = "Invalid password";
        }
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
<title><?= ucfirst($role) ?> Login</title> 
<style>
  * {
    box-sizing: border-box;
    margin: 0; padding: 0;
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
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    padding: 40px 50px;
    border-radius: 15px;
    box-shadow: 0 8px 32px rgba(31,38,135,0.37);
    width: 350px;
    text-align: center;
  }
  h2 {
    margin-bottom: 30px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-shadow: 0 2px 5px rgba(0,0,0,0.3);
  }
  input[type="text"], input[type="password"] {
    width: 100%;
    padding: 12px 15px;
    margin: 12px 0 24px;
    border: none;
    border-radius: 30px;
    font-size: 1rem;
    outline: none;
    background: rgba(255,255,255,0.3);
    color: #fff;
    transition: box-shadow 0.3s ease, background 0.3s ease;
  }
  input::placeholder {
    color: #ddd;
  }
  input:focus {
    box-shadow: 0 0 8px 2px #6a11cb;
    background: rgba(255,255,255,0.5);
    color: #000;
  }
  button {
    width: 100%;
    padding: 12px 0;
    border: none;
    border-radius: 30px;
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    color: #fff;
    font-weight: 700;
    font-size: 1.1rem;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(101,44,243,0.4);
    transition: background 0.3s ease, transform 0.2s ease;
  }
  button:hover {
    background: #2575fc;
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(37,117,252,0.6);
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
  <h2><?= ucfirst($role) ?> Login</h2>
  <?php if ($error): ?>
    <div class="error-msg"><?= htmlspecialchars($error) ?></div> <!-- შეცდომის გამოტანა -->
  <?php endif; ?>
  <form method="post" autocomplete="off">
    <input type="text" name="username" placeholder="<?= ($role === 'admin') ? 'Username' : 'Email' ?>" required autofocus /> 
    <!-- ადმინისტრატორისთვის სახელი, სტუდენტისთვის იმეილი -->
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">Login</button>
  </form>
  <a href="index.php" class="back-link">Back to main page</a>
</div>
</body>
</html>
