<?php 
include 'db.php'; 
session_start(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MKW Originals | Premium Indian Couture</title>
    <style>
        /* --- Premium Typography & Variables --- */
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@100;300;400;600&display=swap');

        :root {
            --gold: #c5a059;
            --dark: #000000;
            --obsidian: #0c0c0d;
            --white: #ffffff;
            --muted: #666666;
            --border: rgba(197, 160, 89, 0.15);
            --transition: 0.6s cubic-bezier(0.19, 1, 0.22, 1);
        }

        /* --- Global Reset --- */
        html { scroll-behavior: smooth; }
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--dark);
            color: var(--white);
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1, h2, h3, .logo { font-family: 'Cinzel', serif; font-weight: 400; letter-spacing: 2px; }

        /* --- Navigation --- */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 0 0 8%;
            background: var(--dark);
            height: 90px;
            position: sticky;
            top: 0;
            z-index: 2000;
            border-bottom: 1px solid #111;
        }

        .logo a {
            font-size: 20px;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: var(--gold);
            text-decoration: none;
        }

        .nav-right-wrapper { display: flex; align-items: center; height: 100%; }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 40px;
            list-style: none;
            margin-right: 50px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 3px;
            transition: var(--transition);
        }

        .nav-links a:hover { color: var(--white); }

        .nav-actions { display: flex; align-items: center; height: 100%; }

        .nav-btn {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--white);
            text-decoration: none;
            margin-right: 30px;
            font-weight: 600;
            transition: var(--transition);
        }

        .user-greeting {
            font-size: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--gold);
            margin-right: 20px;
        }

        .logout-link { color: #8b3a3a !important; }
        .logout-link:hover { color: #ff4d4d !important; }

        /* --- The Signature Gold Block Cart --- */
        .cart-icon {
            background: var(--gold);
            color: #000 !important;
            height: 100%;
            padding: 0 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-decoration: none;
            transition: var(--transition);
            margin-left: 20px;
        }

        .cart-icon:hover {
            background: var(--white);
            color: #000 !important;
            padding: 0 60px;
            cursor: pointer;
        }

        /* --- Hero Section --- */
        .hero {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.4)), 
                        url('https://images.unsplash.com/photo-1593032465175-481ac7f401a0?q=80&w=2080&auto=format&fit=crop') center/cover;
            background-attachment: fixed;
        }

        .hero h1 {
            font-size: clamp(2.5rem, 8vw, 5rem);
            letter-spacing: 15px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        /* --- Category Grid --- */
        .categories { display: flex; flex-wrap: wrap; background: var(--dark); width: 100%; }

        .cat-card {
            flex: 1;
            min-width: 33.333%;
            height: 90vh;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            background-size: cover;
            background-position: center;
        }

        .cat-card.men { background-image: url('https://images.unsplash.com/photo-1507679799987-c73779587ccf?q=80&w=2071&auto=format&fit=crop') !important; }
        .cat-card.women { background-image: url('https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=2070&auto=format&fit=crop') !important; }
        .cat-card.kids { background-image: url('https://images.unsplash.com/photo-1503919545889-aef636e10ad4?q=80&w=1974&auto=format&fit=crop') !important; }

        .cat-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.2) 60%, transparent 100%);
            transition: var(--transition);
            z-index: 1;
        }

        .cat-card:hover::after { background: rgba(0,0,0,0.1); }

        .cat-content {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 60px;
            z-index: 2;
        }

        .cat-content h2 {
            font-size: 32px;
            margin-bottom: 10px;
            color: var(--white);
            text-transform: uppercase;
        }

        .buy-now-btn {
            width: fit-content;
            background: transparent;
            border: 1px solid var(--gold);
            color: var(--gold);
            padding: 15px 35px;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-size: 10px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-weight: bold;
        }

        .buy-now-btn:hover { background: var(--gold); color: #000; transform: translateY(-5px); }

        @media (max-width: 992px) {
            .nav-links { display: none; }
            .cat-card { min-width: 100%; height: 60vh; }
            nav { padding-left: 5%; }
        }
    </style>
</head>
<body>

<nav>
    <div class="nav-left">
        <div class="logo"><a href="index.php">MKW Originals</a></div>
    </div>
    
    <div class="nav-right-wrapper">
        <ul class="nav-links">
            <li><a href="category.php?type=men">Men</a></li>
            <li><a href="category.php?type=women">Women</a></li>
            <li><a href="category.php?type=kids">Kids</a></li>
            <li><a href="about.php">About</a></li>
        </ul>

        <div class="nav-actions">
            <?php if(isset($_SESSION['username'])): ?>
                <span class="user-greeting">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="track_orders.php" class="nav-btn">Track</a>
                <a href="logout.php" class="nav-btn logout-link">Logout</a>
            <?php else: ?>
                <a href="login.php" class="nav-btn">Sign In</a>
            <?php endif; ?>
            
            <a href="cart.php" class="cart-icon">
                CART (<?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>)
            </a>
        </div>
    </div>
</nav>

<header class="hero">
    <p style="color: var(--gold); letter-spacing: 8px; font-size: 10px; text-transform: uppercase; margin-bottom: 20px;">Est. 2026</p>
    <h1>MKW ORIGINALS</h1>
    <a href="#collections" class="buy-now-btn" style="margin-top: 30px;">Discover Archive</a>
</header>

<section class="categories" id="collections">
    <div class="cat-card men" onclick="location.href='category.php?type=men'">
        <div class="cat-content">
            <h2>The Men</h2>
            <p style="color: #aaa; font-size: 10px; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 30px;">Tailored Traditions</p>
            <span class="buy-now-btn">Explore</span>
        </div>
    </div>

    <div class="cat-card women" onclick="location.href='category.php?type=women'">
        <div class="cat-content">
            <h2>The Women</h2>
            <p style="color: #aaa; font-size: 10px; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 30px;">Elegant Silhouettes</p>
            <span class="buy-now-btn">Explore</span>
        </div>
    </div>

    <div class="cat-card kids" onclick="location.href='category.php?type=kids'">
        <div class="cat-content">
            <h2>The Kids</h2>
            <p style="color: #aaa; font-size: 10px; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 30px;">Little Luxuries</p>
            <span class="buy-now-btn">Explore</span>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
</body>
</html>