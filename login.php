<?php
session_start();
include 'config.php';

if (isset($_SESSION['username'])) {
    if (isset($_SESSION['admin']) && $_SESSION['admin'] === 'YES') {
        header("Location: sales.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

$username = $password = "";
$uerr = $perr = "";

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    if (empty(trim($_POST['username'])) || empty(trim($_POST['password']))) {
        $uerr = "Please enter both username and password";
    } else {

        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $sql = "SELECT id, fullname, email, username, password, role, created_at, image
                FROM loginform
                WHERE username = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "s", $username);

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) === 1) {

                    mysqli_stmt_bind_result(
                        $stmt,
                        $id,
                        $fullname,
                        $email,
                        $db_username,
                        $hashed_password,
                        $role,
                        $created_at,
                        $image
                    );

                    if (mysqli_stmt_fetch($stmt)) {

                        if (password_verify($password, $hashed_password)) {

                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $db_username;
                            $_SESSION["fullname"] = $fullname;
                            $_SESSION["email"] = $email;
                            $_SESSION["created_at"] = $created_at;
                            $_SESSION["image"] = $image;

                            if (trim($role) === 'admin') {
                                $_SESSION['admin'] = 'YES';
                                header("Location: sales.php");
                            } else {
                                $_SESSION['admin'] = 'NO';
                                header("Location: index.php");
                            }
                            exit();

                        } else {
                            $perr = "Incorrect password";
                        }
                    }

                } else {
                    $uerr = "Username does not exist";
                }

            } else {
                $uerr = "Something went wrong!";
            }

            mysqli_stmt_close($stmt);
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Burger Mania - Login</title>
    <link rel="icon" href="Assets/logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('Assets/bg.png');
            background-size: 130%;
            animation: gradientBG 10s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            padding: 40px;
            border-radius: 15px;
            width: 350px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            text-align: center;
            color: #fff;
            margin-bottom: 20px;
        }

        label {
            color: #fff;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: none;
            outline: none;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #fd0404;
            color: #080808;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }

        .error {
            color: yellow;
            font-size: 14px;
            display: block;
            margin-bottom: 5px;
        }

        .register-link {
            text-align: center;
            margin-top: 15px;
            color: #fff;
        }

        .register-link a {
            color: yellow;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-box">
            <h2>Login</h2>

            <form method="post" autocomplete="off">
                <label>Username</label>
                <span class="error"><?php echo $uerr ?? ''; ?></span>
                <input type="text" name="username" required>

                <label>Password</label>
                <span class="error"><?php echo $perr ?? ''; ?></span>
                <input type="password" name="password" required>

                <button type="submit">Login</button>

                <div class="register-link">
                    Don't have an account?
                    <a href="register.php">Register First</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>