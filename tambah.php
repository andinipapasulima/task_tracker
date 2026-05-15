<?php
session_start(); // Wajib ada untuk mengambil data user yang login
include 'koneksi.php';

// Cek login dulu agar id_user tersedia
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Ambil ID User dari session
$id_user = $_SESSION['id_user'];

if (isset($_POST['simpan'])) {
    // Gunakan mysqli_real_escape_string agar lebih aman dari karakter aneh
    $matkul   = mysqli_real_escape_string($conn, $_POST['matkul']);
    $tugas    = mysqli_real_escape_string($conn, $_POST['tugas']);
    $deadline = $_POST['deadline'];

    // PERBAIKAN: Masukkan user_id ke dalam query
    $query = "INSERT INTO tugas (user_id, matkul, tugas, deadline, status) 
              VALUES ('$id_user', '$matkul', '$tugas', '$deadline', 'Belum')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit;
    } else {
        echo "Gagal menambah tugas: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tambah Tugas Baru</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn-kembali"><span>←</span> Kembali ke Daftar</a>
    <h2>Tambah Tugas Baru</h2>

    <form method="POST" action="">
        <label>Mata Kuliah</label>
        <input type="text" name="matkul" placeholder="Contoh: Pemrograman Web" required>

        <label>Detail Tugas</label>
        <textarea name="tugas" rows="4" placeholder="Apa yang harus dikerjakan?" required></textarea>

        <label>Deadline</label>
        <input type="date" name="deadline" required>

        <button type="submit" name="simpan">Simpan ke Daftar</button>
    </form>
</div>

</body>
</html>