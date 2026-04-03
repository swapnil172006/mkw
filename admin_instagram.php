<?php 
include 'db.php'; 
session_start(); 

// Security Check: Uncomment when login system is ready
// if(!isset($_SESSION['is_admin'])) { header("Location: login.php"); exit; }

// Handle Status Messages
$status_msg = "";
if(isset($_GET['status'])) {
    if($_GET['status'] == 'success') $status_msg = "Archive Piece Published Successfully.";
    if($_GET['status'] == 'deleted') $status_msg = "Piece Removed from Archive.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | MKW Originals Archive</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Inter:wght@300;400;600&display=swap');

        :root {
            --gold: #c5a059;
            --dark: #050505;
            --bg: #111;
            --white: #ffffff;
            --border: #222;
            --danger: #ff4d4d;
        }

        body { background: var(--dark); color: var(--white); font-family: 'Inter', sans-serif; margin: 0; }
        
        .admin-container { max-width: 1200px; margin: 40px auto; padding: 20px; }
        
        /* Alert Message */
        .status-alert { 
            background: rgba(197, 160, 89, 0.1); 
            border: 1px solid var(--gold); 
            color: var(--gold); 
            padding: 15px; 
            margin-bottom: 30px; 
            font-size: 13px; 
            letter-spacing: 1px;
            text-align: center;
            text-transform: uppercase;
        }

        /* Header */
        .admin-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid var(--border); 
            padding-bottom: 20px; 
            margin-bottom: 40px; 
        }
        
        .admin-header h1 { font-family: 'Cinzel', serif; letter-spacing: 3px; font-size: 24px; margin: 0; }

        /* Upload Form Card */
        .upload-card {
            background: var(--bg);
            border: 1px solid var(--border);
            padding: 30px;
            border-radius: 4px;
            margin-bottom: 50px;
        }

        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        
        label { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: var(--gold); margin-bottom: 10px; }
        
        input, select {
            width: 100%;
            background: #000;
            border: 1px solid var(--border);
            color: white;
            padding: 12px;
            font-size: 13px;
            outline: none;
            transition: 0.3s;
        }

        input:focus { border-color: var(--gold); }

        .btn-upload {
            background: var(--gold);
            color: black;
            border: none;
            padding: 15px 40px;
            font-weight: 800;
            letter-spacing: 2px;
            cursor: pointer;
            margin-top: 30px;
            width: 100%;
            text-transform: uppercase;
            transition: 0.4s;
        }

        .btn-upload:hover { background: white; }

        /* Post Management Table */
        .post-table { width: 100%; border-collapse: collapse; background: var(--bg); margin-top: 20px; }
        .post-table th { text-align: left; padding: 18px; background: #000; font-size: 11px; color: var(--gold); text-transform: uppercase; letter-spacing: 2px; border-bottom: 2px solid var(--border); }
        .post-table td { padding: 18px; border-bottom: 1px solid var(--border); font-size: 13px; vertical-align: middle; }
        
        .thumb { width: 60px; height: 60px; object-fit: cover; border: 1px solid var(--border); border-radius: 2px; }
        
        .badge-video { color: var(--danger); font-size: 10px; font-weight: bold; border: 1px solid var(--danger); padding: 4px 8px; border-radius: 3px; }
        .badge-image { color: #4dadff; font-size: 10px; font-weight: bold; border: 1px solid #4dadff; padding: 4px 8px; border-radius: 3px; }

        .action-btn { color: #888; text-decoration: none; margin-right: 15px; font-size: 16px; transition: 0.3s; }
        .action-btn:hover { color: var(--white); }
        .delete-btn:hover { color: var(--danger); }

        .section-title { font-size: 12px; text-transform: uppercase; letter-spacing: 3px; color: var(--gold); margin-bottom: 20px; display: block; }
    </style>
</head>
<body>

<?php include 'nav.php'; ?>

<div class="admin-container">
    
    <?php if($status_msg): ?>
        <div class="status-alert"><?php echo $status_msg; ?></div>
    <?php endif; ?>

    <div class="admin-header">
        <h1>Archive Management</h1>
        <a href="instagram.php" class="action-btn" target="_blank" style="font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
            <i class="fas fa-external-link-alt"></i> Public Feed
        </a>
    </div>

    <div class="upload-card">
        <span class="section-title">Publish New Piece</span>
        <form action="process_ig.php" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div>
                    <label>File Upload</label>
                    <input type="file" name="ig_image" accept="image/*,video/mp4" required>
                </div>
                <div>
                    <label>Content Type</label>
                    <select name="type">
                        <option value="image">Still Photography (Image)</option>
                        <option value="video">Cinematic/Reel (Video)</option>
                    </select>
                </div>
                <div>
                    <label>Likes (Initial)</label>
                    <input type="text" name="likes" placeholder="e.g., 1.2k" required>
                </div>
                <div>
                    <label>Comments (Initial)</label>
                    <input type="text" name="comments" placeholder="e.g., 45" required>
                </div>
            </div>
            <button type="submit" class="btn-upload">Upload to Archive</button>
        </form>
    </div>

    <span class="section-title">Active Archive Grid</span>
    <table class="post-table">
        <thead>
            <tr>
                <th>Preview</th>
                <th>Details</th>
                <th>Type</th>
                <th>Engagement</th>
                <th>Added On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = mysqli_query($conn, "SELECT * FROM instagram_posts ORDER BY id DESC");
            
            if(mysqli_num_rows($result) > 0):
                while($row = mysqli_fetch_assoc($result)):
            ?>
            <tr>
                <td>
                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" class="thumb" alt="Preview">
                </td>
                
                <td>
                    <div style="font-size: 10px; color: #555;">Filename:</div>
                    <div style="font-family: monospace; color: #aaa;"><?php echo basename($row['image_path']); ?></div>
                </td>

                <td>
                    <?php if($row['content_type'] == 'video'): ?>
                        <span class="badge-video"><i class="fas fa-video"></i> REEL</span>
                    <?php else: ?>
                        <span class="badge-image"><i class="fas fa-image"></i> PHOTO</span>
                    <?php endif; ?>
                </td>

                <td>
                    <div style="display: flex; gap: 15px;">
                        <span><i class="fas fa-heart" style="color: var(--danger);"></i> <?php echo $row['likes']; ?></span>
                        <span><i class="fas fa-comment" style="color: var(--gold);"></i> <?php echo $row['comments']; ?></span>
                    </div>
                </td>

                <td style="color: #666; font-size: 12px;">
                    <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                </td>

                <td>
                    <div style="display: flex; gap: 12px;">
                        <a href="edit_ig.php?id=<?php echo $row['id']; ?>" class="action-btn" title="Edit Metrics">
                            <i class="fas fa-pen-nib"></i>
                        </a>
                        <a href="delete_ig.php?id=<?php echo $row['id']; ?>" 
                           class="action-btn delete-btn" 
                           onclick="return confirm('Permanently remove this piece from the archive?');" 
                           title="Delete Piece">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php 
                endwhile; 
            else: 
            ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 60px; color: #444;">
                    <i class="fas fa-box-open" style="font-size: 40px; margin-bottom: 15px; display: block;"></i>
                    The MKW Archive is currently empty.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>

</body>
</html>