<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $row['id'];
            $_SESSION['nama'] = $row['nama']; // Sesuaikan nama kolom di DB (nama / nama_lengkap)
            header("Location: index.php");
            exit;
        }
    }
    $error = true;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Tracker Tugas</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            height: 100vh; display: flex; align-items: center; justify-content: center;
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            width: 100%; max-width: 400px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            text-align: center;
        }
        h2 { color: #fff; margin-bottom: 30px; font-weight: 800; font-size: 28px; }
        .field { text-align: left; margin-bottom: 20px; }
        label { display: block; color: rgba(255,255,255,0.8); font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; }
        input {
            width: 100%; padding: 14px; background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2); border-radius: 12px;
            color: #fff; font-size: 15px; outline: none; transition: 0.3s;
            box-sizing: border-box;
        }
        input:focus { background: rgba(255,255,255,0.2); border-color: #6c63ff; }
        button {
            width: 100%; padding: 16px; border: none; border-radius: 12px;
            background: linear-gradient(135deg, #6c63ff, #a855f7);
            color: #fff; font-weight: 700; font-size: 16px; cursor: pointer;
            transition: 0.3s; box-shadow: 0 10px 20px rgba(108,99,255,0.3);
        }
        button:hover { transform: translateY(-2px); box-shadow: 0 15px 25px rgba(108,99,255,0.4); }
        .footer-text { margin-top: 25px; color: rgba(255,255,255,0.6); font-size: 14px; }
        .footer-text a { color: #fff; font-weight: 700; text-decoration: none; }
        .error-msg { background: rgba(255, 80, 80, 0.2); color: #ff8080; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid rgba(255,80,80,0.3); }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2>Login</h2>
        <?php if(isset($error)) : ?>
            <div class="error-msg">Username atau Password salah!</div>
        <?php endif; ?>
        <form method="POST">
            <div class="field">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Username kamu">
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" name="login">Masuk Sekarang →</button>
        </form>
        <div class="footer-text">
            Belum punya akun? <a href="register.php">Daftar Akun</a>
        </div>
    </div>
</body>
</html>