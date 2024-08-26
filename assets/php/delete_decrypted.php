<?php
// assets/php/delete_decrypted.php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$movie_id = $_REQUEST['movie_id'];

// fetch user and movie
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ? LIMIT 1");
$stmt->execute([$movie_id]);
$movie = $stmt->fetch();

// verify that the movie exists for this user
if ($user && $movie) {
    // locate the decrypted movie
    $decrypted_filename = '../movies/' . $user['username'] . '/' . $movie['title'] . '_decrypted.mp4';
    // verify that the movie exists
    if (file_exists($decrypted_filename)) {
        echo"delete";
        unlink($decrypted_filename);
    }

    // Update decryption status in the database
    $stmt = $pdo->prepare("REPLACE INTO decryption_status (user_id, movie_id, is_decrypted) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $movie_id, 0]);

    echo json_encode(['status' => 'success', 'message' => 'Decrypted movie deleted successfully']);
} else {
    print_r($_REQUEST);
    echo json_encode(['status' => 'error', 'message' => 'Invalid user or movie']);
}
?>
