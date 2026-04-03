<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Story | MKW Originals</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .about-hero {
            display: flex;
            min-height: 100vh;
            align-items: stretch;
            background: var(--dark);
        }

        .about-image {
            flex: 1;
            background: url('https://images.unsplash.com/photo-1558769132-cb1aea458c5e?auto=format&fit=crop&q=80') center/cover;
            position: relative;
        }

        /* Subtle gold overlay on the image */
        .about-image::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(197, 160, 89, 0.1);
        }

        .about-text {
            flex: 1;
            padding: 10% 8%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .about-text span {
            font-size: 10px;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 20px;
            display: block;
        }

        .about-text h1 {
            font-size: clamp(2rem, 5vw, 3.5rem);
            margin-bottom: 30px;
            line-height: 1.1;
        }

        .about-text p {
            font-size: 15px;
            color: var(--muted);
            max-width: 500px;
            margin-bottom: 25px;
            line-height: 1.8;
        }

        .philosophy-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 50px;
            border-top: 1px solid var(--border);
            padding-top: 50px;
        }

        .phil-item h3 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
            color: var(--white);
        }

        .phil-item p {
            font-size: 12px;
            margin-bottom: 0;
        }

        @media (max-width: 992px) {
            .about-hero { flex-direction: column; }
            .about-image { height: 50vh; }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="about-hero">
    <div class="about-image"></div>

    <div class="about-text">
        <span>The MKW Legacy</span>
        <h1>Crafting the <br>Invisible Detail.</h1>
        
        <p>
            Founded on the principles of timeless elegance and uncompromising quality, 
            MKW Originals was born in the heart of the modern atelier. We don't just 
            create garments; we curate experiences that linger.
        </p>

        <p>
            Every thread is chosen with intention, and every silhouette is designed 
            to celebrate the individuality of those who wear us. From our Men's 
            bespoke tailoring to the delicate intricacies of our Kids' collection, 
            excellence is our only standard.
        </p>

        <div class="philosophy-grid">
            <div class="phil-item">
                <h3>Our Ethos</h3>
                <p>Sustainable luxury that respects the hands that create it and the planet that provides it.</p>
            </div>
            <div class="phil-item">
                <h3>The Vision</h3>
                <p>To redefine the modern wardrobe through a lens of obsidian shadows and gold highlights.</p>
            </div>
        </div>
        
        <a href="index.php" class="buy-now-btn" style="margin-top: 50px;">Explore Collection</a>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>