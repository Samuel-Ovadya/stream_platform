<?php
// logout.php
session_start();
require_once 'db.php';

// Get the username from the session
$username = $_SESSION['username'];

// Destroy the session
session_unset();
session_destroy();

// Delete decrypted movies
$decrypted_movie_dir = '../movies/' . $username . '/';
$files = glob($decrypted_movie_dir . '*_decrypted.mp4'); // Adjust pattern if necessary

foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}

// Redirect to login page
header("Location: ../../login.php");
exit;
?>
