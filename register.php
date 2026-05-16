<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error_msg   = '';
$success_msg = '';

if (isset($_POST['register'])) {
    $nama     = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    if (strlen($password) < 8) {
        $error_msg = 'Password minimal 8 karakter.';
    } else {
        $cek = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
        if (mysqli_num_rows($cek) > 0) {
            $error_msg = 'Username sudah terdaftar, coba yang lain.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $q = "INSERT INTO users (nama, username, email, password) VALUES ('$nama', '$username', '$email', '$password_hash')";
            if (mysqli_query($conn, $q)) {
                $success_msg = 'Akun berhasil dibuat! Silakan login.';
            } else {
                $error_msg = 'Gagal mendaftar, coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Daftar — Tracker Tugas</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
    <style>
        /* Hilangkan background ungu */
        body {
            background: var(--bg-body) !important;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        body::before, body::after {
            display: none !important;
        }
        
        .auth-container {
            max-width: 440px;
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
        
        .success-alert {
            background: var(--success-light);
            border-left: 4px solid var(--success);
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            margin-bottom: 24px;
            color: var(--success);
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .success-alert::before {
            content: "✅";
        }
        
        .success-alert a {
            color: var(--success);
            font-weight: 700;
            margin-left: 8px;
        }
        
        .strength-wrap {
            display: flex;
            gap: 6px;
            margin-top: 8px;
        }
        
        .strength-seg {
            flex: 1;
            height: 4px;
            background: var(--border);
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .strength-seg.weak { background: var(--danger); }
        .strength-seg.medium { background: var(--warning); }
        .strength-seg.strong { background: var(--success); }

        /* Password strength indicator */
.strength-wrap {
    display: flex;
    gap: 6px;
    margin-top: 8px;
}

.strength-seg {
    flex: 1;
    height: 6px;
    background: var(--border);
    border-radius: 4px;
    transition: all 0.2s;
}

.strength-seg.weak { background: #EF4444; }
.strength-seg.medium { background: #F59E0B; }
.strength-seg.strong { background: #10B981; }

#strength-text {
    font-size: 11px;
    margin-top: 6px;
}

.strength-weak { color: #EF4444; }
.strength-medium { color: #F59E0B; }
.strength-strong { color: #10B981; }
    </style>
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <span>✏️</span>
        </div>
        <div class="auth-title">Daftar Akun</div>
        <div class="auth-sub">Buat akun baru untuk mulai manage tugas</div>

        <?php if ($error_msg): ?>
            <div class="error-alert">
                <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
    <div class="success-alert" style="text-align: center; padding: 20px; background: linear-gradient(135deg, var(--success-light) 0%, rgba(16,185,129,0.1) 100%); border-radius: var(--radius-md); border: 1px solid var(--success); margin-bottom: 24px;">
        <div style="font-size: 48px; margin-bottom: 12px;">🎉</div>
        <div style="font-size: 18px; font-weight: 700; color: var(--success); margin-bottom: 8px;">
            <?= htmlspecialchars($success_msg) ?>
        </div>
        <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
            Kamu sekarang punya akun! Yuk langsung masuk.
        </div>
        <a href="login.php" style="display: inline-flex; align-items: center; gap: 8px; background: var(--success); color: white; padding: 10px 24px; border-radius: 50px; text-decoration: none; font-weight: 600; transition: all 0.2s; border: none; cursor: pointer;">
            ✨ Masuk Sekarang
            <span style="font-size: 16px;">→</span>
        </a>
    </div>
<?php endif; ?>

        <form method="POST">
            <div class="auth-field">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" placeholder="Nama lengkap" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
            </div>
            <div class="auth-field">
                <label>Username</label>
                <input type="text" name="username" placeholder="Pilih username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>
            <div class="auth-field">
                <label>Email</label>
                <input type="email" name="email" placeholder="" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="auth-field">
                <label>Password (min. 8 karakter)</label>
                <input type="password" name="password" id="password" placeholder="Buat password" required oninput="checkStrength(this)">
                <div class="strength-wrap">
    <div class="strength-wrap">
    <div class="strength-seg" id="seg1"></div>
    <div class="strength-seg" id="seg2"></div>
    <div class="strength-seg" id="seg3"></div>
    <div class="strength-seg" id="seg4"></div>
</div>
<div id="strength-text" style="font-size: 12px; margin-top: 6px;">
    <span id="strength-label">🔒 Masukkan password</span>
</div>
<div id="password-suggestions" style="margin-top: 8px;"></div>

<div style="margin-top: 12px; padding: 10px; background: var(--bg-surface-alt); border-radius: 8px; font-size: 12px; color: var(--text-muted);">
    <strong>🔐 Contoh password kuat:</strong><br>
    • <code>G0lang!B0ss</code> - pakai huruf besar, kecil, angka, simbol<br>
    • <code>Rahasia#789</code> - kombinasi kata + angka + simbol<br>
    • <code>Suk@Makan69</code> - mudah diingat tapi susah ditebak
</div>
            </div>
            <button type="submit" name="register" class="auth-btn">Daftar →</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="login.php">Masuk di sini</a>
        </div>
    </div>
</div>

<script>
    function checkStrength(inp) {
        const v = inp.value;
        let score = 0;
        if (v.length >= 8) score++;
        if (/[A-Z]/.test(v)) score++;
        if (/[0-9]/.test(v)) score++;
        if (/[^A-Za-z0-9]/.test(v)) score++;
        
        let strengthClass = '';
        if (score <= 1) strengthClass = 'weak';
        else if (score <= 2) strengthClass = 'medium';
        else strengthClass = 'strong';
        
        for (let i = 1; i <= 4; i++) {
            const el = document.getElementById('seg' + i);
            if (i <= score) {
                el.className = 'strength-seg ' + strengthClass;
            } else {
                el.className = 'strength-seg';
            }
        }
    }

    (function() {
        const theme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', theme);
    })();
</script>
</body>
</html>