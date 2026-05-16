<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$error = '';

if (isset($_POST['simpan'])) {
    $matkul   = mysqli_real_escape_string($conn, $_POST['matkul']);
    $tugas    = mysqli_real_escape_string($conn, $_POST['tugas']);
    $deadline = $_POST['deadline'];
    $today    = date('Y-m-d');
    $prioritas = $_POST['prioritas'];
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $catatan  = mysqli_real_escape_string($conn, $_POST['catatan']);
    $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
    $recurring_type = $_POST['recurring_type'] ?? null;

    if ($deadline < $today) {
        $error = "Deadline tidak boleh kurang dari hari ini!";
    } else {
        $query = "INSERT INTO tugas (user_id, matkul, tugas, deadline, status, prioritas, kategori, catatan, is_recurring, recurring_type) 
                  VALUES ('$id_user', '$matkul', '$tugas', '$deadline', 'Belum', '$prioritas', '$kategori', '$catatan', '$is_recurring', '$recurring_type')";
        
        if (mysqli_query($conn, $query)) {
            // Log aktivitas
            $log = "INSERT INTO activity_logs (user_id, action) VALUES ('$id_user', 'Menambah tugas: $matkul')";
            mysqli_query($conn, $log);
            
            header("Location: index.php");
            exit;
        } else {
            $error = "Gagal menambah tugas: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tambah Tugas Baru</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn-kembali"><span>←</span> Kembali ke Daftar</a>
    <h2>Tambah Tugas Baru</h2>

    <?php if ($error): ?>
        <div class="error-msg" style="margin-bottom:20px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Mata Kuliah</label>
        <input type="text" name="matkul" placeholder="Contoh: Pemrograman Web" required>

        <label>Detail Tugas</label>
        <textarea name="tugas" rows="4" placeholder="Apa yang harus dikerjakan?" required></textarea>

        <label>Deadline</label>
        <input type="date" name="deadline" min="<?= date('Y-m-d') ?>" required>

        <label>Prioritas</label>
        <select name="prioritas">
            <option value="Tinggi">🔥 Tinggi</option>
            <option value="Sedang" selected>⚡ Sedang</option>
            <option value="Rendah">✅ Rendah</option>
        </select>

        <label>Kategori</label>
        <select name="kategori">
            <option value="UTS">📝 UTS</option>
            <option value="UAS">📚 UAS</option>
            <option value="Tugas Harian">✏️ Tugas Harian</option>
            <option value="Kelompok">👥 Kelompok</option>
            <option value="Praktikum">💻 Praktikum</option>
            <option value="Umum">📋 Umum</option>
        </select>

        <label>Catatan (opsional)</label>
        <textarea name="catatan" rows="3" placeholder="Tambahkan catatan pribadi..."></textarea>

        <label>
            <input type="checkbox" name="is_recurring" id="recurring_checkbox"> Tugas berulang?
        </label>

        <div id="recurring_options" style="display: none;">
            <label>Jenis Pengulangan</label>
            <select name="recurring_type">
                <option value="mingguan">📅 Setiap Minggu</option>
                <option value="bulanan">📆 Setiap Bulan</option>
            </select>
        </div>

        <button type="submit" name="simpan">Simpan ke Daftar</button>
    </form>
</div>

<script>
document.getElementById('recurring_checkbox').addEventListener('change', function() {
    const options = document.getElementById('recurring_options');
    options.style.display = this.checked ? 'block' : 'none';
});
</script>
</body>
</html>