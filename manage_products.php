<?php
include 'db.php';
session_start();

/**
 * Function to fetch products by category AND search term
 */
function getProductsByCategory($conn, $cat, $search = "") {
    $search_query = "";
    if (!empty($search)) {
        $search = mysqli_real_escape_string($conn, $search);
        $search_query = " AND name LIKE '%$search%'";
    }
    $sql = "SELECT * FROM products WHERE category = '$cat' $search_query ORDER BY id DESC";
    return mysqli_query($conn, $sql);
}

// Check if a search was performed
$search_term = isset($_GET['search']) ? $_GET['search'] : "";

// --- DELETE LOGIC ---
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $find_sql = "SELECT image_url FROM products WHERE id = '$id'";
    $find_res = mysqli_query($conn, $find_sql);
    $product_data = mysqli_fetch_assoc($find_res);

    if ($product_data) {
        $file_path = $product_data['image_url'];
        // Delete the physical file from the server if it exists
        if (file_exists($file_path)) { unlink($file_path); }
        
        mysqli_query($conn, "DELETE FROM products WHERE id = '$id'");
        header("Location: manage_products.php?msg=deleted");
        exit();
    }
}

// Fetching data
$men_products   = getProductsByCategory($conn, 'men', $search_term);
$women_products = getProductsByCategory($conn, 'women', $search_term);
$kids_products  = getProductsByCategory($conn, 'kids', $search_term);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Archive | MKW Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #050505; color: #fff; }
        .manage-wrapper { padding: 60px 8%; max-width: 1400px; margin: 0 auto; }
        
        /* Premium Search Bar */
        .search-container { display: flex; gap: 0; margin-bottom: 50px; border-bottom: 1px solid #1a1a1c; }
        .search-input { 
            flex: 1; padding: 15px 0; background: transparent; border: none; 
            color: #fff; outline: none; letter-spacing: 2px; font-size: 12px;
            text-transform: uppercase;
        }
        .search-btn { 
            background: transparent; color: var(--gold); border: none; 
            padding: 0 20px; text-transform: uppercase; cursor: pointer; font-size: 11px; letter-spacing: 2px;
        }

        /* Section Headers */
        .section-header { 
            display: flex; justify-content: space-between; align-items: center;
            background: #0c0c0d; padding: 20px 25px; margin: 50px 0 0; 
            border-left: 2px solid var(--gold);
        }
        .section-header h2 { font-family: 'Cinzel', serif; letter-spacing: 4px; text-transform: uppercase; font-size: 14px; margin: 0; color: var(--gold); }
        .count-badge { font-size: 9px; color: #555; letter-spacing: 2px; text-transform: uppercase; }
        
        /* Table Customization */
        table { width: 100%; border-collapse: collapse; margin-bottom: 40px; background: #080808; }
        th { text-align: left; padding: 20px; font-size: 10px; color: #444; text-transform: uppercase; letter-spacing: 2px; border-bottom: 1px solid #1a1a1c; }
        td { padding: 15px 20px; border-bottom: 1px solid #0f0f10; vertical-align: middle; }

        .prod-img { width: 60px; height: 75px; object-fit: cover; border: 1px solid #1a1a1c; }
        
        .action-link { 
            font-size: 9px; text-transform: uppercase; text-decoration: none; 
            letter-spacing: 2px; padding: 8px 15px; transition: 0.3s; display: inline-block;
        }
        .edit-link { color: var(--gold); border: 1px solid var(--gold); margin-right: 10px; }
        .edit-link:hover { background: var(--gold); color: #000; }
        .delete-link { color: #ff4d4d; border: 1px solid #ff4d4d; }
        .delete-link:hover { background: #ff4d4d; color: #fff; }

        .msg-banner {
            padding: 15px; background: rgba(197, 160, 89, 0.1); border: 1px solid var(--gold);
            color: var(--gold); font-size: 11px; letter-spacing: 2px; text-transform: uppercase;
            margin-bottom: 30px; text-align: center;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="manage-wrapper">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
        <h1 style="letter-spacing: 8px; text-transform: uppercase; font-family: 'Cinzel', serif; font-size: 28px;">Inventory Archive</h1>
        <a href="admin_dashboard.php" style="color: #666; font-size: 10px; letter-spacing: 2px; text-decoration: none; text-transform: uppercase;">← Back to Dashboard</a>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="msg-banner">Product successfully removed from Atelier Archive.</div>
    <?php endif; ?>

    <form method="GET" class="search-container">
        <input type="text" name="search" class="search-input" placeholder="Search by name..." value="<?php echo htmlspecialchars($search_term); ?>">
        <button type="submit" class="search-btn">Filter</button>
        <?php if(!empty($search_term)): ?>
            <a href="manage_products.php" style="color:#ff4d4d; text-decoration:none; font-size:10px; align-self:center; margin-left:20px; letter-spacing: 2px;">RESET</a>
        <?php endif; ?>
    </form>

    <?php 
    $sections = [
        'Men\'s Collection' => $men_products,
        'Women\'s Collection' => $women_products,
        'Kids\' Collection' => $kids_products
    ];

    foreach($sections as $title => $result): ?>
        <div class="section-header">
            <h2><?php echo $title; ?></h2>
            <span class="count-badge"><?php echo mysqli_num_rows($result); ?> pieces found</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th width="10%">Visual</th>
                    <th width="45%">Identification</th>
                    <th width="20%">Retail Value</th>
                    <th width="25%">Management</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><img src="<?php echo $row['image_url']; ?>" class="prod-img"></td>
                            <td>
                                <div style="font-size: 13px; letter-spacing: 1px; color: #fff;"><?php echo strtoupper($row['name']); ?></div>
                                <div style="font-size: 9px; color: #444; margin-top: 5px; letter-spacing: 1px;">ID: #MKW-<?php echo $row['id']; ?></div>
                            </td>
                            <td style="color: var(--gold); font-family: 'Cinzel', serif;">₹<?php echo number_format($row['price'], 0); ?></td>
                            <td>
                                <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="action-link edit-link">Edit</a>
                                <a href="manage_products.php?delete=<?php echo $row['id']; ?>" 
                                   class="action-link delete-link" 
                                   onclick="return confirm('Archive this piece permanently?')">Remove</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center; padding: 60px; color: #444; font-size: 11px; letter-spacing: 2px; text-transform: uppercase;">The archive is currently empty for this category.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html>