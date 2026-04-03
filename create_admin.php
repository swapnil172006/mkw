<?php
include 'db.php';

// 1. Set your desired credentials here
$admin_user = "Executive";
$admin_email = "admin@mkw.com";
$admin_password = "admin123"; // Change this to whatever you want

// 2. Hash the password (This turns 'admin123' into a secure string)
$hashed_password = password_hash($admin_password, PASSWORD_BCRYPT);

// 3. Clear existing admins to avoid "Duplicate" errors
mysqli_query($conn, "DELETE FROM admins");

// 4. Insert the secure admin into the database
$sql = "INSERT INTO admins (username, email, password) VALUES ('$admin_user', '$admin_email', '$hashed_password')";

if (mysqli_query($conn, $sql)) {
    echo "<div style='font-family:sans-serif; padding:50px; text-align:center;'>";
    echo "<h2 style='color:#c5a059;'>ADMIN CREATED SUCCESSFULLY</h2>";
    echo "<p>Email: <b>$admin_email</b></p>";
    echo "<p>Password: <b>$admin_password</b></p>";
    echo "<p style='color:red;'><b>IMPORTANT: Delete this file (create_admin.php) immediately for security!</b></p>";
    echo "<a href='admin_login.php'>Go to Admin Login</a>";
    echo "</div>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>