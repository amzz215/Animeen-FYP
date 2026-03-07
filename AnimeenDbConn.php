<?php
// AnimeenDbConn.php

$host = "127.0.0.1";   
$dbname = "animeen_db";
$username = "root";
$password = "";        

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $ex) {
    http_response_code(500);
    exit("Failed to connect to the database.<br>" . htmlspecialchars($ex->getMessage()));
}