<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db.php';
session_start();

$message = "";

// 1. Fetch existing product
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id = '$id'");
    
    if (!$res) {
        die("Database Error: " . mysqli_error($conn));
    }
    
    $product = mysqli_fetch_assoc($res);

    if (!$product) {
        die("<div style='background:#050505; color:#c5a059; height:100vh; display:flex; align-items:center; justify-content:center; font-family:serif; letter-spacing:5px;'>PIECE NOT FOUND IN ARCHIVE.</div>");
    }
} else {
    header("Location: manage_products.php");
    exit();
}

// 2. Handle Update Request
if (isset($_POST['update_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // Combine all size categories into one string
    $age_sizes = isset($_POST['age_sizes']) ? $_POST['age_sizes'] : [];
    $num_sizes = isset($_POST['num_sizes']) ? $_POST['num_sizes'] : [];
    $alpha_sizes = isset($_POST['alpha_sizes']) ? $_POST['alpha_sizes'] : [];
    
    $all_selected = array_merge($age_sizes, $num_sizes, $alpha_sizes);
    $final_sizes = mysqli_real_escape_string($conn, implode(',', $all_selected));
    
    $img_paths = [
        'image_url'     => $product['image_url'],
        'image_front_2' => $product['image_front_2'] ?? '',
        'image_side'    => $product['image_side'] ?? '',
        'image_detail'  => $product['image_detail'] ?? ''
    ];

    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    foreach ($img_paths as $key => $current_value) {
        if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
            $file_name = time() . "_" . $key . "_" . basename($_FILES[$key]["name"]);
            $target_file = $target_dir . $file_name;
            if (move_uploaded_file($_FILES[$key]["tmp_name"], $target_file)) {
                if (!empty($current_value) && file_exists($current_value)) unlink($current_value);
                $img_paths[$key] = $target_file;
            }
        }
    }

    $update_sql = "UPDATE products SET 
                   name = '$name', price = '$price', category = '$category',
                   image_url = '{$img_paths['image_url']}', 
                   image_front_2 = '{$img_paths['image_front_2']}', 
                   image_side = '{$img_paths['image_side']}', 
                   image_detail = '{$img_paths['image_detail']}', 
                   description = '$description', sizes = '$final_sizes' 
                   WHERE id = '$id'";

    if (mysqli_query($conn, $update_sql)) {
        header("Location: manage_products.php?msg=updated");
        exit();
    } else {
        $message = "<div style='color:#ff4d4d; border:1px solid #ff4d4d; padding:10px; margin-bottom:20px;'>SQL ERROR: " . mysqli_error($conn) . "</div>";
    }
}

$current_sizes = explode(',', $product['sizes']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Creation | MKW Admin</title>
    <style>
        :root { --gold: #c5a059; --border: #1a1a1c; }
        body { background: #050505; color: #fff; font-family: 'Inter', sans-serif; margin: 0; padding-bottom: 50px; }
        .admin-form-container { max-width: 1000px; margin: 50px auto; background: #0c0c0d; padding: 40px; border: 1px solid var(--border); box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
        h2 { font-family: 'Cinzel', serif; letter-spacing: 5px; text-transform: uppercase; color: var(--gold); text-align: center; margin-bottom: 40px; }
        
        .archive-info-bar { display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 15px; margin-bottom: 30px; font-size: 10px; letter-spacing: 2px; color: #666; text-transform: uppercase; }
        .archive-info-bar span b { color: var(--gold); }

        .input-group { margin-bottom: 25px; }
        .input-group label { display: block; font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 8px; letter-spacing: 2px; }
        input[type="text"], input[type="number"], select, textarea { width: 100%; padding: 12px 0; background: transparent; border: none; border-bottom: 1px solid #222; color: #fff; outline: none; font-size: 14px; transition: 0.3s; font-family: inherit; }
        input:focus, textarea:focus { border-bottom-color: var(--gold); }

        /* Updated Size Matrix to 3 Columns */
        .size-matrix { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; background: #080808; padding: 20px; border: 1px solid #1a1a1c; margin-bottom: 25px; }
        .size-col h4 { font-size: 8px; color: var(--gold); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 15px; border-bottom: 1px solid #222; padding-bottom: 5px; }
        .checkbox-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(50px, 1fr)); gap: 10px; }
        .size-item { display: flex; align-items: center; gap: 5px; font-size: 10px; color: #aaa; cursor: pointer; white-space: nowrap; }
        .size-item input { accent-color: var(--gold); }

        .image-management { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin: 30px 0; }
        .image-card { background: #080808; padding: 15px; border: 1px solid #1a1a1c; text-align: center; transition: 0.3s; }
        .image-card:hover { border-color: var(--gold); transform: translateY(-5px); }
        .preview-container { width: 100%; height: 250px; background: #000; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid #222; }
        .preview-box { width: 100%; height: 100%; object-fit: cover; }
        .custom-file-btn { display: block; background: #1a1a1c; color: #999; padding: 10px; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; border: 1px solid #333; }
        .save-btn { background: var(--gold); color: #000; font-weight: 900; border: none; padding: 22px; width: 100%; cursor: pointer; text-transform: uppercase; letter-spacing: 4px; transition: 0.4s; margin-top: 20px; }
        .save-btn:hover { background: #fff; letter-spacing: 6px; }
        .back-link { display: block; text-align: center; margin-top: 25px; color: #444; text-decoration: none; font-size: 10px; letter-spacing: 2px; text-transform: uppercase; }
    </style>
</head>
<body>

<div class="admin-form-container">
    <div class="archive-info-bar">
        <span>Archive Log: <b><?php echo date("Y"); ?></b></span>
        <span>Registry ID: <b>MKW-<?php echo str_pad($product['id'], 4, '0', STR_PAD_LEFT); ?></b></span>
    </div>

    <h2>Edit Archive Piece</h2>
    <?php echo $message; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="input-group">
            <label>Identification Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>

        <div style="display: flex; gap: 20px;">
            <div class="input-group" style="flex: 1;">
                <label>Retail Value (INR)</label>
                <input type="number" name="price" value="<?php echo $product['price']; ?>" required>
            </div>
            <div class="input-group" style="flex: 1;">
                <label>Category Archive</label>
                <select name="category" required>
                    <option value="men" <?php echo ($product['category'] == 'men') ? 'selected' : ''; ?>>Men</option>
                    <option value="women" <?php echo ($product['category'] == 'women') ? 'selected' : ''; ?>>Women</option>
                    <option value="kids" <?php echo ($product['category'] == 'kids') ? 'selected' : ''; ?>>Kids</option>
                </select>
            </div>
        </div>

        <div class="input-group">
            <label>Inventory Size Availability</label>
            <div class="size-matrix">
                <div class="size-col">
                    <h4>Alpha (Standard)</h4>
                    <div class="checkbox-grid">
                        <?php 
                        $alphas = ['S', 'M', 'L', 'XL', 'XXL', 'XM', 'XX'];
                        foreach($alphas as $alpha): ?>
                            <label class="size-item">
                                <input type="checkbox" name="alpha_sizes[]" value="<?php echo $alpha; ?>" <?php echo in_array($alpha, $current_sizes) ? 'checked' : ''; ?>>
                                <?php echo $alpha; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="size-col">
                    <h4>Numeric (Bespoke)</h4>
                    <div class="checkbox-grid">
                        <?php 
                        $nums = ['36', '38', '40', '42', '44', '46'];
                        foreach($nums as $num): ?>
                            <label class="size-item">
                                <input type="checkbox" name="num_sizes[]" value="<?php echo $num; ?>" <?php echo in_array($num, $current_sizes) ? 'checked' : ''; ?>>
                                <?php echo $num; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="size-col">
                    <h4>Junior (Age)</h4>
                    <div class="checkbox-grid">
                        <?php 
                        $ages = ['2-3Y', '4-5Y', '6-7Y', '8-9Y', '10-11Y'];
                        foreach($ages as $age): ?>
                            <label class="size-item">
                                <input type="checkbox" name="age_sizes[]" value="<?php echo $age; ?>" <?php echo in_array($age, $current_sizes) ? 'checked' : ''; ?>>
                                <?php echo $age; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="image-management">
            <?php 
            $labels = ['image_url' => 'Primary', 'image_front_2' => 'Angle 2', 'image_side' => 'Side', 'image_detail' => 'Detail'];
            foreach ($labels as $key => $label): 
            ?>
            <div class="image-card">
                <label style="font-size: 9px; color: var(--gold);"><?php echo $label; ?></label>
                <div class="preview-container">
                    <?php if(!empty($product[$key]) && file_exists($product[$key])): ?>
                        <img src="<?php echo $product[$key]; ?>" class="preview-box" id="preview-<?php echo $key; ?>">
                    <?php else: ?>
                        <img src="" class="preview-box" id="preview-<?php echo $key; ?>" style="display:none;">
                        <div id="no-img-<?php echo $key; ?>" style="color:#333; font-size:10px;">EMPTY</div>
                    <?php endif; ?>
                </div>
                <div style="position:relative; overflow:hidden;">
                    <span class="custom-file-btn">Upload</span>
                    <input type="file" name="<?php echo $key; ?>" accept="image/*" onchange="previewImage(this, '<?php echo $key; ?>')" style="position:absolute; left:0; top:0; opacity:0; cursor:pointer; width:100%;">
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="input-group">
            <label>Atelier Description</label>
            <textarea name="description" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>

        <button type="submit" name="update_product" class="save-btn">Update Creation</button>
        <a href="manage_products.php" class="back-link">← Return to Inventory</a>
    </form>
</div>

<script>
    function previewImage(input, key) {
        const preview = document.getElementById('preview-' + key);
        const placeholder = document.getElementById('no-img-' + key);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                if(placeholder) placeholder.style.display = 'none';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

</body>
</html>