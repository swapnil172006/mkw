<?php
include 'db.php';
session_start();

$error = "";

if (isset($_POST['login_admin'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM admins WHERE email = '$email'");
    
    if (mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_user'] = $admin['username'];
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "INVALID CREDENTIALS";
        }
    } else {
        $error = "ADMIN NOT FOUND";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Executive Authentication | MKW</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-box {
            max-width: 400px; margin: 150px auto; padding: 40px;
            background: #0c0c0d; border: 1px solid var(--border);
            text-align: center;
        }
        .login-box h1 { font-family: 'Cinzel', serif; letter-spacing: 5px; margin-bottom: 30px; }
        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group label { font-size: 10px; color: #555; text-transform: uppercase; letter-spacing: 2px; }
        .input-group input {
            width: 100%; background: #000; border: 1px solid #222;
            padding: 12px; color: #fff; margin-top: 5px;
        }
        .login-btn {
            width: 100%; background: var(--gold); border: none;
            padding: 15px; font-weight: bold; cursor: pointer;
            text-transform: uppercase; letter-spacing: 2px;
        }
        .error-msg { color: #ff4d4d; font-size: 10px; margin-bottom: 20px; letter-spacing: 1px; }
    </style>
</head>
<body>

<div class="login-box">
    <h1>ADMIN</h1>
    <?php if($error) echo "<p class='error-msg'>$error</p>"; ?>
    
    <form method="POST">
        <div class="input-group">
            <label>Administrative Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="input-group">
            <label>Security Key (Password)</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" name="login_admin" class="login-btn">Authenticate</button>
    </form>
    <p style="margin-top: 20px;"><a href="index.php" style="color: #444; font-size: 10px; text-decoration: none;">Return to Store</a></p>
</div>

</body>
</html>