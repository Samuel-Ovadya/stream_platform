<?php 
// index.php
require_once 'assets/php/db.php'; // Load database object
session_start(); 

// checked if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); //if not redirect
    exit;
}

$user_id = $_SESSION['user_id'];

//load movies
$stmt = $pdo->query("SELECT * FROM movies");
$movies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streaming Platform</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <!-- navbar  -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Streaming Platform</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <button id="togglePurchased" class="btn btn-primary">Show Purchased Movies</button>
                </li>
                <li class="nav-item">
                    <a href="assets/php/logout.php" class="btn btn-danger ml-2">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <!-- movies table -->
    <div class="container mt-4">
        <div class="row" id="moviesList">
            <?php 
            /* create a card for each movie */
            foreach ($movies as $movie) : 
            ?>
                <!-- data-purchased  will allow filtering -->
                <div class="col-md-4 movie-card" data-purchased="<?php
                    // fetch only purchased movies
                    $stmt = $pdo->prepare("SELECT * FROM purchases WHERE user_id = ? AND movie_id = ?");
                    $stmt->execute([$user_id, $movie['id']]);
                    $purchase = $stmt->fetch();
                    echo $purchase ? 'true' : 'false';
                ?>">
                    <div class="card">
                        <img src="assets/images/<?php echo $movie['image']; ?>" class="card-img-top" alt="Movie Image">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($movie['description']); ?></p>
                            <p class="card-text">Price: $<?php echo htmlspecialchars($movie['price']); ?></p>
                            <?php
                            if ($purchase) {
                                echo "<a href='watch_movie.php?movie_id=" . htmlspecialchars($movie['id']) . "' class='btn btn-watch'>Watch <i class='fas fa-play'></i></a>";
                            } else {
                                echo "<form action='assets/php/purchase.php' method='post'>";
                                echo "<input type='hidden' name='movie_id' value='" . htmlspecialchars($movie['id']) . "'>";
                                echo "<button type='submit' class='btn btn-purchase'>Buy <i class='fas fa-shopping-cart'></i></button>";
                                echo "</form>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- end of table -->
    </div>
    
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        const movieId = <?= $movie['id'] ?>; 
        
        //will delete decrypted movies when user enters this page 
        async function deleteMovie() {
            try {
                // uses the deleted_decrypted as an endpoint to load the PHP script
                const response = await fetch('assets/php/delete_decrypted.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        'movie_id': movieId
                    })
                });

                // check if the decrypted movies were successfully deleted
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

        deleteMovie();

        const toggleButton = document.getElementById('togglePurchased');
        const moviesList = document.getElementById('moviesList');
        let showPurchased = false;

        // when the purchased filter is enabled
        toggleButton.addEventListener('click', function() {
            showPurchased = !showPurchased;
            toggleButton.textContent = showPurchased ? 'Show All Movies' : 'Show Purchased Movies';

            Array.from(moviesList.getElementsByClassName('movie-card')).forEach(card => {
                if (showPurchased) {
                    // use the data-purchased to display
                    if (card.getAttribute('data-purchased') === 'true') {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                } else {
                    card.style.display = 'block';
                }
            });
        });
    });
    </script>
</body>
</html>
