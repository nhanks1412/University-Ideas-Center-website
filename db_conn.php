<?php

$host = 'localhost';
$dbname = 'UniversityIdeaDB';
$username = 'root';
$password = '123456';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection error: " . $e->getMessage();
    exit();
}
?>