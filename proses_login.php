<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $row['password'])) {
            // DATA YANG DIBUTUHKAN INDEX.PHP HARUS DISIMPAN DI SINI
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $row['id']; // Ini yang bikin error line 10
            $_SESSION['nama'] = $row['nama'];   // Ini yang bikin error line 11 (pastikan kolom di DB namanya 'nama')
            
            header("Location: index.php");
            exit;
        }
    }
    echo "<script>alert('Username atau Password salah!'); window.location='login.php';</script>";
}
?>