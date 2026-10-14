<?php
// ==========================================
// KONEKSI DATABASE MYSQL
// ==========================================
$host = "localhost";
$user = "root";       // Sesuaikan username database Anda
$pass = "";           // Sesuaikan password database Anda
$db   = "db_laporan_fasilitas"; // Sesuaikan nama database Anda

$conn = new mysqli($host, $user, $pass, $db);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>