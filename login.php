<?php
session_start();
if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error = isset($_GET['error']) ? true : false;
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login — Tracker Tugas</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
    <style>
        /* Hilangkan background ungu, pakai background dari style.css */
        body {
            background: var(--bg-body) !important;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        /* Hapus efek gradien/background aneh */
        body::before, body::after {
            display: none !important;
        }
        
        .auth-container {
            max-width: 420px;
            width: 100%;
            margin: 0 auto;
        }
        
        .auth-card {
            background: var(--bg-container);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 40px 36px;
            box-shadow: var(--shadow-lg);
        }
        
        .auth-logo {
            text-align: center;
            margin-bottom: 28px;
        }
        
        .auth-logo span {
            font-size: 52px;
        }
        
        .auth-title {
            font-size: 28px;
            font-weight: 800;
            text-align: center;
            margin-bottom: 8px;
            color: var(--text-main);
        }
        
        .auth-sub {
            text-align: center;
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 32px;
        }
        
        .auth-field {
            margin-bottom: 20px;
        }
        
        .auth-field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-sub);
        }
        
        .auth-field input {
            width: 100%;
            padding: 12px 16px;
            font-family: inherit;
            font-size: 15px;
            background: var(--bg-surface);
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            color: var(--text-main);
            transition: all 0.2s;
        }
        
        .auth-field input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(67,97,238,0.1);
        }
        
        .auth-btn {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            font-weight: 700;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 8px;
        }
        
        .auth-btn:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: var(--text-muted);
        }
        
        .auth-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .error-alert {
            background: var(--danger-light);
            border-left: 4px solid var(--danger);
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            margin-bottom: 24px;
            color: var(--danger);
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .error-alert::before {
            content: "⚠️";
        }
    </style>
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <span>📚</span>
        </div>
        <div class="auth-title">Masuk</div>
        <div class="auth-sub">Kelola tugas kuliahmu dengan mudah</div>

        <?php if ($error): ?>
            <div class="error-alert">
                Username atau password salah. Coba lagi!
            </div>
        <?php endif; ?>

        <form action="proses_login.php" method="POST">
            <div class="auth-field">
                <label>Username</label>
                <input type="text" name="username" placeholder="Username kamu" required autocomplete="username">
            </div>
            <div class="auth-field">
                <label>Password</label>
                <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
            </div>
            <button type="submit" name="login" class="auth-btn">Masuk →</button>
        </form>

        <div class="auth-footer">
            Belum punya akun? <a href="register.php">Daftar sekarang</a>
        </div>
    </div>
</div>

<script>
    (function() {
        const theme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', theme);
    })();
</script>
</body>
</html>