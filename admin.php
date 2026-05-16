<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Cek role admin
$id_user = $_SESSION['id_user'];
$user_check = mysqli_query($conn, "SELECT role FROM users WHERE id='$id_user'");
$user_data = mysqli_fetch_assoc($user_check);

if ($user_data['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Handle reset password
if (isset($_POST['reset_password'])) {
    $target_user = (int)$_POST['user_id'];
    $new_password = password_hash('password123', PASSWORD_DEFAULT);
    mysqli_query($conn, "UPDATE users SET password='$new_password' WHERE id='$target_user'");
    $message = "Password user telah direset menjadi 'password123'";
}

// Handle delete user
if (isset($_GET['delete_user'])) {
    $delete_id = (int)$_GET['delete_user'];
    mysqli_query($conn, "DELETE FROM tugas WHERE user_id='$delete_id'");
    mysqli_query($conn, "DELETE FROM users WHERE id='$delete_id'");
    $message = "User berhasil dihapus!";
}

// Statistik
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
$total_tasks = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tugas"))['total'];
$total_completed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tugas WHERE status='Selesai'"))['total'];

// Ambil semua user
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — Tracker Tugas</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
    <style>
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .admin-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 20px;
            text-align: center;
        }
        .admin-number {
            font-size: 32px;
            font-weight: 800;
            color: var(--accent);
        }
        .user-table {
            width: 100%;
            overflow-x: auto;
        }
        .user-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .user-table th, .user-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        .role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .role-admin {
            background: var(--accent-light);
            color: var(--accent);
        }
        .role-user {
            background: var(--bg-surface-alt);
            color: var(--text-muted);
        }
    </style>
</head>
<body>
<div class="container">
    <a href="index.php" class="btn-kembali">← Kembali ke Beranda</a>
    <h2 class="page-title">👑 Admin Panel</h2>

    <?php if (isset($message)): ?>
        <div class="error-msg" style="background: var(--success-light); color: var(--success);">✅ <?= $message ?></div>
    <?php endif; ?>

    <div class="admin-stats">
        <div class="admin-card">
            <div class="admin-number"><?= $total_users ?></div>
            <div>Total User</div>
        </div>
        <div class="admin-card">
            <div class="admin-number"><?= $total_tasks ?></div>
            <div>Total Tugas</div>
        </div>
        <div class="admin-card">
            <div class="admin-number"><?= $total_completed ?></div>
            <div>Tugas Selesai</div>
        </div>
        <div class="admin-card">
            <div class="admin-number"><?= round(($total_completed/$total_tasks)*100) ?>%</div>
            <div>Completion Rate</div>
        </div>
    </div>

    <h3>📋 Daftar User</h3>
    <div class="user-table">
        <table>
            <thead>
                <tr><th>ID</th><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Bergabung</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php while($user = mysqli_fetch_assoc($users)): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['nama']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span class="role-badge <?= $user['role'] == 'admin' ? 'role-admin' : 'role-user' ?>">
                            <?= $user['role'] ?>
                        </span>
                    </td>
                    <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                    <td>
                        <?php if($user['id'] != $_SESSION['id_user']): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="submit" name="reset_password" style="background: var(--warning); padding: 4px 8px; font-size: 11px;">Reset Pass</button>
                        </form>
                        <a href="?delete_user=<?= $user['id'] ?>" onclick="return confirm('Yakin hapus user ini? Semua tugasnya akan hilang!')" style="background: var(--danger); padding: 4px 8px; border-radius: 4px; color: white; text-decoration: none; font-size: 11px;">Hapus</a>
                        <?php else: ?>
                        <small style="color: var(--text-muted);">(Anda)</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>