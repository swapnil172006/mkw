<?php 
include 'db.php'; 
session_start(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instagram Feed | MKW Originals</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --ig-bg: #fafafa;
            --ig-border: #dbdbdb;
            --ig-text: #262626;
            --ig-link: #0095f6;
            --story-gradient: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);
        }

        body { background-color: var(--ig-bg); color: var(--ig-text); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; }

        .ig-header-container { max-width: 935px; margin: 30px auto; padding: 0 20px; }

        /* --- Header Section --- */
        .ig-header { display: flex; align-items: center; gap: 80px; margin-bottom: 44px; }
        .profile-pic { width: 150px; height: 150px; border-radius: 50%; border: 1px solid var(--ig-border); padding: 3px; object-fit: cover; }
        
        .profile-info h1 { font-size: 28px; font-weight: 300; margin: 0; display: inline-block; }
        .follow-btn { background: var(--ig-link); color: white; border: none; padding: 7px 24px; font-weight: 600; font-size: 14px; border-radius: 4px; cursor: pointer; margin-left: 20px; }
        
        .stats { display: flex; gap: 40px; margin: 20px 0; list-style: none; padding: 0; }
        .stats li { font-size: 16px; }
        .bio { font-size: 16px; line-height: 24px; }
        .bio b { font-weight: 600; }
        
        /* --- Highlights Section --- */
        .highlights { display: flex; gap: 45px; margin-bottom: 50px; padding: 10px 0; border-bottom: 1px solid var(--ig-border); padding-bottom: 40px; }
        .highlight-item { display: flex; flex-direction: column; align-items: center; gap: 10px; cursor: pointer; border: none; background: none; }
        
        .highlight-circle { 
            width: 77px; height: 77px; 
            border-radius: 50%; 
            padding: 3px;
            background: var(--ig-border); 
            transition: transform 0.2s;
        }

        .highlight-item:hover .highlight-circle { background: var(--story-gradient); transform: scale(1.05); }
        .highlight-circle img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 2px solid white; }
        .highlight-item span { font-size: 12px; font-weight: 600; color: var(--ig-text); }

        /* --- Grid Section --- */
        .ig-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }
        .ig-post { position: relative; aspect-ratio: 1 / 1; background: #efefef; cursor: pointer; overflow: hidden; }
        .ig-post img { width: 100%; height: 100%; object-fit: cover; transition: 0.3s; }
        
        .reel-badge { position: absolute; top: 12px; right: 12px; color: white; font-size: 20px; z-index: 5; }

        .ig-overlay {
            position: absolute; inset: 0; background: rgba(0, 0, 0, 0.3);
            display: flex; justify-content: center; align-items: center;
            opacity: 0; transition: 0.2s; z-index: 10;
        }
        .ig-post:hover .ig-overlay { opacity: 1; }
        .ig-post:hover img { transform: scale(1.05); }
        .ig-stats { color: white; font-size: 18px; font-weight: 700; display: flex; gap: 30px; }
        .ig-stats i { margin-right: 7px; }

        /* --- Story Modal --- */
        .story-modal {
            position: fixed; inset: 0; background: rgba(0,0,0,0.95);
            z-index: 10000; display: none; justify-content: center; align-items: center;
        }
        .story-content {
            position: relative; width: 100%; max-width: 400px; height: 80vh;
            background: #111; border-radius: 8px; overflow: hidden;
        }
        .story-content img { width: 100%; height: 100%; object-fit: cover; }
        .close-story { position: absolute; top: 20px; right: 30px; color: white; font-size: 35px; cursor: pointer; z-index: 10001; }
        .story-progress { position: absolute; top: 10px; left: 10px; right: 10px; height: 2px; background: rgba(255,255,255,0.3); }
        .story-bar { height: 100%; background: white; width: 0%; }

        @media (max-width: 768px) {
            .ig-header { flex-direction: column; text-align: center; gap: 20px; }
            .stats { justify-content: center; gap: 20px; }
            .highlights { gap: 15px; overflow-x: scroll; }
            .ig-grid { gap: 3px; }
        }
    </style>
</head>
<body>

<?php include 'nav.php'; ?>

<div class="ig-header-container">
    <div class="ig-header">
        <img src="https://images.unsplash.com/photo-1507679799987-c73779587ccf?q=80&w=300" class="profile-pic">
        <div class="profile-info">
            <h1>mkworiginals</h1>
            <button class="follow-btn">Follow</button>
            <ul class="stats">
                <li><b>5</b> posts</li>
                <li><b>12.5k</b> followers</li>
                <li><b>342</b> following</li>
            </ul>
            <div class="bio">
                <b>MKW ORIGINALS</b><br>
                Couture • Atelier • Heritage<br>
                The Pinnacle of Modern Indian Craftsmanship.<br>
                <a href="index.php" style="color: #00376b; text-decoration: none; font-weight: 600;">mkworiginals.com</a>
            </div>
        </div>
    </div>

    <div class="highlights">
        <button class="highlight-item" onclick="openStory('https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=800')">
            <div class="highlight-circle"><img src="https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=150"></div>
            <span>Men '26</span>
        </button>

        <button class="highlight-item" onclick="openStory('https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=800')">
            <div class="highlight-circle"><img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=150"></div>
            <span>Women</span>
        </button>

        <button class="highlight-item" onclick="openStory('https://images.unsplash.com/photo-1503919545889-aef636e10ad4?w=800')">
            <div class="highlight-circle"><img src="https://images.unsplash.com/photo-1503919545889-aef636e10ad4?w=150"></div>
            <span>Junior</span>
        </button>

        <button class="highlight-item" onclick="openStory('https://images.unsplash.com/photo-1583394838336-acd977730f90?w=800')">
            <div class="highlight-circle"><img src="https://images.unsplash.com/photo-1583394838336-acd977730f90?w=150"></div>
            <span>Atelier</span>
        </button>
    </div>

    <div class="ig-grid">
        <?php
        $result = mysqli_query($conn, "SELECT * FROM instagram_posts ORDER BY id DESC");
        if(mysqli_num_rows($result) > 0):
            while($row = mysqli_fetch_assoc($result)):
        ?>
        <div class="ig-post">
            <?php if($row['content_type'] == 'video'): ?>
                <div class="reel-badge"><i class="fas fa-video"></i></div>
            <?php endif; ?>
            
            <img src="<?php echo htmlspecialchars($row['image_path']); ?>">
            
            <div class="ig-overlay">
                <div class="ig-stats">
                    <span><i class="fas fa-heart"></i> <?php echo $row['likes']; ?></span>
                    <span><i class="fas fa-comment"></i> <?php echo $row['comments']; ?></span>
                </div>
            </div>
        </div>
        <?php 
            endwhile; 
        else: 
        ?>
            <p style="grid-column: span 3; text-align: center; color: #8e8e8e; padding: 50px;">Archive is currently being curated...</p>
        <?php endif; ?>
    </div>
</div>

<div id="storyModal" class="story-modal">
    <span class="close-story" onclick="closeStory()">&times;</span>
    <div class="story-content">
        <div class="story-progress"><div id="storyBar" class="story-bar"></div></div>
        <img id="storyImg" src="">
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    let progressInterval;
    function openStory(imgSrc) {
        const modal = document.getElementById('storyModal');
        const img = document.getElementById('storyImg');
        const bar = document.getElementById('storyBar');
        img.src = imgSrc;
        modal.style.display = 'flex';
        let width = 0;
        bar.style.width = '0%';
        clearInterval(progressInterval);
        progressInterval = setInterval(() => {
            if (width >= 100) { closeStory(); } 
            else { width += 1; bar.style.width = width + '%'; }
        }, 40); 
    }
    function closeStory() {
        document.getElementById('storyModal').style.display = 'none';
        clearInterval(progressInterval);
    }
</script>

</body>
</html>