<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "SELECT * FROM tugas WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "is", $id, $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row    = mysqli_fetch_assoc($result);

if (!$row) {
    header("Location: index.php");
    exit;
}

$errors = [];

if (isset($_POST['update'])) {
    $matkul   = trim($_POST['matkul'] ?? '');
    $tugas    = trim($_POST['tugas'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');
    $today    = date('Y-m-d');
    $prioritas = $_POST['prioritas'];
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $catatan  = mysqli_real_escape_string($conn, $_POST['catatan']);

    if ($matkul === '') $errors[] = "Mata kuliah tidak boleh kosong.";
    if ($tugas === '') $errors[] = "Detail tugas tidak boleh kosong.";
    if ($deadline === '') $errors[] = "Deadline tidak boleh kosong.";
    if ($deadline < $today) $errors[] = "Deadline tidak boleh kurang dari hari ini.";

    if (empty($errors)) {
        $upd = mysqli_prepare($conn, "UPDATE tugas SET matkul=?, tugas=?, deadline=?, prioritas=?, kategori=?, catatan=? WHERE id=? AND user_id=?");
        mysqli_stmt_bind_param($upd, "ssssssii", $matkul, $tugas, $deadline, $prioritas, $kategori, $catatan, $id, $id_user);

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
                   value="<?= htmlspecialchars($row['matkul']) ?>" required>
        </div>

        <div class="form-group">
            <label>Detail Tugas</label>
            <textarea name="tugas" rows="4" required><?= htmlspecialchars($row['tugas']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Deadline</label>
            <input type="date" name="deadline"
                   value="<?= htmlspecialchars($row['deadline']) ?>" 
                   min="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="form-group">
            <label>Prioritas</label>
            <select name="prioritas">
                <option value="Tinggi" <?= $row['prioritas'] == 'Tinggi' ? 'selected' : '' ?>>🔥 Tinggi</option>
                <option value="Sedang" <?= $row['prioritas'] == 'Sedang' ? 'selected' : '' ?>>⚡ Sedang</option>
                <option value="Rendah" <?= $row['prioritas'] == 'Rendah' ? 'selected' : '' ?>>✅ Rendah</option>
            </select>
        </div>

        <div class="form-group">
            <label>Kategori</label>
            <select name="kategori">
                <option value="UTS" <?= $row['kategori'] == 'UTS' ? 'selected' : '' ?>>📝 UTS</option>
                <option value="UAS" <?= $row['kategori'] == 'UAS' ? 'selected' : '' ?>>📚 UAS</option>
                <option value="Tugas Harian" <?= $row['kategori'] == 'Tugas Harian' ? 'selected' : '' ?>>✏️ Tugas Harian</option>
                <option value="Kelompok" <?= $row['kategori'] == 'Kelompok' ? 'selected' : '' ?>>👥 Kelompok</option>
                <option value="Praktikum" <?= $row['kategori'] == 'Praktikum' ? 'selected' : '' ?>>💻 Praktikum</option>
                <option value="Umum" <?= $row['kategori'] == 'Umum' ? 'selected' : '' ?>>📋 Umum</option>
            </select>
        </div>

        <div class="form-group">
            <label>Catatan</label>
            <textarea name="catatan" rows="3" placeholder="Catatan tambahan..."><?= htmlspecialchars($row['catatan']) ?></textarea>
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