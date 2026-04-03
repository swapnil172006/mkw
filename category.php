<?php
include 'db.php';
session_start();

// 1. Securely fetch category and search terms
$cat = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : 'men';
$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";

// 2. Build the Query
$query = "SELECT * FROM products WHERE category = '$cat'";
if (!empty($search)) {
    $query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}
$query .= " ORDER BY id DESC";

$products = mysqli_query($conn, $query);

// 3. Status for the "Added to Bag" Toast
$added_status = isset($_GET['added']) ? true : false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($cat); ?> Collection | MKW Originals</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* --- Premium Shop Layout --- */
        .shop-container { padding: 80px 8%; background: var(--dark); min-height: 80vh; }
        
        .shop-header { 
            display: flex; justify-content: space-between; align-items: flex-end; 
            margin-bottom: 60px; border-bottom: 1px solid var(--border); padding-bottom: 30px;
        }

        .shop-header h1 { 
            font-family: 'Cinzel', serif; font-size: 3rem; letter-spacing: 8px; 
            text-transform: uppercase; color: var(--white);
        }

        /* Sophisticated Search */
        .search-box input { 
            background: transparent; border: none; border-bottom: 1px solid #333;
            color: var(--white); padding: 10px; width: 200px; font-size: 10px;
            letter-spacing: 2px; transition: 0.4s; outline: none;
        }
        .search-box input:focus { width: 300px; border-color: var(--gold); }

        /* Luxury Product Grid */
        .product-grid { 
            display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
            gap: 60px 40px; 
        }

        .product-card { position: relative; background: #0c0c0d; transition: 0.5s; overflow: hidden; }
        
        /* Image Container & Hover Zoom */
        .img-container { position: relative; overflow: hidden; height: 480px; display: block; text-decoration: none; }
        .product-card img { width: 100%; height: 100%; object-fit: cover; transition: 1.2s cubic-bezier(0.19, 1, 0.22, 1); }
        .product-card:hover img { transform: scale(1.1); }

        /* Overlay text on image hover */
        .view-overlay {
            position: absolute; inset: 0; background: rgba(0,0,0,0.3);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: 0.4s;
        }
        .product-card:hover .view-overlay { opacity: 1; }
        .view-text { border: 1px solid #fff; padding: 10px 20px; font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: #fff; }

        /* Product Details */
        .product-info { padding: 30px 20px; text-align: center; }
        .product-info h3 { font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 3px; margin-bottom: 10px; color: #fff; }
        .product-info .price { color: var(--gold); font-size: 14px; letter-spacing: 2px; margin-bottom: 20px; }

        /* Premium Bag Button */
        .add-bag-btn {
            width: 100%; background: transparent; border: 1px solid #333;
            color: var(--muted); padding: 15px; font-size: 10px;
            text-transform: uppercase; letter-spacing: 3px; cursor: pointer;
            transition: 0.4s; outline: none;
        }

        .product-card:hover .add-bag-btn { border-color: var(--gold); color: #fff; }
        .add-bag-btn:hover { background: var(--gold) !important; color: #000 !important; transform: translateY(-2px); }

        /* --- Notification Toast --- */
        .toast {
            position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%);
            background: var(--gold); color: #000; padding: 15px 40px;
            font-size: 11px; text-transform: uppercase; letter-spacing: 3px;
            z-index: 9999; box-shadow: 0 15px 40px rgba(0,0,0,0.8);
            animation: toast-in 0.5s ease, toast-out 0.5s 3s ease forwards;
        }

        @keyframes toast-in { from { bottom: -50px; opacity: 0; } to { bottom: 30px; opacity: 1; } }
        @keyframes toast-out { from { opacity: 1; } to { opacity: 0; transform: translate(-50%, 20px); } }

        @media (max-width: 768px) {
            .shop-header { flex-direction: column; align-items: flex-start; gap: 30px; }
            .search-box input { width: 100%; }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<?php if($added_status): ?>
    <div class="toast">Selection added to your CART</div>
<?php endif; ?>

<main class="shop-container">
    <div class="shop-header">
        <div>
            <p style="color: var(--gold); font-size: 9px; letter-spacing: 5px; text-transform: uppercase; margin-bottom: 10px;">Collection</p>
            <h1><?php echo $cat; ?></h1>
        </div>
        
        <form action="category.php" method="GET" class="search-box">
            <input type="hidden" name="type" value="<?php echo $cat; ?>">
            <input type="text" name="q" placeholder="SEARCH PRODUCT" value="<?php echo htmlspecialchars($search); ?>">
        </form>
    </div>

    <div class="product-grid">
        <?php if(mysqli_num_rows($products) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($products)): ?>
                <div class="product-card">
                    <a href="view_product.php?id=<?php echo $row['id']; ?>" class="img-container">
                        <img src="<?php echo $row['image_url']; ?>" alt="<?php echo $row['name']; ?>">
                        <div class="view-overlay">
                            <span class="view-text">VIEW PRODUCT</span>
                        </div>
                    </a>
                    
                    <div class="product-info">
                        <h3><?php echo $row['name']; ?></h3>
                        <p class="price">₹<?php echo number_format($row['price'], 0); ?></p>
                        
                        <form action="add_to_cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="return_url" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                            <button type="submit" name="add_to_bag" class="add-bag-btn">Add to Cart</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 150px 0;">
                <p style="color: var(--muted); letter-spacing: 3px; font-size: 11px; text-transform: uppercase;">No creations found matching your search.</p>
                <a href="category.php?type=<?php echo $cat; ?>" style="color: var(--gold); font-size: 10px; text-decoration: none; margin-top: 30px; display: inline-block; border-bottom: 1px solid var(--gold); padding-bottom: 5px; letter-spacing: 2px;">VIEW ALL</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>

</body>
</html>