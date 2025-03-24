<?php
// includes/config.php

$host = 'localhost';
$dbname = 'attendance_db';
$username = 'root';
$password = 'Agtsj123##';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("MySQL Connection failed: " . $e->getMessage());
}

try {
    require __DIR__ . '/../vendor/autoload.php';
} catch (Exception $e) {
    die("Composer autoload error: " . $e->getMessage());
}