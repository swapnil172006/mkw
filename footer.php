<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-column brand-story">
            <h2 class="footer-logo">MKW <span class="gold-text">Originals</span></h2>
            <p>
                Founded in 2026, MKW Originals represents the pinnacle of modern Indian craftsmanship. 
                Our mission is to blend traditional heritage with contemporary luxury through digital innovation.
            </p>
            <div class="social-links">
                <a href="https://www.instagram.com/mkworiginals/" target="_blank">Instagram</a>
                <a href="#">Pinterest</a>
                <a href="#">Twitter/X</a>
            </div>
        </div>

        <div class="footer-column">
            <h4>Collections</h4>
            <ul>
                <li><a href="category.php?type=men">Men's Couture</a></li>
                <li><a href="category.php?type=women">Women's Atelier</a></li>
                <li><a href="category.php?type=kids">Junior Collection</a></li>
                <li><a href="#">Seasonal Archive</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h4>Assistance</h4>
            <ul>
                <li><a href="track_orders.php">Track Order</a></li>
                <li><a href="#">Shipping & Returns</a></li>
                <li><a href="#">Size Guide</a></li>
                <li><a href="#">Contact Us</a></li>
            </ul>
        </div>

        <div class="footer-column newsletter">
            <h4>The Atelier Newsletter</h4>
            <p>Subscribe to receive exclusive access to new collections and private events.</p>
            <form class="newsletter-form" action="#" method="POST">
                <input type="email" placeholder="EMAIL ADDRESS" required>
                <button type="submit">JOIN</button>
            </form>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="payment-methods">
            <span>VISA</span>
            <span>MASTERCARD</span>
            <span>AMEX</span>
            <span>UPI</span>
        </div>
        <div class="copyright">
            &copy; 2026 MKW Originals. Crafted in India. <span class="atelier-stamp">Digital Atelier v3.1</span>
        </div>
    </div>
</footer>

<style>
    /* --- Core Variables --- */
    :root {
        --gold: #c5a059;
        --white: #ffffff;
        --bg-dark: #050505;
        --glass: rgba(255, 255, 255, 0.03);
        --transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .main-footer {
        background: var(--bg-dark);
        border-top: 1px solid #1a1a1c;
        padding: 100px 8% 40px 8%;
        font-family: 'Inter', sans-serif;
    }

    .footer-container {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1fr 1.5fr;
        gap: 60px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .footer-logo { font-family: 'Cinzel', serif; letter-spacing: 6px; text-transform: uppercase; font-size: 24px; color: var(--white); margin-bottom: 25px; }
    .gold-text { color: var(--gold); text-shadow: 0 0 15px rgba(197, 160, 89, 0.3); }

    .footer-column h4 { color: var(--gold); text-transform: uppercase; letter-spacing: 4px; margin-bottom: 30px; font-size: 10px; font-weight: 700; }
    .footer-column p { color: #888; line-height: 1.8; font-size: 13px; }
    .footer-column ul { list-style: none; padding: 0; }
    .footer-column li { margin-bottom: 15px; }
    .footer-column a { color: #666; text-decoration: none; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; transition: var(--transition); }
    .footer-column a:hover { color: var(--white); padding-left: 8px; }

    /* Magnetic Target Styling (Ensures smooth movement) */
    .pay-btn, .buy-now-btn {
        display: inline-block;
        will-change: transform;
        transition: transform 0.2s cubic-bezier(0.23, 1, 0.32, 1), background 0.3s ease;
    }

    .social-links { display: flex; gap: 12px; margin-top: 30px; }
    .social-links a { font-size: 9px; color: #777; border: 1px solid #1a1a1c; padding: 10px 16px; background: var(--glass); backdrop-filter: blur(5px); text-decoration: none; transition: var(--transition); }
    .social-links a:hover { border-color: var(--gold); color: var(--gold); transform: translateY(-5px); }

    .footer-bottom { margin-top: 100px; padding-top: 40px; border-top: 1px solid #121212; display: flex; flex-direction: column; align-items: center; gap: 25px; }
    .payment-methods { display: flex; gap: 30px; color: #222; font-size: 9px; letter-spacing: 4px; font-weight: 900; }
    .copyright { color: #444; font-size: 9px; letter-spacing: 4px; text-transform: uppercase; display: flex; align-items: center; gap: 15px; }
    .atelier-stamp { font-size: 8px; color: #222; border: 1px solid #111; padding: 2px 8px; }

    @media (max-width: 1100px) { .footer-container { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 768px) { .footer-container { grid-template-columns: 1fr; text-align: center; } .social-links { justify-content: center; } }
</style>

<script>
/**
 * MKW ORIGINALS - MASTER INTERFACE SCRIPT v3.1
 * Includes: Navbar Hide/Show & Magnetic Button Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // 1. MAGNETIC BUTTON LOGIC
    const magneticButtons = document.querySelectorAll('.pay-btn, .buy-now-btn, .clear-bag-btn');

    magneticButtons.forEach(btn => {
        btn.addEventListener('mousemove', function(e) {
            const position = btn.getBoundingClientRect();
            // Calculate relative mouse position
            const x = e.clientX - position.left - position.width / 2;
            const y = e.clientY - position.top - position.height / 2;
            
            // Apply magnetic pull (0.3 and 0.5 are intensity multipliers)
            btn.style.transform = `translate(${x * 0.3}px, ${y * 0.4}px)`;
        });

        btn.addEventListener('mouseleave', function() {
            // Reset to original position
            btn.style.transform = 'translate(0px, 0px)';
        });
    });

    // 2. NAVBAR SCROLL LOGIC
    let lastScrollTop = 0;
    const navbarElement = document.querySelector('nav');

    window.addEventListener('scroll', function() {
        window.requestAnimationFrame(() => {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > 120) {
                if (scrollTop > lastScrollTop) {
                    // Scrolling Down
                    if(navbarElement) {
                        navbarElement.style.transform = "translateY(-100%)";
                        navbarElement.style.opacity = "0";
                    }
                } else {
                    // Scrolling Up
                    if(navbarElement) {
                        navbarElement.style.transform = "translateY(0)";
                        navbarElement.style.opacity = "1";
                        navbarElement.style.background = "rgba(5, 5, 5, 0.95)";
                        navbarElement.style.backdropFilter = "blur(10px)";
                    }
                }
            } else {
                // At the top
                if(navbarElement) {
                    navbarElement.style.transform = "translateY(0)";
                    navbarElement.style.opacity = "1";
                    navbarElement.style.background = "transparent";
                    navbarElement.style.backdropFilter = "none";
                }
            }
            lastScrollTop = scrollTop;
        });
    }, false);

});
</script>