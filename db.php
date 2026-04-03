<?php
// Try the default connection first
$servername = "localhost"; 
$username = "root";
$password = ""; 
$dbname = "mkw_originals";

// The @ hides the scary warning so we can show a clean error message instead
$conn = @mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    echo "<h3>Database Connection Error</h3>";
    echo "Reason: " . mysqli_connect_error();
    echo "<br>Check if MySQL is turned ON in your XAMPP Control Panel.";
    exit;
}
?>