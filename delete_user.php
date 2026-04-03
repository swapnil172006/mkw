<?php
include 'db.php';
session_start();

// 1. Security Check: Ensure only logged-in admins can delete
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 2. Check if the ID is provided in the URL
if (isset($_GET['id'])) {
    $user_id = mysqli_real_escape_string($conn, $_GET['id']);

    // 3. Prevent deleting the main Admin account (Optional but recommended)
    // Assuming your admin ID is 1
    if ($user_id == '1') {
        header("Location: admin_dashboard.php?error=cannot_delete_admin");
        exit();
    }

    // 4. Execute the Delete Query
    $sql = "DELETE FROM users WHERE id = '$user_id'";

    if (mysqli_query($conn, $sql)) {
        // Success: Redirect back with a message
        header("Location: admin_dashboard.php?msg=user_deleted");
        exit();
    } else {
        // Failure
        header("Location: admin_dashboard.php?error=delete_failed");
        exit();
    }
} else {
    // No ID found in URL
    header("Location: admin_dashboard.php");
    exit();
}
?>