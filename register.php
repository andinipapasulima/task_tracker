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
        // Cek username sudah dipakai
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
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar — TaskTracker</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; position: relative; background: #0d0c2a;
  }

  .bg { position: fixed; inset: 0; background: linear-gradient(135deg, #0d0c2a 0%, #1a1740 50%, #0f1629 100%); z-index: 0; }
  .blob { position: fixed; border-radius: 50%; filter: blur(90px); animation: floatBlob 12s ease-in-out infinite alternate; z-index: 1; }
  .b1 { width: 480px; height: 480px; background: rgba(168,85,247,0.4); top: -130px; right: -100px; }
  .b2 { width: 380px; height: 380px; background: rgba(99,102,241,0.4); bottom: -80px; left: -80px; animation-delay: -5s; }
  .b3 { width: 240px; height: 240px; background: rgba(236,72,153,0.22); top: 45%; right: 60%; animation-delay: -8s; }

  @keyframes floatBlob { 0% { transform: translate(0,0) scale(1); } 100% { transform: translate(-20px, 20px) scale(1.07); } }

  .noise { position: fixed; inset: 0; pointer-events: none; z-index: 2; opacity: 0.3;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.05'/%3E%3C/svg%3E");
  }

  .wrap { position: relative; z-index: 10; width: 100%; max-width: 440px; padding: 16px; }

  .card {
    background: rgba(255,255,255,0.07);
    backdrop-filter: blur(28px) saturate(180%);
    -webkit-backdrop-filter: blur(28px) saturate(180%);
    border-radius: 28px;
    border: 1px solid rgba(255,255,255,0.13);
    padding: 44px 40px 38px;
    box-shadow: 0 0 0 1px rgba(255,255,255,0.05), 0 40px 80px rgba(0,0,0,0.55), 0 0 60px rgba(168,85,247,0.1);
    position: relative;
  }

  .card::before {
    content: ''; position: absolute; top: 0; left: 15%; right: 15%; height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.35), transparent);
  }

  .brand { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 28px; }
  .brand-icon {
    width: 40px; height: 40px; border-radius: 11px;
    background: linear-gradient(135deg, #a855f7, #6366f1);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 18px rgba(168,85,247,0.45);
  }
  .brand-icon svg { width: 20px; height: 20px; fill: none; stroke: white; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
  .brand-name { font-size: 19px; font-weight: 800; color: white; letter-spacing: -0.3px; }

  .greeting { font-size: 21px; font-weight: 800; color: white; margin-bottom: 4px; }
  .subtext { font-size: 13px; color: rgba(255,255,255,0.5); margin-bottom: 26px; }

  .alert {
    border-radius: 12px; padding: 12px 16px; font-size: 13px; font-weight: 600;
    margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
  }
  .alert-error { background: rgba(239,68,68,0.13); border: 1px solid rgba(239,68,68,0.28); color: #fca5a5; }
  .alert-success { background: rgba(16,185,129,0.13); border: 1px solid rgba(16,185,129,0.28); color: #6ee7b7; }

  .field { margin-bottom: 16px; }
  .field label {
    display: block; font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1.2px; color: rgba(255,255,255,0.6); margin-bottom: 7px;
  }
  .input-wrap { position: relative; }
  .input-wrap svg.icon {
    position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
    width: 16px; height: 16px; stroke: rgba(255,255,255,0.32); fill: none;
    stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
    pointer-events: none; transition: stroke 0.2s;
  }
  .field input {
    width: 100%; padding: 13px 13px 13px 40px;
    background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.13);
    border-radius: 13px; color: white; font-family: inherit; font-size: 14px;
    font-weight: 500; outline: none; transition: all 0.25s ease; margin: 0;
  }
  .field input::placeholder { color: rgba(255,255,255,0.28); }
  .field input:focus {
    border-color: rgba(168,85,247,0.65); background: rgba(255,255,255,0.13);
    box-shadow: 0 0 0 4px rgba(168,85,247,0.14);
  }
  .input-wrap:focus-within svg.icon { stroke: rgba(196,181,253,0.75); }

  /* Password strength */
  .strength-wrap { display: flex; gap: 5px; margin-top: 8px; }
  .seg { flex: 1; height: 4px; border-radius: 4px; background: rgba(255,255,255,0.1); transition: background 0.3s; }
  .seg.weak { background: #f87171; }
  .seg.medium { background: #fbbf24; }
  .seg.strong { background: #34d399; }

  .btn-submit {
    width: 100%; padding: 14px; border: none; border-radius: 13px;
    background: linear-gradient(135deg, #a855f7 0%, #7c3aed 60%, #6366f1 100%);
    color: white; font-family: inherit; font-size: 15px; font-weight: 700;
    cursor: pointer; margin-top: 6px; transition: all 0.25s ease;
    box-shadow: 0 8px 24px rgba(168,85,247,0.4); position: relative; overflow: hidden;
  }
  .btn-submit::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(to bottom, rgba(255,255,255,0.12), transparent);
    pointer-events: none;
  }
  .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 14px 32px rgba(168,85,247,0.55); }

  .footer-link { text-align: center; margin-top: 22px; font-size: 13px; color: rgba(255,255,255,0.45); }
  .footer-link a { color: #c4b5fd; text-decoration: none; font-weight: 700; }
  .footer-link a:hover { color: #ddd6fe; }
</style>
</head>
<body>

<div class="bg"></div>
<div class="blob b1"></div>
<div class="blob b2"></div>
<div class="blob b3"></div>
<div class="noise"></div>

<div class="wrap">
  <div class="card">

    <div class="brand">
      <div class="brand-icon">
        <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
      </div>
      <span class="brand-name">TaskTracker</span>
    </div>

    <div class="greeting">Buat akun baru ✨</div>
    <div class="subtext">Daftar dan mulai atur tugas kuliahmu</div>

    <?php if ($error_msg): ?>
    <div class="alert alert-error">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($error_msg); ?>
    </div>
    <?php endif; ?>

    <?php if ($success_msg): ?>
    <div class="alert alert-success">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
      <?= htmlspecialchars($success_msg); ?>
      <a href="login.php" style="color:inherit; margin-left:4px; text-decoration:underline;">Masuk sekarang →</a>
    </div>
    <?php endif; ?>

    <form action="" method="POST">

      <div class="field">
        <label>Nama Lengkap</label>
        <div class="input-wrap">
          <svg class="icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <input type="text" name="nama" placeholder="Nama lengkapmu" required value="<?= htmlspecialchars($_POST['nama'] ?? ''); ?>">
        </div>
      </div>

      <div class="field">
        <label>Username</label>
        <div class="input-wrap">
          <svg class="icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <input type="text" name="username" placeholder="Pilih username unik" required value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>">
        </div>
      </div>

      <div class="field">
        <label>Email</label>
        <div class="input-wrap">
          <svg class="icon" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <input type="email" name="email" placeholder="email@kampus.ac.id" required value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
      </div>

      <div class="field">
        <label>Password</label>
        <div class="input-wrap">
          <svg class="icon" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          <input type="password" name="password" id="pw-reg" placeholder="Min. 8 karakter" required oninput="checkStr(this)">
        </div>
        <div class="strength-wrap">
          <div class="seg" id="s1"></div>
          <div class="seg" id="s2"></div>
          <div class="seg" id="s3"></div>
          <div class="seg" id="s4"></div>
        </div>
      </div>

      <button class="btn-submit" type="submit" name="register">Buat Akun →</button>
    </form>

    <div class="footer-link">
      Sudah punya akun? <a href="login.php">Masuk di sini</a>
    </div>

  </div>
</div>

<script>
  function checkStr(inp) {
    const v = inp.value;
    let s = 0;
    if (v.length >= 8) s++;
    if (/[A-Z]/.test(v)) s++;
    if (/[0-9]/.test(v)) s++;
    if (/[^A-Za-z0-9]/.test(v)) s++;
    const cls = s <= 1 ? 'weak' : s <= 2 ? 'medium' : 'strong';
    for (let i = 1; i <= 4; i++) {
      const el = document.getElementById('s' + i);
      el.className = 'seg' + (i <= s ? ' ' + cls : '');
    }
  }
</script>
</body>
</html>