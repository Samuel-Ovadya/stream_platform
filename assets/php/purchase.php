<?php
// purchase.php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$movie_id = $_POST['movie_id'];

// fetch user and movie 
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ? LIMIT 1");
$stmt->execute([$movie_id]);
$movie = $stmt->fetch();


if ($user && $movie) {
    // verify that user has enough credits to complete purchase
    if ($user['credit'] >= $movie['price']) {
        $new_credit = $user['credit'] - $movie['price'];

        // Update user's credit
        $stmt = $pdo->prepare("UPDATE users SET credit = ? WHERE id = ?");
        $stmt->execute([$new_credit, $user_id]);

        // Record purchase
        $stmt = $pdo->prepare("INSERT INTO purchases (user_id, movie_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $movie_id]);

        // Encrypt and save movie to user's directory
        $encrypted_filename = '../movies/' . $user['username'] . '/' . $movie['title'] . '_encrypted.mp4';
        $original_video = '../videos/' . $movie['video_url']; // Adjust as per your setup
        $password = $user['password'].$user['id']; // Replace with actual secure password

        // Create directory if it doesn't exist
        $user_directory = '../movies/' . $user['username'];
        if (!file_exists($user_directory)) {
            mkdir($user_directory, 0777, true);
        }

        // Encrypt and save the file
        $handle_read = fopen($original_video, 'rb');
        if (!$handle_read) {
            die('Error: Cannot open input file');
        }

        $handle_write = fopen($encrypted_filename, 'wb');
        if (!$handle_write) {
            fclose($handle_read);
            die('Error: Cannot create output file');
        }


        // Read and encrypt the file chunk by chunk
        while (!feof($handle_read)) {
            $chunk = fread($handle_read, 1024); // Adjust chunk size as needed
            $encrypted_chunk = encrypt($chunk, $password);
            fwrite($handle_write, $encrypted_chunk);
        }

        // Close file handles
        fclose($handle_read);
        fclose($handle_write);

        // Redirect back to index or wherever appropriate
        header("Location: ../../index.php");
        exit;
    } else {
        echo "Insufficient credit.";
    }
} else {
    echo "Invalid purchase.";
}
?>
