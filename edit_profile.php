<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$message = '';
$error = '';

// Ambil data user
$query = "SELECT * FROM users WHERE id = '$id_user'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (isset($_POST['update_profile'])) {
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($nama) || empty($email)) {
        $error = "Nama dan email tidak boleh kosong!";
    } elseif (!empty($new_password)) {
        // Jika ingin ganti password
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 8) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update = "UPDATE users SET nama='$nama', email='$email', password='$hashed_password' WHERE id='$id_user'";
                    if (mysqli_query($conn, $update)) {
                        $_SESSION['nama'] = $nama;
                        $message = "Profile berhasil diupdate!";
                        // Refresh data user
                        $result = mysqli_query($conn, "SELECT * FROM users WHERE id='$id_user'");
                        $user = mysqli_fetch_assoc($result);
                    } else {
                        $error = "Gagal update profile.";
                    }
                } else {
                    $error = "Password minimal 8 karakter!";
                }
            } else {
                $error = "Password baru tidak cocok!";
            }
        } else {
            $error = "Password saat ini salah!";
        }
    } else {
        // Update tanpa ganti password
        $update = "UPDATE users SET nama='$nama', email='$email' WHERE id='$id_user'";
        if (mysqli_query($conn, $update)) {
            $_SESSION['nama'] = $nama;
            $message = "Profile berhasil diupdate!";
            $result = mysqli_query($conn, "SELECT * FROM users WHERE id='$id_user'");
            $user = mysqli_fetch_assoc($result);
        } else {
            $error = "Gagal update profile.";
        }
    }
}

// Upload foto profil
if (isset($_POST['upload_photo']) && isset($_FILES['foto'])) {
    $target_dir = "uploads/profiles/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($file_extension, $allowed)) {
        $new_filename = $id_user . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            $update = "UPDATE users SET foto_profil='$target_file' WHERE id='$id_user'";
            mysqli_query($conn, $update);
            $message = "Foto profil berhasil diupload!";
            $result = mysqli_query($conn, "SELECT * FROM users WHERE id='$id_user'");
            $user = mysqli_fetch_assoc($result);
        } else {
            $error = "Gagal upload foto.";
        }
    } else {
        $error = "Format file tidak didukung (JPG, PNG, GIF).";
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile — Tracker Tugas</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
    <style>
        .profile-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .profile-photo {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-photo img {
            width: 120px;
            height: 120px;
            border-radius: 60px;
            object-fit: cover;
            border: 3px solid var(--accent);
        }
        .message {
            padding: 12px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
        }
        .message-success {
            background: var(--success-light);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        .message-error {
            background: var(--danger-light);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
    </style>
</head>
<body>
<div class="container profile-container">
    <a href="index.php" class="btn-kembali">← Kembali</a>
    <h2 class="page-title">✏️ Edit Profile</h2>

    <?php if ($message): ?>
        <div class="message message-success">✅ <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="message message-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="profile-photo">
        <?php if ($user['foto_profil'] && file_exists($user['foto_profil'])): ?>
            <img src="<?= htmlspecialchars($user['foto_profil']) ?>" alt="Foto Profil">
        <?php else: ?>
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%236366f1'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E" alt="Default Avatar">
        <?php endif; ?>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Upload Foto Profil</label>
            <input type="file" name="foto" accept="image/*">
        </div>
        <button type="submit" name="upload_photo">Upload Foto</button>
    </form>

    <form method="POST" style="margin-top: 30px;">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled style="background: var(--border);">
            <small style="color: var(--text-muted);">Username tidak bisa diubah</small>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        
        <hr style="margin: 25px 0; border-color: var(--border);">
        <h3>Ganti Password (opsional)</h3>
        
        <div class="form-group">
            <label>Password Saat Ini</label>
            <input type="password" name="current_password" placeholder="Isi jika ingin ganti password">
        </div>
        <div class="form-group">
            <label>Password Baru</label>
            <input type="password" name="new_password" placeholder="Minimal 8 karakter">
        </div>
        <div class="form-group">
            <label>Konfirmasi Password Baru</label>
            <input type="password" name="confirm_password" placeholder="Ketik ulang password baru">
        </div>
        
        <button type="submit" name="update_profile">Simpan Perubahan</button>
    </form>
</div>
</body>
</html>