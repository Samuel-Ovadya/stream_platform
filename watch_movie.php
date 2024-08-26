<?php
// watch_movie.php

// loads and decrypt the movie, as soon as the is left, the decrypted version is deleted

require_once 'assets/php/db.php'; // load object 
require_once 'assets/php/encryption.php'; // load encryption functions
session_start(); # load session

// if user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// get user and movie to display
$user_id = $_SESSION['user_id'];
$movie_id = $_GET['movie_id'];

// fetch user and movie data from the database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?"); // for limited number of movies add LIMIT number_of_movies
$stmt->execute([$movie_id]);
$movie = $stmt->fetch();

// if user owns the movie, define paths to encrypted and decrypted files, reconstitute the password
if ($user && $movie) {
    $encrypted_filename = 'assets/movies/' . $user['username'] . '/' . $movie['title'] . '_encrypted.mp4';
    $decrypted_filename = 'assets/movies/' . $user['username'] . '/' . $movie['title'] . '_decrypted.mp4';
    $password = $user['password'].$user['id']; // Replace with actual secure password


    // the presence of the decrypted file means the movie has been bought
    // Check if the encrypted file exists, if not, notify user and redirect to home page 
    if (!file_exists($encrypted_filename)) {
        echo '<h1>Need to buy this movie.</h1>';
        echo '<script>
                setTimeout(function() {
                    window.location.href = "../../index.html"; 
                }, 3000);
            </script>';
        exit;
    }

// read encrypted file, decrypt and write in decrypted file
    $handle_read = fopen($encrypted_filename, 'rb');
    $handle_write = fopen($decrypted_filename, 'wb');

    while (!feof($handle_read)) {
        $encrypted_chunk = fread($handle_read, 1024); // read, Adjust chunk size as needed
        $decrypted_chunk = decrypt($encrypted_chunk, $password); // decrypt
        fwrite($handle_write, $decrypted_chunk); // write
    }

    fclose($handle_read);
    fclose($handle_write);

    // Update decryption status in the database
    $stmt = $pdo->prepare("REPLACE INTO decryption_status (user_id, movie_id, is_decrypted) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $movie_id,1]);

    // Serve the HTML5 video player
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Watch Movie</title>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    </head>
    <body>
        <div class="container">
            <h1><?php echo htmlspecialchars($movie['title']); ?></h1>
            <video id="moviePlayer" width="640" height="360" controls >
                <source src="<?php echo htmlspecialchars($decrypted_filename); ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        <script>
            /*function showElementAfterDelay(elementId) {
            // Wait for 2 seconds (2000 milliseconds)
            setTimeout(function() {
                // Change the display property to block
                var element = document.getElementById(elementId);
                if (element) {
                element.style.display = 'block';
                }
            }, 2000);
            }
            showElementAfterDelay('moviePlayer');*/


            // Handle re-encryption when the page is left

            async function deleteMovie() {
                try {
                    const response = await fetch('assets/php/delete_decrypted.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            'movie_id': <?= $movie_id ?>
                        })
                    });

                    if (response.ok) {
                        const result = await response.text();
                        console.log('Movie deleted successfully:', result);
                    } else {
                        console.error('Error deleting movie:', response.statusText);
                    }
                } catch (error) {
                    console.error('Network error:', error);
                }
            }

            // when leaving the page, a confirmation dialog is shown, then the decrypted movie will be deleted
            window.addEventListener('beforeunload', function (event) {
                    window.location="assets/php/delete_decrypted.php?movie_id=<?= $movie_id ?>";
                    var message = "Are you sure you want to leave? You may have unsaved changes.";

                    deleteMovie();

                    // Set the returnValue property and return the message
                    event.returnValue = message; 
                    return message; 
                });

            $(document).ready(function() {

                /*var video = document.getElementById('moviePlayer');
                video.onended = function() {
                    $.ajax({
                        type: "POST",
                        url: "assets/php/reencrypt.php",
                        data: { movie_id: <?php echo $movie_id; ?> },
                        success: function(response) {
                            console.log(response);
                            alert('The movie has ended and will be re-encrypted.');
                            window.location.href = "index.php"; // Redirect to another page
                        }
                    });
                };*/

                

            });
        </script>
    </body>
    </html>
    <?php
} else {
    echo "Movie not found.";
}
?>
