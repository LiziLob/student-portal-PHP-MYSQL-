<?php
session_start(); 

include('db.php'); 

$role = isset($_GET['role']) ? $_GET['role'] : 'student'; 
// Determine role from URL parameter, default to 'student'

$error = ''; 
// Initialize error message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 

    $username = $_POST['username'];  

    $password = $_POST['password']; 

    if ($role === 'admin') {
        $query = "SELECT * FROM admins WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        // Prepare and bind parameter for admin username
    } else {
        $query = "SELECT * FROM student WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        // Prepare and bind parameter for student email
    }

    $stmt->execute(); 
    // Execute the prepared statement

    $result = $stmt->get_result(); 
    // Get the result of the query

    if ($result->num_rows === 1) { 
        // Check if exactly one matching user found

        $user = $result->fetch_assoc(); 
        // Fetch user data as associative array

        if ($role === 'admin') {
            if (password_verify($password, $user['password'])) { 
                // Verify admin password hash

                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'admin';
                header('Location: dashboard.php');
                exit;
                // Set session and redirect to admin dashboard
            } else {
                $error = "Invalid password"; 
                // Password mismatch error for admin
            }
        } else {
            if (password_verify($password, $user['password'])) { 
                // Verify student password hash

                $_SESSION['username'] = $user['email'];
                $_SESSION['role'] = 'student';
                header('Location: student_page.php');
                exit;
                // Set session and redirect to student page
            } else {
                $error = "Invalid password"; 
                // Password mismatch error for student
            }
        }
    } else {
        $error = "Invalid login credentials"; 
        // No user found with provided username/email
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
        <!-- Displays the role with the first letter capitalized (e.g., Admin Login) -->

        <?php if (!empty($error)) { ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <!-- If there is an error message, display it safely -->
        <?php } ?>

        <form method="post" autocomplete="off">
            <input type="text" name="username" placeholder="<?php echo ($role == 'admin') ? 'Username' : 'Email'; ?>" required autofocus />
            <!-- Username input for admin, Email input for other roles -->
            
            <input type="password" name="password" placeholder="Password" required />
            <!-- Password input field -->

            <button type="submit">Login</button>
            <!-- Submit button -->
        </form>

        <a href="index.php" class="back-link">Back to main page</a>
    </div>
</body>
</html>
