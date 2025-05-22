<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Student Portal Main Page</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            text-align: center;
            padding: 20px;
        }

        h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 0 2px 5px rgba(0,0,0,0.3);
            letter-spacing: 2px;
        }

        p {
            margin-top: 1.5rem;
        }

        a {
            display: inline-block;
            margin: 0 15px;
            padding: 12px 28px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        a:hover {
            background: #fff;
            color: #764ba2;
            box-shadow: 0 6px 20px rgba(118, 75, 162, 0.6);
            transform: translateY(-3px);
        }

        /* Responsive tweaks */
        @media (max-width: 480px) {
            h1 {
                font-size: 2rem;
            }

            a {
                display: block;
                margin: 12px auto;
                width: 70%;
                font-size: 1rem;
                padding: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div>
        <h1>Welcome to the Student Portal</h1>
        <p>
            <a href="login.php?role=student">Student Login</a> 
            <a href="login.php?role=admin">Admin Login</a> 
            <a href="register.php">Register as Student</a> 
            <a href="register_admin.php">Register as Admin</a>
        </p>
    </div>
</body>
</html>
