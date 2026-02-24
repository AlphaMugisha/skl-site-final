<?php 
// 1. Connect to the database FIRST
require 'db.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - New Generation Academy</title>
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>

<nav>
    <div class="logo">New Generation Academy</div>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="news.php">News</a></li>
        <li><a href="academics.php">Academics</a></li>
        <li><a href="courses.php">Courses</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="admin.php" style="color: var(--primary); font-weight: bold;">Admin Login</a></li>
    </ul>
</nav>

<div class="container">
    <h1 style="color: var(--primary);">Welcome to New Generation Academy</h1>
    <p>Empowering the future through technology and innovation.</p>
    <hr style="border: 0; border-top: 2px solid #ddd; margin: 20px 0;">

    <?php 
    // 2. Ask MySQL ONLY for posts where the page name is 'index'
    $sql = "SELECT * FROM posts WHERE page_name = 'index' ORDER BY created_at DESC";
    $result = mysqli_query($conn, $sql);

    // 3. Check if we found any posts
    if (mysqli_num_rows($result) > 0) {
        
        // Loop through every post we found and draw a card
        while ($post = mysqli_fetch_assoc($result)) {
            echo "<div class='card'>";
            
            // --- SHOW IMAGE ---
            if (!empty($post['image_path'])) {
                echo "<img src='" . htmlspecialchars($post['image_path']) . "' style='width:100%; max-height:400px; object-fit:cover; border-radius:8px; margin-bottom:15px;'>";
            }
            
            // --- SHOW TITLE & TEXT ---
            echo "<h2>" . htmlspecialchars($post['title']) . "</h2>";
            echo "<p>" . nl2br(htmlspecialchars($post['content'])) . "</p>";
            
            // --- SHOW LINK BUTTON ---
            if (!empty($post['link_url'])) {
                echo "<a href='" . htmlspecialchars($post['link_url']) . "' target='_blank' style='display: inline-block; background: var(--secondary); color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-top: 10px; font-weight: bold;'>Read More</a><br><br>";
            }

            // --- SHOW YOUTUBE VIDEO ---
            if (!empty($post['video_url'])) {
                // Change a standard YouTube link into a playable embedded link
                $embed_url = str_replace("watch?v=", "embed/", $post['video_url']);
                echo "<iframe width='100%' height='315' src='" . htmlspecialchars($embed_url) . "' frameborder='0' allowfullscreen style='border-radius: 8px; margin-top: 15px;'></iframe>";
            }

            // --- SHOW GOOGLE MAP ---
            if (!empty($post['location_map'])) {
                echo "<div style='margin-top: 15px; border-radius: 8px; overflow: hidden;'>" . $post['location_map'] . "</div>";
            }

            echo "</div>"; // Close the card
        }
    } else {
        // If the database has no posts for 'index', show this message
        echo "<p style='color: grey; text-align: center; padding: 40px; background: white; border-radius: 8px;'>No content has been added to the Home page yet. Log into the Admin panel to publish your first post!</p>";
    }
    ?>
</div>

</body>
</html>