<?php 
include 'db.php'; 
session_start();
$message = ""; 

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            
            // Premium redirect feel
            header("Location: index.php");
            exit();
        } else {
            $message = "<div class='auth-msg error'>Incorrect password. Please try again.</div>";
        }
    } else {
        $message = "<div class='auth-msg error'>No account found with that email Signup First.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MKW Originals</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: var(--dark); }

        .auth-wrapper {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .auth-container {
            background: #0c0c0d;
            border: 1px solid var(--border);
            padding: 60px 50px;
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .auth-container h2 {
            font-family: 'Cinzel', serif;
            font-size: 28px;
            letter-spacing: 5px;
            text-transform: uppercase;
            margin-bottom: 40px;
            color: var(--white);
        }

        /* Login-specific form styling */
        .input-group { text-align: left; margin-bottom: 25px; }
        .input-group label {
            display: block;
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #555;
            margin-bottom: 8px;
        }

        .input-group input {
            width: 100%;
            padding: 12px 0;
            background: transparent;
            border: none;
            border-bottom: 1px solid #222;
            color: #fff;
            outline: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .input-group input:focus { border-bottom-color: var(--gold); }

        /* Notification Banners */
        .auth-msg {
            font-size: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 15px;
            margin-bottom: 30px;
            border: 1px solid;
        }

        .auth-msg.error {
            background: rgba(255, 77, 77, 0.05);
            color: #ff4d4d;
            border-color: #ff4d4d;
        }

        .auth-msg.success {
            background: rgba(75, 181, 67, 0.05);
            color: #4bb543;
            border-color: #4bb543;
        }

        /* Override the buy-now-btn for full width here */
        .buy-now-btn {
            background: var(--gold);
            color: #000;
            font-weight: bold;
            padding: 18px;
            border: none;
            cursor: pointer;
            letter-spacing: 3px;
            text-transform: uppercase;
            transition: 0.4s;
        }

        .buy-now-btn:hover { background: #fff; transform: translateY(-3px); }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="auth-wrapper">
    <div class="auth-container">
        <p style="letter-spacing: 5px; color: var(--gold); font-size: 10px; text-transform: uppercase; margin-bottom: 10px;">Membership</p>
        <h2>Log In</h2>
        
        <?php if(isset($_GET['signup']) && $_GET['signup'] == 'success'): ?>
            <div class="auth-msg success">Registration successful. Please sign in.</div>
        <?php endif; ?>

        <?php echo $message; ?>

        <form method="POST">
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="name@example.com" required>
            </div>

            <div class="input-group">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <label>Password</label>
                    <a href="forgot_password.php" style="font-size: 8px; letter-spacing: 1px; text-transform: uppercase; color: #444; text-decoration: none; border-bottom: 1px solid #444;">Forgot?</a>
                </div>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" name="login" class="buy-now-btn" style="width: 100%; margin-top: 10px;">
                Enter Collective
            </button>
        </form>

        <div style="margin-top: 40px; border-top: 1px solid #1a1a1c; padding-top: 30px;">
            <p style="font-size: 10px; color: #444; letter-spacing: 2px; text-transform: uppercase;">
                New to the brand? <a href="signup.php" style="color: var(--gold); text-decoration: none; border-bottom: 1px solid var(--gold); padding-bottom: 2px; margin-left: 10px;">Sign In</a>
            </p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>