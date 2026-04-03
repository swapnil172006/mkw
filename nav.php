<nav id="main-navbar">
    <div class="nav-left">
        <div class="logo">
            <a href="index.php">MKW <span class="gold-accent">Originals</span></a>
        </div>
    </div>
    
    <div class="nav-right-wrapper">
        <ul class="nav-links">
            <li><a href="category.php?type=men">Men</a></li>
            <li><a href="category.php?type=women">Women</a></li>
            <li><a href="category.php?type=kids">Kids</a></li>
            <li><a href="instagram.php">Archive</a></li>
            <li><a href="about.php">About</a></li>
        </ul>

        <div class="nav-actions">
            <?php if(isset($_SESSION['username'])): ?>
                <span class="nav-user-status">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
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

<style>
/* --- Premium Variables --- */
:root {
    --gold: #c5a059;
    --dark: #000000;
    --white: #ffffff;
    --muted: #888888;
    --nav-transition: transform 0.6s cubic-bezier(0.19, 1, 0.22, 1);
}

/* --- Navbar Layout --- */
#main-navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 0 0 8%;
    background: var(--dark);
    height: 90px;
    position: sticky;
    top: 0;
    left: 0;
    right: 0;
    z-index: 9999;
    border-bottom: 1px solid #111;
    transition: var(--nav-transition);
}

.logo a {
    font-family: 'Cinzel', serif;
    color: var(--white);
    text-decoration: none;
    font-size: 20px;
    text-transform: uppercase;
    letter-spacing: 5px;
    font-weight: 400;
}

.gold-accent { color: var(--gold); }

.nav-right-wrapper { display: flex; align-items: center; height: 100%; }

.nav-links {
    display: flex;
    list-style: none;
    gap: 35px;
    margin-right: 40px;
}

.nav-links a {
    text-decoration: none;
    color: var(--muted);
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 2px;
    transition: color 0.3s ease;
}

.nav-links a:hover { color: var(--white); }

.nav-actions { display: flex; align-items: center; height: 100%; }

.nav-user-status {
    font-size: 9px;
    color: var(--gold);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-right: 15px;
}

.nav-btn {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: var(--white);
    text-decoration: none;
    margin-right: 25px;
    font-weight: 600;
    padding: 10px 18px;
    border: 1px solid transparent;
    transition: all 0.3s ease;
}

.nav-btn:hover { color: var(--gold); }

.logout-link { color: #8b3a3a !important; }

/* The Signature Gold Block Cart */
.cart-icon {
    background: var(--gold);
    color: #000 !important;
    height: 100%;
    padding: 0 45px;
    display: flex;
    align-items: center;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 2px;
    text-decoration: none;
    transition: var(--nav-transition);
}

.cart-icon:hover {
    background: var(--white);
    padding: 0 55px; /* Dramatic Elastic Stretch */
}

@media (max-width: 992px) {
    .nav-links, .nav-user-status { display: none; }
}
</style>

<script>
/* --- Scroll to Hide Navbar Script --- */
let lastScroll = 0;
const navElement = document.getElementById('main-navbar');

window.addEventListener('scroll', function() {
    let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    
    if (currentScroll > 150) { 
        if (currentScroll > lastScroll) {
            navElement.style.transform = "translateY(-100%)"; 
        } else {
            navElement.style.transform = "translateY(0)"; 
        }
    } else {
        navElement.style.transform = "translateY(0)"; 
    }
    lastScroll = currentScroll;
});
</script>