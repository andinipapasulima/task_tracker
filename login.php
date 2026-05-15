<?php
session_start();
if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error = isset($_GET['error']) ? true : false;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — TaskTracker</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
    background: #0d0c2a;
  }

  /* Background */
  .bg { position: fixed; inset: 0; background: linear-gradient(135deg, #0d0c2a 0%, #1a1740 50%, #0f1629 100%); z-index: 0; }

  .blob {
    position: fixed;
    border-radius: 50%;
    filter: blur(90px);
    animation: floatBlob 12s ease-in-out infinite alternate;
    z-index: 1;
  }
  .b1 { width: 500px; height: 500px; background: rgba(99,102,241,0.45); top: -150px; left: -120px; }
  .b2 { width: 380px; height: 380px; background: rgba(168,85,247,0.4); bottom: -80px; right: -80px; animation-delay: -4s; }
  .b3 { width: 260px; height: 260px; background: rgba(236,72,153,0.25); top: 35%; left: 65%; animation-delay: -7s; }

  @keyframes floatBlob {
    0%   { transform: translate(0, 0) scale(1); }
    100% { transform: translate(25px, -25px) scale(1.06); }
  }

  .noise {
    position: fixed; inset: 0; pointer-events: none; z-index: 2; opacity: 0.3;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.05'/%3E%3C/svg%3E");
  }

  /* Card */
  .wrap { position: relative; z-index: 10; width: 100%; max-width: 420px; padding: 16px; }

  .card {
    background: rgba(255,255,255,0.07);
    backdrop-filter: blur(28px) saturate(180%);
    -webkit-backdrop-filter: blur(28px) saturate(180%);
    border-radius: 28px;
    border: 1px solid rgba(255,255,255,0.14);
    padding: 44px 40px 40px;
    box-shadow: 0 0 0 1px rgba(255,255,255,0.05), 0 40px 80px rgba(0,0,0,0.55), 0 0 60px rgba(99,102,241,0.1);
    position: relative;
  }

  .card::before {
    content: '';
    position: absolute;
    top: 0; left: 15%; right: 15%;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
  }

  /* Brand */
  .brand {
    display: flex; align-items: center; justify-content: center; gap: 10px;
    margin-bottom: 32px;
  }

  .brand-icon {
    width: 40px; height: 40px; border-radius: 11px;
    background: linear-gradient(135deg, #6366f1, #a855f7);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 18px rgba(99,102,241,0.45);
  }

  .brand-icon svg { width: 20px; height: 20px; fill: none; stroke: white; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
  .brand-name { font-size: 19px; font-weight: 800; color: white; letter-spacing: -0.3px; }

  /* Greeting */
  .greeting { font-size: 22px; font-weight: 800; color: white; margin-bottom: 4px; letter-spacing: -0.3px; }
  .subtext { font-size: 13px; color: rgba(255,255,255,0.5); margin-bottom: 30px; }

  /* Error Alert */
  .alert-error {
    background: rgba(239,68,68,0.15);
    border: 1px solid rgba(239,68,68,0.3);
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 13px;
    font-weight: 600;
    color: #fca5a5;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  /* Fields */
  .field { margin-bottom: 18px; }
  .field label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: rgba(255,255,255,0.65);
    margin-bottom: 8px;
  }

  .input-wrap { position: relative; }

  .input-wrap svg.icon {
    position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
    width: 17px; height: 17px;
    stroke: rgba(255,255,255,0.35); fill: none;
    stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
    pointer-events: none; transition: stroke 0.2s;
  }

  .field input {
    width: 100%;
    padding: 13px 14px 13px 42px;
    background: rgba(255,255,255,0.09);
    border: 1px solid rgba(255,255,255,0.14);
    border-radius: 13px;
    color: white;
    font-family: inherit;
    font-size: 14px;
    font-weight: 500;
    outline: none;
    transition: all 0.25s ease;
    margin: 0;
  }

  .field input::placeholder { color: rgba(255,255,255,0.3); }

  .field input:focus {
    border-color: rgba(99,102,241,0.65);
    background: rgba(255,255,255,0.14);
    box-shadow: 0 0 0 4px rgba(99,102,241,0.15);
  }

  .field input:focus ~ svg.icon,
  .input-wrap:focus-within svg.icon { stroke: rgba(165,180,252,0.8); }

  /* Forgot */
  .row-forgot { display: flex; justify-content: flex-end; margin: -6px 0 18px; }
  .row-forgot a { font-size: 12px; color: #a5b4fc; text-decoration: none; font-weight: 600; }
  .row-forgot a:hover { color: #c4b5fd; }

  /* Submit */
  .btn-submit {
    width: 100%; padding: 14px; border: none; border-radius: 13px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 60%, #a855f7 100%);
    color: white; font-family: inherit; font-size: 15px; font-weight: 700;
    cursor: pointer; letter-spacing: 0.2px; transition: all 0.25s ease;
    box-shadow: 0 8px 24px rgba(99,102,241,0.4); position: relative; overflow: hidden;
  }

  .btn-submit::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(to bottom, rgba(255,255,255,0.12), transparent);
    pointer-events: none;
  }

  .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 14px 32px rgba(99,102,241,0.55); }
  .btn-submit:active { transform: translateY(0); }

  /* Footer link */
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

    <div class="greeting">Selamat datang! 👋</div>
    <div class="subtext">Masuk untuk mengelola tugas kuliahmu</div>

    <?php if ($error): ?>
    <div class="alert-error">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      Username atau password salah, coba lagi.
    </div>
    <?php endif; ?>

    <form action="proses_login.php" method="POST">

      <div class="field">
        <label>Username</label>
        <div class="input-wrap">
          <svg class="icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <input type="text" name="username" placeholder="Masukkan username" required autocomplete="username">
        </div>
      </div>

      <div class="field">
        <label>Password</label>
        <div class="input-wrap">
          <svg class="icon" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          <input type="password" name="password" id="pw" placeholder="Masukkan password" required autocomplete="current-password">
        </div>
      </div>

      <div class="row-forgot"><a href="#">Lupa password?</a></div>

      <button class="btn-submit" type="submit" name="login">Masuk Sekarang →</button>
    </form>

    <div class="footer-link">
      Belum punya akun? <a href="register.php">Daftar di sini</a>
    </div>

  </div>
</div>

</body>
</html>