<?php
session_start();
require 'db.php'; 

// ==========================================
// 1. LOGIN & SECURITY
// ==========================================
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: admin.php");
    exit();
}

if (isset($_POST['login_submit'])) {
    if ($_POST['username'] == 'admin' && $_POST['password'] == 'nga2026') {
        $_SESSION['user_logged_in'] = true;
        header("Location: admin.php");
        exit();
    }
}

if (!isset($_SESSION['user_logged_in'])) {
    echo '<div style="font-family: sans-serif; max-width: 300px; margin: 100px auto; text-align: center;">
            <h2>Admin Login</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" style="width: 100%; padding: 10px; margin: 10px 0;">
                <input type="password" name="password" placeholder="Password" style="width: 100%; padding: 10px; margin: 10px 0;">
                <button type="submit" name="login_submit" style="width: 100%; padding: 10px; background: #2F80ED; color: white; border: none; cursor: pointer;">Login</button>
            </form>
          </div>';
    exit(); 
}

// ==========================================
// 2. PAGE CONTROLLER
// ==========================================
$current_page = isset($_GET['page']) ? $_GET['page'] : 'index';

// --- NEW INBOX FEATURE: Get Unread Message Count ---
$unread_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM messages WHERE is_read = 0");
$unread_data = mysqli_fetch_assoc($unread_query);
$unread_count = $unread_data['count'];

// --- NEW INBOX FEATURE: Mark as Read & Delete Logic ---
if (isset($_GET['mark_read'])) {
    $msg_id = (int)$_GET['mark_read'];
    mysqli_query($conn, "UPDATE messages SET is_read = 1 WHERE id = $msg_id");
    header("Location: admin.php?page=inbox");
    exit();
}

if (isset($_GET['delete_msg'])) {
    $msg_id = (int)$_GET['delete_msg'];
    mysqli_query($conn, "DELETE FROM messages WHERE id = $msg_id");
    header("Location: admin.php?page=inbox");
    exit();
}

// ==========================================
// 3. POST ADD / UPDATE / DELETE LOGIC (For Pages)
// ==========================================
if (isset($_POST['save_post'])) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $page_name = mysqli_real_escape_string($conn, $_POST['page_name']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $video_url = mysqli_real_escape_string($conn, $_POST['video_url']);
    $link_url = mysqli_real_escape_string($conn, $_POST['link_url']);
    $location_map = mysqli_real_escape_string($conn, $_POST['location_map']);
    
    $image_path = isset($_POST['existing_image']) ? $_POST['existing_image'] : "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $filename = uniqid() . "_" . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $filename)) {
            $image_path = 'uploads/' . $filename; 
        }
    }

    if ($id > 0) {
        $sql = "UPDATE posts SET page_name='$page_name', title='$title', content='$content', image_path='$image_path', video_url='$video_url', link_url='$link_url', location_map='$location_map' WHERE id=$id";
    } else {
        $sql = "INSERT INTO posts (page_name, title, content, image_path, video_url, link_url, location_map) VALUES ('$page_name', '$title', '$content', '$image_path', '$video_url', '$link_url', '$location_map')";
    }
    
    mysqli_query($conn, $sql);
    header("Location: admin.php?page=" . $page_name);
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM posts WHERE id = $id");
    header("Location: admin.php?page=" . $current_page);
    exit();
}

