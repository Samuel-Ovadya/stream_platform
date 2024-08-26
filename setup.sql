CREATE DATABASE streaming_platform;

USE streaming_platform;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    credit DECIMAL(10, 2) DEFAULT 1000.00
);

CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    video_url VARCHAR(255) NOT NULL
);

CREATE TABLE purchases (
    user_id INT,
    movie_id INT,
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, movie_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (movie_id) REFERENCES movies(id)
);


INSERT INTO movies (title, description, price, image, video_url) 
VALUES ('Movie 1', 'Description of Movie 1', 9.99, 'movie1.jpg', 'movie1.mp4');

INSERT INTO movies (title, description, price, image, video_url) 
VALUES ('Movie 2', 'Description of Movie 2', 9.99, 'movie2.jpg', 'movie2.mp4');


CREATE TABLE decryption_status (
    user_id INT,
    movie_id INT,
    is_decrypted BOOLEAN,
    PRIMARY KEY (user_id, movie_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (movie_id) REFERENCES movies(id)
);
