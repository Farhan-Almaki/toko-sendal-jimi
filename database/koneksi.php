<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Hanya load .env jika file ada
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$name = getenv('DB_NAME') ?: 'sendal_jimi';

// ❗❗ GUNAKAN OOP MYSQLI (yang pakai `new`)
$conn = new mysqli($host, $user, $pass, $name);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
