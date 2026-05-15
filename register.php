<?php 
session_start();
include 'koneksi.php';

if (isset($_POST['register'])) {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $cek = mysqli_query($conn, "SELECT username FROM users WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Username sudah ada!');</script>";
    } else {
        $query = "INSERT INTO users (nama, username, email, password) VALUES ('$nama', '$username', '$email', '$password')";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Berhasil Daftar!'); window.location='login.php';</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Register - Tracker Tugas</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Pakai CSS yang sama dengan login.php tadi */
        body { margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(20px); border-radius: 24px; padding: 40px; width: 100%; max-width: 450px; border: 1px solid rgba(255, 255, 255, 0.2); box-shadow: 0 25px 50px rgba(0,0,0,0.3); text-align: center; }
        h2 { color: #fff; margin-bottom: 25px; font-weight: 800; font-size: 28px; }
        .field { text-align: left; margin-bottom: 15px; }
        label { display: block; color: rgba(255,255,255,0.8); font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; }
        input { width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); border-radius: 10px; color: #fff; font-size: 14px; outline: none; box-sizing: border-box; }
        input:focus { border-color: #6c63ff; background: rgba(255,255,255,0.1); }
        button { width: 100%; padding: 14px; border: none; border-radius: 10px; background: linear-gradient(135deg, #6c63ff, #a855f7); color: #fff; font-weight: 700; font-size: 16px; cursor: pointer; margin-top: 10px; }
        .footer-text { margin-top: 20px; color: rgba(255,255,255,0.6); font-size: 14px; }
        .footer-text a { color: #fff; font-weight: 700; text-decoration: none; }
    </style>
    
</head>
<body>
    <div class="auth-card">
        <h2>Daftar Akun</h2>
        <form method="POST">
            <div class="field">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" required placeholder="Nama lengkap">
            </div>
            <div class="field">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Username unik">
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" required placeholder="Email kamu">
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Min. 8 karakter">
            </div>
            <button type="submit" name="register">Buat Akun Sekarang</button>
        </form>
        <div class="footer-text">
            Sudah punya akun? <a href="login.php">Login</a>
        </div>
    </div>
</body>
</html>