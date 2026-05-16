<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id = (int)$_GET['id'];
$id_user = $_SESSION['id_user'];

// Hanya hapus jika tugas milik user ini
$query = "DELETE FROM tugas WHERE id = $id AND user_id = $id_user";
mysqli_query($conn, $query);

header("Location: index.php");
?>