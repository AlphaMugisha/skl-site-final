<?php
require 'db.php';

$username = 'admin';
$password = 'nga2026';

// This is the magic function that scrambles the password securely!
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO admins (username, password) VALUES ('$username', '$hashed_password')";

if (mysqli_query($conn, $sql)) {
    echo "<h1>Admin account securely created!</h1>";
    echo "<p>Your password was scrambled into this text: <b>$hashed_password</b></p>";
    echo "<p style='color:red;'>SECURITY RULE: Please delete this setup_admin.php file now.</p>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>