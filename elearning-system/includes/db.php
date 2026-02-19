<?php
$host = 'localhost';
$db = 'elearning';
$user = 'root'; // Update with your MySQL username
$pass = ''; // Update with your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    $pdo = null; // Set pdo to null so pages can check for it
}