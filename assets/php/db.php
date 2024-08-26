<?php
// db.php
// create DB PDO Object


// get data from environment variables
$host = getenv('HOST');
$db = getenv("DB"); # streaming_platform
$user = getenv("USERNAME");
$pass = getenv('PASSWORD'); 

$dsn = "mysql:host=$host;dbname=$db;charset=utf8";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>
