<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- STEP 1: THE ERROR CHECK (Place the code here) ---
    if (isset($_FILES['ig_image']) && $_FILES['ig_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        
        if ($_FILES['ig_image']['error'] === UPLOAD_ERR_INI_SIZE) {
            die("Error: The file is too large for the server settings. Increase upload_max_filesize in php.ini.");
        }

        // --- STEP 2: IF FILE IS OK, PROCEED WITH DATA ---
        $type = mysqli_real_escape_string($conn, $_POST['type']);
        $likes = mysqli_real_escape_string($conn, $_POST['likes']);
        $comments = mysqli_real_escape_string($conn, $_POST['comments']);
        
        $target_dir = "uploads/instagram/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES["ig_image"]["name"]);
        $target_file = $target_dir . $file_name;

        // --- STEP 3: MOVE AND SAVE ---
        if (move_uploaded_file($_FILES["ig_image"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO instagram_posts (image_path, content_type, likes, comments) 
                    VALUES ('$target_file', '$type', '$likes', '$comments')";

            if (mysqli_query($conn, $sql)) {
                header("Location: admin_instagram.php?status=success");
                exit();
            }
        } else {
            echo "Error: Could not move the file to the folder.";
        }

    } else {
        // --- STEP 4: THE FALLBACK ---
        die("Error: No file was selected or the browser failed to send the image.");
    }
}
?>