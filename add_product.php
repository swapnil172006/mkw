<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';
session_start();

if (!isset($_SESSION['admin_user'])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    // Sanitize inputs with fallbacks to prevent "Undefined Index" errors
    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $price = mysqli_real_escape_string($conn, $_POST['price'] ?? '0');
    $category = mysqli_real_escape_string($conn, $_POST['category'] ?? 'men');
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $sizes = mysqli_real_escape_string($conn, $_POST['sizes'] ?? ''); // FIXED LINE
    
    $final_image_path = "";

    // File Upload Logic
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $file_extension = pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION);
        $file_name = "MKW_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $file_extension;
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            $final_image_path = $target_file;
        }
    } 
    elseif (!empty($_POST['image_url_link'])) {
        $final_image_path = mysqli_real_escape_string($conn, $_POST['image_url_link']);
    }

    if (!empty($final_image_path)) {
        $sql = "INSERT INTO products (name, price, category, image_url, description, sizes) 
                VALUES ('$name', '$price', '$category', '$final_image_path', '$description', '$sizes')";

        if (mysqli_query($conn, $sql)) {
            $message = "<div class='banner-success'>Piece added to the archive successfully.</div>";
        } else {
            $message = "<div class='banner-error'>Database Error: " . mysqli_error($conn) . "</div>";
        }
    } else {
        $message = "<div class='banner-error'>A Visual Asset is required.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Creation | MKW Originals Admin</title>
    <style>
        :root { --gold: #c5a059; --border: #1a1a1c; }
        body { background: #050505; color: #fff; font-family: sans-serif; margin: 0; }
        .admin-form-container { max-width: 650px; margin: 60px auto; background: #0c0c0d; padding: 50px; border: 1px solid var(--border); }
        h2 { font-family: serif; letter-spacing: 4px; text-transform: uppercase; color: var(--gold); text-align: center; }
        .input-group { margin-bottom: 25px; }
        .input-group label { display: block; font-size: 10px; color: #555; text-transform: uppercase; margin-bottom: 8px; }
        input, select, textarea { width: 100%; padding: 12px 0; background: transparent; border: none; border-bottom: 1px solid #222; color: #fff; outline: none; }
        .submit-btn { background: var(--gold); color: #000; font-weight: bold; border: none; padding: 20px; width: 100%; cursor: pointer; text-transform: uppercase; margin-top: 20px; }
        .banner-success { border: 1px solid #4bb543; color: #4bb543; padding: 15px; text-align: center; margin-bottom: 20px; }
        .banner-error { border: 1px solid #ff4d4d; color: #ff4d4d; padding: 15px; text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="admin-form-container">
    <h2>Add New Creation</h2>
    <?php echo $message; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="input-group">
            <label>Product Name</label>
            <input type="text" name="name" required>
        </div>

        <div style="display: flex; gap: 20px;">
            <div class="input-group" style="flex: 1;">
                <label>Price (₹)</label>
                <input type="number" name="price" required>
            </div>
            <div class="input-group" style="flex: 1;">
                <label>Category</label>
                <select name="category" required>
                    <option value="men">Men</option>
                    <option value="women">Women</option>
                    <option value="kids">Kids</option>
                </select>
            </div>
        </div>

        <div class="input-group">
            <label>Available Sizes</label>
            <input type="text" name="sizes" placeholder="S, M, L, XL">
        </div>

        <div class="input-group">
            <label>Upload Image</label>
            <input type="file" name="product_image" accept="image/*">
        </div>

        <div class="input-group">
            <label>Description</label>
            <textarea name="description" rows="3"></textarea>
        </div>

        <button type="submit" name="add_product" class="submit-btn">Upload to Archive</button>
    </form>
</div>

</body>
</html>