<?php
include 'db.php';
session_start();

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT * FROM products WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $product = mysqli_fetch_assoc($result);

    if (!$product) {
        echo "<div style='color:#c5a059; background:#050505; height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; font-family:Cinzel, serif;'>
                <h1 style='letter-spacing:10px;'>ARCHIVE PIECE NOT FOUND</h1>
                <a href='index.php' style='color:#fff; margin-top:20px; text-decoration:none; font-size:12px; letter-spacing:2px;'>RETURN TO HOME</a>
              </div>";
        exit();
    }

    // Gallery Logic
    $gallery = [];
    $image_keys = [
        'image_url'     => 'Front View',
        'image_front_2' => 'Front Angle',
        'image_side'    => 'Side Profile',
        'image_detail'  => 'Detail Shot'
    ];

    foreach ($image_keys as $key => $label) {
        if (!empty($product[$key])) {
            $gallery[] = ['src' => $product[$key], 'label' => $label];
        }
    }
    $main_img = !empty($gallery) ? $gallery[0]['src'] : 'placeholder.jpg';

    // Size Logic: Convert "S,M,L" string into an array
    $available_sizes = !empty($product['sizes']) ? explode(',', $product['sizes']) : [];

} else {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> | MKW Originals Atelier</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #050505; color: #fff; margin: 0; font-family: 'Cinzel', serif; }
        .product-container { display: flex; max-width: 1400px; margin: 80px auto; gap: 40px; padding: 0 5%; align-items: flex-start; }
        
        /* Gallery Section */
        .gallery-section { flex: 1.4; display: flex; gap: 20px; }
        .thumbnail-column { display: flex; flex-direction: column; gap: 12px; max-height: 700px; overflow-y: auto; scrollbar-width: none; }
        .thumbnail-column::-webkit-scrollbar { display: none; }
        
        .thumb-box { width: 75px; height: 100px; background: #0c0c0d; border: 1px solid #1a1a1c; cursor: pointer; position: relative; overflow: hidden; opacity: 0.4; flex-shrink: 0; transition: 0.3s; }
        .thumb-box img { width: 100%; height: 100%; object-fit: cover; }
        .thumb-box.active, .thumb-box:hover { opacity: 1; border-color: #c5a059; }
        
        .main-image-stage { flex: 1; background: #0c0c0d; border: 1px solid #1a1a1c; height: 700px; overflow: hidden; }
        .main-image-stage img { width: 100%; height: 100%; object-fit: contain; transition: opacity 0.3s ease; }

        /* Details Section */
        .product-details { flex: 0.8; position: sticky; top: 120px; }
        .cat-breadcrumb { font-size: 10px; letter-spacing: 4px; text-transform: uppercase; color: #c5a059; margin-bottom: 15px; display: block; }
        .product-details h1 { font-size: clamp(28px, 3.5vw, 42px); letter-spacing: 4px; text-transform: uppercase; margin-bottom: 10px; font-weight: 200; line-height: 1.1; }
        .price-tag { font-size: 24px; color: #c5a059; margin-bottom: 30px; font-weight: 300; letter-spacing: 2px; }

        /* Description & Specs */
        .description-box { border-top: 1px solid #1a1a1c; padding-top: 30px; margin-bottom: 40px; }
        .description-text { color: #aaa; line-height: 1.8; font-size: 14px; margin-bottom: 25px; font-weight: 300; }
        
        .specs-list { list-style: none; border-bottom: 1px solid #1a1a1c; padding-bottom: 20px; padding-left: 0; margin-bottom: 30px; }
        .specs-list li { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #666; margin-bottom: 12px; display: flex; justify-content: space-between; }
        .specs-list li span { color: #fff; }

        /* Size Selector */
        .size-selector { margin-bottom: 35px; }
        .size-label { font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: #666; margin-bottom: 15px; display: block; }
        .size-options { display: flex; gap: 10px; flex-wrap: wrap; }
        .size-options input { display: none; }
        .size-options label { border: 1px solid #1a1a1c; padding: 10px 20px; font-size: 12px; cursor: pointer; transition: 0.3s; min-width: 45px; text-align: center; color: #888; }
        .size-options input:checked + label { border-color: #c5a059; color: #fff; background: #111; }
        .size-options label:hover { border-color: #444; }

        /* Action Buttons */
        .action-area { display: flex; flex-direction: column; gap: 15px; }
        .gold-btn { background: #c5a059; color: #000; border: none; padding: 20px; font-size: 11px; text-transform: uppercase; letter-spacing: 4px; font-weight: 600; cursor: pointer; transition: 0.4s; width: 100%; }
        .gold-btn:hover { background: #fff; letter-spacing: 6px; }
        .outline-btn { background: transparent; color: #666; border: 1px solid #1a1a1c; padding: 18px; font-size: 10px; text-transform: uppercase; letter-spacing: 3px; text-decoration: none; text-align: center; transition: 0.4s; }
        .outline-btn:hover { color: #fff; border-color: #444; background: #0c0c0d; }

        @media (max-width: 992px) {
            .product-container { flex-direction: column; }
            .gallery-section { flex-direction: column-reverse; width: 100%; }
            .thumbnail-column { flex-direction: row; justify-content: center; overflow-x: auto; width: 100%; }
            .main-image-stage { height: 500px; }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="product-container">
    <section class="gallery-section">
        <div class="thumbnail-column">
            <?php foreach ($gallery as $index => $item): ?>
                <div class="thumb-box <?php echo ($index === 0) ? 'active' : ''; ?>" onclick="updateView(this, '<?php echo $item['src']; ?>')">
                    <img src="<?php echo $item['src']; ?>" alt="View">
                </div>
            <?php endforeach; ?>
        </div>
        <div class="main-image-stage">
            <img id="main-view" src="<?php echo $main_img; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
    </section>

    <section class="product-details">
        <span class="cat-breadcrumb"><?php echo htmlspecialchars($product['category']); ?> Archive</span>
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <p class="price-tag">₹<?php echo number_format($product['price'], 0); ?></p>

        <div class="description-box">
            <p class="description-text">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </p>
            
            <ul class="specs-list">
                <li>Material <span>Premium Heritage Fabric</span></li>
                <li>Fitting <span>Artisanal Couture Fit</span></li>
                <li>Archive Series <span>Multi-Angle Perspective</span></li>
                <li>Reference <span>#MKW-ARC-<?php echo $product['id']; ?></span></li>
            </ul>
        </div>

        <form action="add_to_cart.php" method="POST">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

            <div class="size-selector">
                <span class="size-label">Select Size</span>
                <div class="size-options">
                    <?php if (!empty($available_sizes)): ?>
                        <?php foreach ($available_sizes as $size): $size = trim($size); ?>
                            <input type="radio" name="selected_size" id="size-<?php echo $size; ?>" value="<?php echo $size; ?>" required>
                            <label for="size-<?php echo $size; ?>"><?php echo $size; ?></label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="font-size: 10px; color: #666;">One Size / Archive Standard</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="action-area">
                <button type="submit" class="gold-btn">Add To Cart</button>
                <a href="index.php" class="outline-btn">Continue Shopping</a>
            </div>
        </form>
    </section>
</main>

<?php include 'footer.php'; ?>

<script>
    function updateView(thumb, newSrc) {
        const mainView = document.getElementById('main-view');
        mainView.style.opacity = '0';
        setTimeout(() => {
            mainView.src = newSrc;
            mainView.style.opacity = '1';
        }, 200);
        document.querySelectorAll('.thumb-box').forEach(box => box.classList.remove('active'));
        thumb.classList.add('active');
    }
</script>

</body>
</html>