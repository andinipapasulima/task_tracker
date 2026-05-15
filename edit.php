<?php
session_start();
include 'koneksi.php';

// Wajib login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data + verifikasi kepemilikan (user hanya bisa edit tugasnya sendiri)
$stmt = mysqli_prepare($conn, "SELECT * FROM tugas WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "is", $id, $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row    = mysqli_fetch_assoc($result);

if (!$row) {
    // Tugas tidak ditemukan atau bukan milik user ini
    header("Location: index.php");
    exit;
}

$errors = [];

if (isset($_POST['update'])) {
    $matkul   = trim($_POST['matkul']   ?? '');
    $tugas    = trim($_POST['tugas']    ?? '');
    $deadline = trim($_POST['deadline'] ?? '');

    if ($matkul   === '') $errors[] = "Mata kuliah tidak boleh kosong.";
    if ($tugas    === '') $errors[] = "Detail tugas tidak boleh kosong.";
    if ($deadline === '') $errors[] = "Deadline tidak boleh kosong.";

    if (empty($errors)) {
        $upd = mysqli_prepare($conn, "UPDATE tugas SET matkul=?, tugas=?, deadline=? WHERE id=? AND user_id=?");
        mysqli_stmt_bind_param($upd, "sssii", $matkul, $tugas, $deadline, $id, $id_user);

        if (mysqli_stmt_execute($upd)) {
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Gagal menyimpan perubahan. Coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Edit Tugas</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>
<div class="container">

    <a href="index.php" class="btn-kembali">← Batal</a>
    <h2 class="page-title">✏️ Edit Tugas</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-msg" style="margin-bottom:20px;">
            <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Mata Kuliah</label>
            <input type="text" name="matkul"
                   value="<?= htmlspecialchars($row['matkul']) ?>" required
                   placeholder="Contoh: Pemrograman Web">
        </div>

        <div class="form-group">
            <label>Detail Tugas</label>
            <textarea name="tugas" rows="4" required
                      placeholder="Apa yang harus dikerjakan?"><?= htmlspecialchars($row['tugas']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Deadline</label>
            <input type="date" name="deadline"
                   value="<?= htmlspecialchars($row['deadline']) ?>" required>
        </div>

        <button type="submit" name="update">💾 Simpan Perubahan</button>
    </form>

</div>
<script>
(function () {
    const t = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', t);
})();
</script>
</body>
</html>