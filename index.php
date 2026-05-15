<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$nama_user = $_SESSION['nama'];
date_default_timezone_set('Asia/Jakarta');
$jam = date('H');
if ($jam >= 5 && $jam < 12) {
    $sapaan = "Selamat Pagi";
} elseif ($jam >= 12 && $jam < 17) {
    $sapaan = "Selamat Siang";
} elseif ($jam >= 17 && $jam < 20) {
    $sapaan = "Selamat Sore";
} else {
    $sapaan = "Selamat Malam";
}
// Menghitung tugas yang deadline-nya HARI INI dan statusnya BELUM SELESAI
$today = date('Y-m-d');
$query_notif = "SELECT COUNT(*) as total FROM tugas WHERE user_id = '$id_user' AND deadline = '$today' AND status != 'Selesai'";
$sql_notif = mysqli_query($conn, $query_notif);
$data_notif = mysqli_fetch_assoc($sql_notif);
$jumlah_tugas_hari_ini = $data_notif['total'];

// 1. Ambil keyword cari dan filter status
$keyword = isset($_POST['keyword']) ? mysqli_real_escape_string($conn, $_POST['keyword']) : "";
$filter  = isset($_GET['filter']) ? mysqli_real_escape_string($conn, $_GET['filter']) : "";

// 2. Bangun Query Dasar
$query = "SELECT * FROM tugas WHERE user_id = '$id_user'";

// 3. Tambahkan kondisi jika sedang mencari (Keyword)
if ($keyword != "") {
    $query .= " AND (matkul LIKE '%$keyword%' OR tugas LIKE '%$keyword%')";
}

// 4. Tambahkan kondisi jika sedang memfilter (Status)
if ($filter != "") {
    $query .= " AND status LIKE '%$filter%'";
}

// 5. Tambahkan pengurutan (Deadline Terdekat)
$query .= " ORDER BY deadline ASC";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tracker Tugas Saya</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h3 style="margin:0; color: var(--text-main);">Halo, <?= $nama_user; ?>! 👋</h3>
            <?php if ($jumlah_tugas_hari_ini > 0) : ?>
    <div class="notif-deadline">
        <div class="notif-icon">⚠️</div>
        <div class="notif-text">
            <strong>Ada tugas darurat!</strong>
            <p>Kamu punya <?= $jumlah_tugas_hari_ini; ?> tugas yang harus kelar hari ini.</p>
        </div>
    </div>
<?php endif; ?>
            <p style="margin:0; font-size: 12px; color: var(--text-muted);">Semangat ngerjain tugasnya!</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button id="theme-toggle">🌙 Mode Gelap</button>
            <a href="logout.php" onclick="return confirm('Yakin mau keluar?')">Logout</a>
        </div>
    </div>

    <h2>Daftar Tugas Kuliah</h2>
    
    <form class="search-form" method="POST">
        <input type="text" name="keyword" placeholder="Cari matkul atau tugas..." value="<?= $keyword; ?>">
        <button type="submit" name="cari">Cari</button>
    </form>

    <a href="tambah.php" class="btn-tambah">+ Tambah Tugas Baru</a>
    <div class="filter-container" style="margin-bottom: 20px; display: flex; gap: 10px; overflow-x: auto; padding-bottom: 5px;">
    <a href="index.php" class="btn-filter <?= !isset($_GET['filter']) ? 'active' : '' ?>">Semua</a>
    <a href="index.php?filter=Belum" 
   class="btn-filter <?= ($_GET['filter'] ?? '') == 'Belum' ? 'active' : '' ?>">
   Belum Selesai
</a>

<a href="index.php?filter=Selesai" 
   class="btn-filter <?= ($_GET['filter'] ?? '') == 'Selesai' ? 'active' : '' ?>">
   Selesai
</a>
</div>
    <div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Mata Kuliah</th>
                <th>Tugas</th>
                <th>Deadline</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)) : ?>
            <tr>
                <td><strong><?= $row['matkul']; ?></strong></td>
                <td><?= $row['tugas']; ?></td>
                <td style="<?= ($row['status'] != 'Selesai' && $selisih <= 0) ? 'color: #fc8181; font-weight: bold;' : ''; ?>">
    <?= date('d M Y', strtotime($row['deadline'])); ?>
    <br>
    <?php 
        // Logika hitung sisa hari (sudah ada di kode kamu)
        $deadline = strtotime($row['deadline']);
        $hari_ini = strtotime(date('Y-m-d'));
        $selisih  = ($deadline - $hari_ini) / 60 / 60 / 24;

        if ($row['status'] == 'Selesai') {
            echo "<small style='color: #68d391;'>Tugas tuntas!</small>";
        } elseif ($selisih < 0) {
            echo "<small style='color: #fc8181; font-weight: bold;'>Terlewat " . abs($selisih) . " hari</small>";
        } elseif ($selisih == 0) {
            echo "<small style='color: #ecc94b; font-weight: bold;'>Hari ini!</small>";
        } else {
            echo "<small style='color: var(--text-muted);'>$selisih hari lagi</small>";
        }
    ?>
</td>
                <td>
                    <span class="badge <?= ($row['status'] == 'Selesai') ? 'selesai' : 'belum'; ?>">
                        <?= $row['status']; ?>
                    </span>
                </td>
                <td>
                    <a href="selesai.php?id=<?= $row['id']; ?>" title="Selesai">✅</a>
                    <a href="edit.php?id=<?= $row['id']; ?>" title="Edit">✏️</a>
                    <a href="hapus.php?id=<?= $row['id']; ?>" onclick="return confirm('Hapus tugas ini?')" title="Hapus">🗑️</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>

<script>
    const toggleBtn = document.getElementById('theme-toggle');

    // Kita panggil fungsi applyTheme dari script.js untuk sinkronisasi awal
    if (typeof applyTheme === "function") {
        applyTheme();
    }

    toggleBtn.addEventListener('click', () => {
        let currentTheme = document.documentElement.getAttribute('data-theme');
        let newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        // 1. Simpan pilihan baru ke localStorage
        localStorage.setItem('theme', newTheme);
        
        // 2. Terapkan tema ke seluruh halaman
        if (typeof applyTheme === "function") {
            applyTheme();
        } else {
            // Fallback jika script.js gagal dimuat
            document.documentElement.setAttribute('data-theme', newTheme);
            toggleBtn.innerHTML = newTheme === 'dark' ? '☀️ Mode Terang' : '🌙 Mode Gelap';
        }
    });
</script>
</body>
</html>