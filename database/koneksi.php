<?php

require_once __DIR__ . '/../vendor/autoload.php'; // pastikan path ke autoload.php benar

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Ambil dari .env
$host = $_ENV['DB_HOST'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];
$name = $_ENV['DB_NAME'];

// Koneksi ke database
$conn = mysqli_connect($host, $user, $pass, $name);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