$edit_post = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM posts WHERE id=$id");
    $edit_post = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NGA Admin Dashboard</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { margin: 0; padding: 0; background: #f4f7fa; display: flex; min-height: 100vh; }
        
        .sidebar { width: 250px; background: #1e2b3c; color: white; padding: 20px 0; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; color: #4da3ff; margin-bottom: 30px; font-size: 1.2rem; }
        .sidebar a { display: block; color: #a0b2c6; padding: 15px 25px; text-decoration: none; font-weight: 500; transition: 0.3s; }
        .sidebar a:hover { background: #2c3e50; color: white; border-left: 4px solid #4da3ff; }
        .sidebar a.active { background: #2a3f54; color: white; border-left: 4px solid #27AE60; }
        
        .badge { background: #e74c3c; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem; margin-left: 5px; font-weight: bold; }

        .main-content { flex: 1; padding: 40px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #ddd; padding-bottom: 15px; }
        .top-bar h1 { margin: 0; color: #333; text-transform: capitalize; }
        .logout-btn { background: #e74c3c; color: white; text-decoration: none; padding: 8px 15px; border-radius: 5px; }
        
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .full-width { grid-column: span 2; }
        
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 1rem; }
        label { font-weight: bold; color: #555; display: block; margin-bottom: 5px; margin-top: 10px;}
        
        button.save-btn { background: #27AE60; color: white; border: none; padding: 12px 20px; font-size: 1rem; border-radius: 5px; cursor: pointer; margin-top: 20px;}
        button.save-btn:hover { background: #219150; }
        
        .action-links a { margin-right: 15px; text-decoration: none; font-weight: bold; }
        .edit-text { color: #f39c12; }
        .delete-text { color: #e74c3c; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <h2>NGA CONTROL PANEL</h2>
        
        <a href="admin.php?page=inbox" class="<?= $current_page == 'inbox' ? 'active' : '' ?>" style="border-bottom: 1px solid #2c3e50; margin-bottom: 10px;">
            📥 Inbox 
            <?php if($unread_count > 0) echo "<span class='badge'>$unread_count</span>"; ?>
        </a>

        <a href="admin.php?page=index" class="<?= $current_page == 'index' ? 'active' : '' ?>">Home Page</a>
        <a href="admin.php?page=about" class="<?= $current_page == 'about' ? 'active' : '' ?>">About Page</a>
        <a href="admin.php?page=news" class="<?= $current_page == 'news' ? 'active' : '' ?>">News Page</a>
        <a href="admin.php?page=academics" class="<?= $current_page == 'academics' ? 'active' : '' ?>">Academics Page</a>
        <a href="admin.php?page=courses" class="<?= $current_page == 'courses' ? 'active' : '' ?>">Courses Page</a>
        <a href="admin.php?page=contact" class="<?= $current_page == 'contact' ? 'active' : '' ?>">Contact Page</a>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <h1><?php echo $current_page == 'inbox' ? 'Message Inbox' : 'Editing: ' . $current_page . ' Page'; ?></h1>
            <div>
                <span style="margin-right: 20px; font-weight: bold; color: #555;">Welcome back, Levi</span>
                <a href="admin.php?action=logout" class="logout-btn">Logout</a>
            </div>
        </div>

        <?php 
        // ==========================================
        // 4. DISPLAY THE INBOX (If Inbox is clicked)
        // ==========================================
        if ($current_page == 'inbox'): 
        ?>
            <h2>📬 Recent Form Submissions</h2>
            
            <?php 
            // Fetch messages, newest first
            $msg_result = mysqli_query($conn, "SELECT * FROM messages ORDER BY created_at DESC");
            
            if (mysqli_num_rows($msg_result) == 0) {
                echo "<p style='color: grey;'>Your inbox is currently empty.</p>";
            }

            while($msg = mysqli_fetch_assoc($msg_result)): 
                // Change background color if the message is unread
                $bg_color = $msg['is_read'] ? '#ffffff' : '#e8f4f8';
                $border_color = $msg['is_read'] ? '#ccc' : '#2F80ED';
            ?>
                <div class="card" style="background: <?php echo $bg_color; ?>; border-left: 5px solid <?php echo $border_color; ?>;">
                    <div style="display: flex; justify-content: space-between;">
                        <h3 style="margin-top: 0;"><?php echo htmlspecialchars($msg['subject']); ?></h3>
                        <small style="color: grey;"><?php echo date('F j, Y, g:i a', strtotime($msg['created_at'])); ?></small>
                    </div>
                    
                    <p style="margin: 5px 0;"><b>From:</b> <?php echo htmlspecialchars($msg['sender_name']); ?> 
                       (<a href="mailto:<?php echo htmlspecialchars($msg['sender_email']); ?>"><?php echo htmlspecialchars($msg['sender_email']); ?></a>)</p>
                    
                    <div style="background: #fdfdfd; padding: 15px; border: 1px solid #eee; border-radius: 5px; margin: 15px 0;">
                        <?php echo nl2br(htmlspecialchars($msg['message_text'])); ?>
                    </div>

                    <div class="action-links">
                        <?php if(!$msg['is_read']): ?>
                            <a href="admin.php?mark_read=<?php echo $msg['id']; ?>" style="color: #27AE60;">✔️ Mark as Read</a>
                        <?php endif; ?>
                        <a href="mailto:<?php echo htmlspecialchars($msg['sender_email']); ?>" style="color: #2F80ED;">✉️ Reply via Email</a>
                        <a href="admin.php?delete_msg=<?php echo $msg['id']; ?>" class="delete-text" onclick="return confirm('Permanently delete this message?');">🗑️ Delete</a>
                    </div>
                </div>
            <?php endwhile; ?>

        <?php 
        // ==========================================
        // 5. DISPLAY THE PAGE EDITOR (For all other pages)
        // ==========================================
        else: 
        ?>
            
            <div class="card" style="border-top: 4px solid #2F80ED;">
                <h2><?php echo $edit_post ? "Update Content" : "Add New Content to " . ucfirst($current_page); ?></h2>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="page_name" value="<?php echo htmlspecialchars($current_page); ?>">
                    <?php if($edit_post): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_post['id']; ?>">
                        <input type="hidden" name="existing_image" value="<?php echo $edit_post['image_path']; ?>">
                    <?php endif; ?>

                    <div class="full-width">
                        <label>Post Title *</label>
                        <input type="text" name="title" required value="<?php echo $edit_post ? htmlspecialchars($edit_post['title']) : ''; ?>">
                    </div>

                    <div class="full-width">
                        <label>Main Text Content *</label>
                        <textarea name="content" rows="6" required><?php echo $edit_post ? htmlspecialchars($edit_post['content']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-grid">
                        <div>
                            <label>Upload a Picture (Optional)</label>
                            <input type="file" name="image" accept="image/*">
                            <?php if($edit_post && !empty($edit_post['image_path'])): ?>
                                <small style="color: green;">Current Image saved.</small>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label>YouTube Video Link (Optional)</label>
                            <input type="text" name="video_url" placeholder="https://youtube.com/..." value="<?php echo $edit_post ? htmlspecialchars($edit_post['video_url']) : ''; ?>">
                        </div>
                        <div>
                            <label>Button Link (Optional)</label>
                            <input type="text" name="link_url" placeholder="https://google.com" value="<?php echo $edit_post ? htmlspecialchars($edit_post['link_url']) : ''; ?>">
                        </div>
                        <div>
                            <label>Google Maps Iframe/Link (Optional)</label>
                            <input type="text" name="location_map" placeholder="Paste map embed code here..." value="<?php echo $edit_post ? htmlspecialchars($edit_post['location_map']) : ''; ?>">
                        </div>
                    </div>

                    <button type="submit" name="save_post" class="save-btn"><?php echo $edit_post ? "Save Changes" : "Publish to Page"; ?></button>
                    <?php if($edit_post): ?>
                        <a href="admin.php?page=<?php echo $current_page; ?>" style="margin-left: 15px; color: #555;">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <h2>Content currently on the <?php echo ucfirst($current_page); ?> page:</h2>
            
            <?php 
            $sql = "SELECT * FROM posts WHERE page_name = '$current_page' ORDER BY created_at DESC";
            $result = mysqli_query($conn, $sql);
            
            if (mysqli_num_rows($result) == 0) {
                echo "<p style='color: grey;'>No content here yet. Add something above!</p>";
            }

            while($post = mysqli_fetch_assoc($result)): 
            ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                    
                    <div style="font-size: 0.85rem; background: #eee; padding: 10px; border-radius: 5px; margin: 10px 0;">
                        <b>Media Attached:</b> 
                        <?php echo !empty($post['image_path']) ? '🖼️ Image ' : ''; ?>
                        <?php echo !empty($post['video_url']) ? '🎥 Video ' : ''; ?>
                        <?php echo !empty($post['link_url']) ? '🔗 Link ' : ''; ?>
                        <?php echo !empty($post['location_map']) ? '📍 Map ' : ''; ?>
                        <?php if(empty($post['image_path']) && empty($post['video_url']) && empty($post['link_url']) && empty($post['location_map'])) echo 'None (Text Only)'; ?>
                    </div>

                    <div class="action-links">
                        <a href="admin.php?page=<?php echo $current_page; ?>&edit=<?php echo $post['id']; ?>" class="edit-text">✏️ Edit</a>
                        <a href="admin.php?page=<?php echo $current_page; ?>&delete=<?php echo $post['id']; ?>" class="delete-text" onclick="return confirm('Delete this completely?');">🗑️ Delete</a>
                    </div>
                </div>
            <?php endwhile; ?>
            
        <?php endif; ?> </main>

</body>
</html>