<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Hanya load .env jika file ada (untuk lokal development)
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

// Ambil environment variables
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$name = getenv('DB_NAME') ?: 'sendal_jimi';

// Koneksi ke database
$conn = mysqli_connect($host, $user, $pass, $name);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
