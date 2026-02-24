<?php 
include 'header.php'; 
require 'db.php'; // Connect to database
?>

<div class="container">
    <h1 style="color: var(--primary);">NGA Course Catalog</h1>
    <p>Explore our programs in software engineering, 3D modeling, and hardware robotics.</p>
    <hr style="border: 0; border-top: 2px solid #ddd; margin: 20px 0;">

    <?php 
    // Ask MySQL ONLY for posts where page_name is 'courses'
    $sql = "SELECT * FROM posts WHERE page_name = 'courses' ORDER BY created_at DESC";
    $result = mysqli_query($conn, $sql);

    // Check if we found any courses
    if (mysqli_num_rows($result) > 0) {
        
        while ($post = mysqli_fetch_assoc($result)) {
            echo "<div class='card'>";
            
            // 1. SHOW IMAGE (If uploaded)
            if (!empty($post['image_path'])) {
                echo "<img src='" . htmlspecialchars($post['image_path']) . "' style='width:100%; max-height:300px; object-fit:cover; border-radius:8px; margin-bottom:15px;'>";
            }
            
            // 2. SHOW TITLE AND TEXT
            echo "<h2>" . htmlspecialchars($post['title']) . "</h2>";
            echo "<p>" . nl2br(htmlspecialchars($post['content'])) . "</p>";
            
            // 3. SHOW BUTTON LINK (If admin added a link)
            if (!empty($post['link_url'])) {
                echo "<a href='" . htmlspecialchars($post['link_url']) . "' target='_blank' style='display: inline-block; background: var(--secondary); color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-top: 10px; font-weight: bold;'>Enroll / Learn More</a><br><br>";
            }

            // 4. SHOW YOUTUBE VIDEO (If admin added a video link)
            if (!empty($post['video_url'])) {
                // We convert a standard YouTube link into an "embed" link so it plays right on your page
                $embed_url = str_replace("watch?v=", "embed/", $post['video_url']);
                echo "<iframe width='100%' height='315' src='" . htmlspecialchars($embed_url) . "' frameborder='0' allowfullscreen style='border-radius: 8px; margin-top: 15px;'></iframe>";
            }

            // 5. SHOW MAP (If admin pasted an iframe map)
            if (!empty($post['location_map'])) {
                echo "<div style='margin-top: 15px; border-radius: 8px; overflow: hidden;'>" . $post['location_map'] . "</div>";
            }

            echo "</div>"; // Close the card
        }
    } else {
        echo "<p style='color: grey; text-align: center;'>No courses have been added yet. Please check back later!</p>";
    }
    ?>
</div>

</body>
</html>