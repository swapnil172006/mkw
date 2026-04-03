<?php
include 'db.php';
session_start();

// --- 1. SECURE: Admin Authentication ---
if (!isset($_SESSION['admin_user'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_identity = $_SESSION['admin_user'];
$current_time = date('H:i:s');
$current_date = date('d M, Y');

// --- 2. HANDLE STATUS UPDATE ---
if (isset($_POST['update_status'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Auto-timestamp for logistics tracking
    $delivered_query = ($new_status === 'Delivered') ? ", delivered_at = NOW()" : ", delivered_at = NULL";
    
    $update_sql = "UPDATE orders SET status = '$new_status' $delivered_query WHERE id = '$order_id'";
    if (mysqli_query($conn, $update_sql)) {
        header("Location: admin_dashboard.php?msg=synced");
        exit();
    }
}

// --- 3. ANALYTICS CALCULATIONS ---
$revenue_query = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE status = 'Delivered'");
$total_revenue = mysqli_fetch_assoc($revenue_query)['total'] ?? 0;

$pipeline_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status != 'Delivered' AND status != 'Cancelled'");
$active_pipeline = mysqli_fetch_assoc($pipeline_query)['count'];

$member_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
$total_members = mysqli_fetch_assoc($member_query)['count'];

$inv_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
$total_inv = mysqli_fetch_assoc($inv_query)['total'] ?? 0;

// --- 4. LIFECYCLE DATA (For the Charts) ---
$p_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status = 'Pending'"))['c'];
$s_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status = 'Shipped'"))['c'];
$d_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status = 'Delivered'"))['c'];

// --- 5. ADMIN DATA FETCH ---
$admin_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM admins WHERE username = '$admin_identity'"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Terminal | MKW Originals</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Inter:wght@300;400;600&display=swap');

        :root {
            --gold: #c5a059;
            --dark: #050505;
            --bg-card: #0c0c0d;
            --border: rgba(197, 160, 89, 0.15);
            --cyan: #00e5ff;
        }

        body { background: var(--dark); color: #fff; font-family: 'Inter', sans-serif; margin: 0; overflow-x: hidden; }
        .admin-wrapper { padding: 40px 5%; max-width: 1500px; margin: 0 auto; }
        
        /* --- Action Bar --- */
        .action-bar {
            background: var(--bg-card); border: 1px solid var(--border); padding: 25px 40px;
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px; border-left: 5px solid var(--gold);
        }
        .header-title h1 { font-family: 'Cinzel', serif; font-size: 22px; letter-spacing: 6px; margin: 0; color: #fff; }
        .header-title p { font-size: 10px; color: #555; margin: 5px 0 0; text-transform: uppercase; letter-spacing: 2px; }

        /* --- Tab System --- */
        .dashboard-nav { display: flex; gap: 15px; margin-bottom: 35px; border-bottom: 1px solid #111; padding-bottom: 15px; }
        .nav-btn { 
            background: transparent; border: none; color: #444; 
            padding: 10px 20px; cursor: pointer; font-size: 11px; text-transform: uppercase; 
            letter-spacing: 2px; transition: 0.4s; font-weight: 600;
        }
        .nav-btn.active { color: var(--gold); border-bottom: 2px solid var(--gold); }
        .tab-panel { display: none; animation: slideUp 0.6s ease; }
        .tab-panel.active { display: block; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* --- Visualization Grid --- */
        .viz-layout { display: grid; grid-template-columns: 1.8fr 1fr; gap: 25px; margin-bottom: 40px; }
        .chart-container { background: var(--bg-card); border: 1px solid var(--border); padding: 30px; height: 350px; }
        .lifecycle-sidebar { display: flex; flex-direction: column; gap: 15px; }
        
        .pill-stat { 
            background: #080808; border: 1px solid var(--border); padding: 25px; 
            display: flex; justify-content: space-between; align-items: center;
            transition: 0.3s;
        }
        .pill-stat:hover { border-color: var(--gold); transform: translateX(10px); }
        .pill-stat label { font-size: 10px; color: #666; text-transform: uppercase; letter-spacing: 2px; }
        .pill-stat label i { margin-right: 10px; }
        .pill-stat span { font-family: 'Cinzel', serif; font-size: 24px; color: #fff; }

        /* --- Metric Cards --- */
        .metrics-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px; }
        .metric-card { background: var(--bg-card); border: 1px solid var(--border); padding: 30px; text-align: center; }
        .metric-card h3 { font-size: 9px; color: #555; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 12px; }
        .metric-card p { font-family: 'Cinzel', serif; font-size: 26px; color: var(--gold); margin: 0; }

        /* --- Tables --- */
        .ledger-box { background: var(--bg-card); border: 1px solid var(--border); margin-bottom: 40px; }
        .ledger-header { background: #080808; padding: 20px 30px; border-bottom: 1px solid var(--border); }
        .ledger-header h2 { font-family: 'Cinzel', serif; font-size: 14px; color: var(--gold); margin: 0; letter-spacing: 3px; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 18px 30px; font-size: 10px; text-transform: uppercase; color: #444; border-bottom: 1px solid #111; }
        td { padding: 18px 30px; font-size: 13px; border-bottom: 1px solid #111; color: #888; }
        
        .badge { padding: 5px 12px; font-size: 9px; text-transform: uppercase; font-weight: bold; border-radius: 2px; }
        .badge-pending { background: rgba(255, 200, 0, 0.1); color: #ffc800; border: 1px solid #ffc80033; }
        .badge-shipped { background: rgba(0, 229, 255, 0.1); color: var(--cyan); border: 1px solid #00e5ff33; }
        .badge-settled { background: rgba(0, 255, 100, 0.1); color: #00ff64; border: 1px solid #00ff6433; }

        /* --- Admin/Login Section --- */
        .security-container { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
        .profile-card { background: var(--bg-card); border: 1px solid var(--border); padding: 50px; text-align: center; }
        .avatar-circle { width: 90px; height: 90px; border: 1px solid var(--gold); margin: 0 auto 25px; display: flex; align-items: center; justify-content: center; font-size: 35px; color: var(--gold); border-radius: 50%; box-shadow: 0 0 20px rgba(197, 160, 89, 0.1); }
        
        .creds-list { background: var(--bg-card); border: 1px solid var(--border); padding: 30px; }
        .cred-item { display: flex; justify-content: space-between; padding: 20px 0; border-bottom: 1px solid #111; }
        .cred-item label { font-size: 10px; color: #555; text-transform: uppercase; letter-spacing: 2px; }
        .cred-item span { font-size: 13px; color: #eee; }

        .btn-update { background: var(--gold); color: #000; border: none; padding: 8px 16px; cursor: pointer; font-weight: 800; font-size: 9px; text-transform: uppercase; }
        .btn-export { background: #fff; color: #000; padding: 12px 25px; font-size: 11px; font-weight: bold; text-decoration: none; text-transform: uppercase; }
        .btn-logout { color: #ff4d4d; text-decoration: none; font-size: 11px; text-transform: uppercase; font-weight: 600; }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <div class="action-bar">
        <div class="header-title">
            <h1>EXECUTIVE TERMINAL</h1>
            <p>Node Online: <?php echo $current_date; ?> | Sync: <?php echo $current_time; ?></p>
        </div>
        <div style="display: flex; gap: 20px; align-items: center;">
            <a href="export_data.php" class="btn-export"><i class="fas fa-file-csv"></i> Export CSV</a>
            <a href="admin_logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <div class="dashboard-nav">
        <button class="nav-btn active" onclick="switchTab(event, 'main-dashboard')">Analytics & Orders</button>
        <button class="nav-btn" onclick="switchTab(event, 'registry')">Member Collective</button>
        <button class="nav-btn" onclick="switchTab(event, 'security')">Security Credentials</button>
    </div>

    <div id="main-dashboard" class="tab-panel active">
        <div class="viz-layout">
            <div class="chart-container">
                <h3 style="font-size: 10px; color:#555; margin-top:0; letter-spacing: 2px;">LIFECYCLE VISUALIZATION</h3>
                <canvas id="orderChart"></canvas>
            </div>
            <div class="lifecycle-sidebar">
                <div class="pill-stat">
                    <label><i class="fas fa-hourglass-start"></i> Pending Orders</label>
                    <span><?php echo $p_count; ?></span>
                </div>
                <div class="pill-stat">
                    <label><i class="fas fa-shipping-fast"></i> In Transit</label>
                    <span><?php echo $s_count; ?></span>
                </div>
                <div class="pill-stat">
                    <label><i class="fas fa-check-double"></i> Settled Pieces</label>
                    <span><?php echo $d_count; ?></span>
                </div>
            </div>
        </div>

        <div class="metrics-grid">
            <div class="metric-card"><h3>Gross Revenue</h3><p>₹<?php echo number_format($total_revenue, 0); ?></p></div>
            <div class="metric-card"><h3>Active Pipeline</h3><p><?php echo $active_pipeline; ?></p></div>
            <div class="metric-card"><h3>Collective Size</h3><p><?php echo $total_members; ?></p></div>
            <div class="metric-card"><h3>Inventory Volume</h3><p><?php echo $total_inv; ?></p></div>
        </div>

        <div class="ledger-box">
            <div class="ledger-header"><h2>PIPELINE CONTROL</h2></div>
            <table>
                <thead><tr><th>Registry ID</th><th>Client Identity</th><th>Value</th><th>Status</th><th>Protocol</th></tr></thead>
                <tbody>
                    <?php 
                    $orders = mysqli_query($conn, "SELECT * FROM orders WHERE status != 'Delivered' AND status != 'Cancelled' ORDER BY order_date DESC");
                    while($row = mysqli_fetch_assoc($orders)): ?>
                    <tr>
                        <td style="color: var(--gold); font-family: monospace;">#MKW-<?php echo $row['id']; ?></td>
                        <td><b style="color: #fff;"><?php echo strtoupper($row['customer_name']); ?></b></td>
                        <td>₹<?php echo number_format($row['total_amount'], 0); ?></td>
                        <td>
                            <span class="badge <?php echo ($row['status'] == 'Shipped') ? 'badge-shipped' : 'badge-pending'; ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display: flex; gap: 8px;">
                                <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                <select name="status" style="background:#000; color:#fff; border:1px solid #333; font-size:10px; padding: 5px;">
                                    <option value="Pending" <?php if($row['status']=='Pending') echo 'selected';?>>Pending</option>
                                    <option value="Shipped" <?php if($row['status']=='Shipped') echo 'selected';?>>Shipped</option>
                                    <option value="Delivered">Delivered</option>
                                </select>
                                <button type="submit" name="update_status" class="btn-update">Sync</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="registry" class="tab-panel">
        <div class="ledger-box">
            <div class="ledger-header"><h2>Member Collective Registry</h2></div>
            <table>
                <thead><tr><th>Member ID</th><th>Identity</th><th>Secure Email</th><th>Archive Join</th><th>Acquisitions</th></tr></thead>
                <tbody>
                    <?php 
                    $users = mysqli_query($conn, "SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.customer_name = u.username) as orders_placed FROM users u ORDER BY created_at DESC");
                    while($u = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td style="color: var(--gold);">#USR-<?php echo $u['id']; ?></td>
                        <td><b style="color: #fff;"><?php echo strtoupper($u['username']); ?></b></td>
                        <td><?php echo $u['email']; ?></td>
                        <td><?php echo date('d M, Y', strtotime($u['created_at'])); ?></td>
                        <td style="color: var(--gold); font-weight: bold;"><?php echo $u['orders_placed']; ?> Pieces</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="security" class="tab-panel">
        <div class="security-container">
            <div class="profile-card">
                <div class="avatar-circle"><i class="fas fa-shield-alt"></i></div>
                <h2 style="font-family: 'Cinzel';"><?php echo strtoupper($admin_identity); ?></h2>
                <p style="font-size: 10px; color: var(--gold); letter-spacing: 3px;">SYSTEM ADMINISTRATOR</p>
                <br><br>
                <a href="manage_products.php" class="btn-export" style="display: block; text-align: center;">Access Inventory Archive</a>
            </div>
            <div class="creds-list">
                <h3 style="font-family: 'Cinzel'; font-size: 15px; margin-bottom: 25px; color: var(--gold);">Identity Metadata</h3>
                <div class="cred-item"><label>Node Access ID</label><span><?php echo $admin_data['username']; ?></span></div>
                <div class="cred-item"><label>Linked Asset Email</label><span><?php echo $admin_data['email'] ?? 'internal_node@mkw.com'; ?></span></div>
                <div class="cred-item"><label>Security Privilege</label><span style="color: var(--gold);">ROOT / EXECUTIVE</span></div>
                <div class="cred-item"><label>Current Session</label><span>Encrypted (RSA-4096)</span></div>
                <div class="cred-item"><label>Server Uptime</label><span style="color: #00ff64;">99.9% / STABLE</span></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Tab Interaction Logic
    function switchTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-panel");
        for (i = 0; i < tabcontent.length; i++) { tabcontent[i].style.display = "none"; tabcontent[i].classList.remove("active"); }
        tablinks = document.getElementsByClassName("nav-btn");
        for (i = 0; i < tablinks.length; i++) { tablinks[i].classList.remove("active"); }
        document.getElementById(tabName).style.display = "block";
        document.getElementById(tabName).classList.add("active");
        evt.currentTarget.classList.add("active");
    }

    // Chart.js - Working Lifecycle "Chat" Visualization
    const ctx = document.getElementById('orderChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['PENDING', 'SHIPPED', 'DELIVERED'],
            datasets: [{
                label: 'Order Volume',
                data: [<?php echo $p_count; ?>, <?php echo $s_count; ?>, <?php echo $d_count; ?>],
                borderColor: '#c5a059',
                backgroundColor: 'rgba(197, 160, 89, 0.05)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(255, 255, 255, 0.03)' }, ticks: { color: '#444' } },
                x: { grid: { display: false }, ticks: { color: '#666', font: { size: 10, family: 'Inter' } } }
            }
        }
    });
</script>

</body>
</html>