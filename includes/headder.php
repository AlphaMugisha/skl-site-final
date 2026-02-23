<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NGA Coding Academy | Building Future Innovators</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo">NGA <span>CODING</span></a>
        <nav>
            <?php $current = basename($_SERVER['PHP_SELF']); ?>
            <ul>
                <li><a href="index.php" class="<?= ($current == 'index.php') ? 'active' : '' ?>">Home</a></li>
                <li><a href="about.php" class="<?= ($current == 'about.php') ? 'active' : '' ?>">About Us</a></li>
                <li><a href="academics.php" class="<?= ($current == 'academics.php') ? 'active' : '' ?>">Academics</a></li>
                <li><a href="news.php" class="<?= ($current == 'news.php') ? 'active' : '' ?>">News</a></li>
                <li><a href="contact.php" class="<?= ($current == 'contact.php') ? 'active' : '' ?>">Contact</a></li>
            </ul>
        </nav>
    </header>