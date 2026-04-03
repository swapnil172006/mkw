<?php 
include 'db.php'; 
session_start();
$message = ""; 

if (isset($_POST['signup'])) {
    $name = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Basic Security Validation
    if (strlen($password) < 6) {
        $message = "<div class='auth-msg error'>Password must be at least 6 characters.</div>";
    } else {
        $pass_hashed = password_hash($password, PASSWORD_DEFAULT); 
        
        // Check if email already exists
        $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        
        if (mysqli_num_rows($check_email) > 0) {
            $message = "<div class='auth-msg error'>This email is already part of the collective.</div>";
        } else {
            $sql = "INSERT INTO users (username, email, password) VALUES ('$name', '$email', '$pass_hashed')";
            if (mysqli_query($conn, $sql)) {
                // Success: Redirect to login with a status flag
                header("Location: login.php?signup=success");
                exit(); 
            } else {
                $message = "<div class='auth-msg error'>An error occurred. Please try again.</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In the Collective | MKW Originals</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: var(--dark); color: #fff; }

        .auth-wrapper {
            min-height: 85vh;
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
            letter-spacing: 8px;
            text-transform: uppercase;
            margin-bottom: 40px;
            color: var(--gold);
            font-weight: 400;
        }

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
            width: 100%;
            margin-top: 10px;
        }

        .buy-now-btn:hover { background: #fff; transform: translateY(-3px); }

        .footer-link {
            margin-top: 40px;
            border-top: 1px solid #1a1a1c;
            padding-top: 30px;
            font-size: 10px;
            color: #444;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .footer-link a {
            color: var(--gold);
            text-decoration: none;
            border-bottom: 1px solid var(--gold);
            margin-left: 10px;
            padding-bottom: 2px;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="auth-wrapper">
    <div class="auth-container">
        <p style="letter-spacing: 5px; color: var(--gold); font-size: 10px; text-transform: uppercase; margin-bottom: 10px;">New Membership</p>
        <h2>Sign In</h2>
        
        <?php echo $message; ?>

        <form method="POST">
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="username" placeholder="Enter your name" required>
            </div>
            
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="name@example.com" required>
            </div>

            <div class="input-group">
                <label>Create Password</label>
                <input type="password" name="password" placeholder="Minimum 6 characters" required>
            </div>

            <button type="submit" name="signup" class="buy-now-btn">
                Create Account
            </button>
        </form>

        <div class="footer-link">
            Already a member? <a href="login.php">Login</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>