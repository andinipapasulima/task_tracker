<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user   = $_SESSION['id_user'];
$nama_user = $_SESSION['nama'];

date_default_timezone_set('Asia/Jakarta');
$jam = (int)date('H');
if      ($jam >= 5  && $jam < 12) $sapaan = "Selamat Pagi";
elseif  ($jam >= 12 && $jam < 17) $sapaan = "Selamat Siang";
elseif  ($jam >= 17 && $jam < 20) $sapaan = "Selamat Sore";
else                               $sapaan = "Selamat Malam";

$today       = date('Y-m-d');
$sql_notif   = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tugas WHERE user_id='$id_user' AND deadline='$today' AND status!='Selesai'");
$data_notif  = mysqli_fetch_assoc($sql_notif);
$tugas_hari_ini = (int)$data_notif['total'];

// Search & Filter
$keyword = isset($_POST['keyword']) ? mysqli_real_escape_string($conn, trim($_POST['keyword'])) : "";
$filter  = isset($_GET['filter'])   ? mysqli_real_escape_string($conn, $_GET['filter'])          : "";

$query = "SELECT * FROM tugas WHERE user_id='$id_user'";
if ($keyword !== "") $query .= " AND (matkul LIKE '%$keyword%' OR tugas LIKE '%$keyword%')";
if ($filter  !== "") $query .= " AND status LIKE '%$filter%'";
$query .= " ORDER BY deadline ASC";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tracker Tugas — <?= htmlspecialchars($nama_user) ?></title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>
<div class="container">

    <!-- ── TOP BAR ── -->
    <div class="top-bar">
        <div class="greeting-block">
            <p class="greeting-name"><?= $sapaan ?>, <?= htmlspecialchars($nama_user) ?>! 👋</p>
            <p class="greeting-sub">Semangat ngerjain tugasnya!</p>
        </div>
        <div class="top-controls">
            <button id="theme-toggle">🌙 Mode Gelap</button>
            <a href="logout.php" onclick="return confirm('Yakin mau logout?')">Logout</a>
        </div>
    </div>

    <!-- ── NOTIFIKASI DEADLINE ── -->
    <?php if ($tugas_hari_ini > 0): ?>
    <div class="notif-deadline">
        <div class="notif-icon">⚠️</div>
        <div class="notif-text">
            <strong>Tugas mendesak hari ini!</strong>
            <p>Kamu punya <strong><?= $tugas_hari_ini ?></strong> tugas yang harus selesai hari ini.</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── JUDUL ── -->
    <h2 class="page-title">📚 Daftar Tugas Kuliah</h2>

    <!-- ── SEARCH ── -->
    <form class="search-form" method="POST">
        <input type="text" name="keyword" placeholder="🔍  Cari mata kuliah atau tugas..." value="<?= htmlspecialchars($keyword) ?>">
        <button type="submit" name="cari">Cari</button>
    </form>

    <!-- ── FILTER ── -->
    <div class="filter-bar">
        <a href="index.php"               class="btn-filter <?= !isset($_GET['filter']) ? 'active' : '' ?>">Semua</a>
        <a href="index.php?filter=Belum"  class="btn-filter <?= (($_GET['filter'] ?? '') === 'Belum')   ? 'active' : '' ?>">🔴 Belum Selesai</a>
        <a href="index.php?filter=Selesai" class="btn-filter <?= (($_GET['filter'] ?? '') === 'Selesai') ? 'active' : '' ?>">🟢 Selesai</a>
    </div>

    <!-- ── TOMBOL TAMBAH ── -->
    <a href="tambah.php" class="btn-tambah">＋ Tambah Tugas Baru</a>

    <!-- ── TABLE ── -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Mata Kuliah</th>
                    <th>Detail Tugas</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $row_count = 0;
            while ($row = mysqli_fetch_assoc($result)):
                $row_count++;

                // Hitung selisih hari — WAJIB dihitung sebelum dipakai
                $ts_deadline = strtotime($row['deadline']);
                $ts_today    = strtotime($today);
                $selisih     = (int)(($ts_deadline - $ts_today) / 86400);

                if ($row['status'] === 'Selesai') {
                    $hint_class = 'deadline-done';
                    $hint_text  = '✓ Tugas tuntas!';
                } elseif ($selisih < 0) {
                    $hint_class = 'deadline-overdue';
                    $hint_text  = 'Terlewat ' . abs($selisih) . ' hari';
                } elseif ($selisih === 0) {
                    $hint_class = 'deadline-today';
                    $hint_text  = '⚡ Hari ini!';
                } else {
                    $hint_class = 'deadline-upcoming';
                    $hint_text  = $selisih . ' hari lagi';
                }
            ?>
            <tr>
                <td><strong><?= htmlspecialchars($row['matkul']) ?></strong></td>
                <td style="white-space:normal; max-width:200px;"><?= htmlspecialchars($row['tugas']) ?></td>
                <td class="deadline-cell <?= ($row['status'] !== 'Selesai' && $selisih <= 0) ? 'deadline-overdue' : '' ?>">
                    <span class="deadline-date"><?= date('d M Y', $ts_deadline) ?></span>
                    <span class="deadline-hint <?= $hint_class ?>"><?= $hint_text ?></span>
                </td>
                <td>
                    <span class="badge <?= $row['status'] === 'Selesai' ? 'selesai' : 'belum' ?>">
                        <?= $row['status'] ?>
                    </span>
                </td>
                <td>
                    <div class="action-group">
                        <?php if ($row['status'] !== 'Selesai'): ?>
                        <a href="selesai.php?id=<?= (int)$row['id'] ?>" class="action-done" title="Tandai Selesai">✅</a>
                        <?php endif; ?>
                        <a href="edit.php?id=<?= (int)$row['id'] ?>" class="action-edit" title="Edit Tugas">✏️</a>
                        <a href="hapus.php?id=<?= (int)$row['id'] ?>" class="action-hapus" title="Hapus" onclick="return confirm('Hapus tugas ini?')">🗑️</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>

            <?php if ($row_count === 0): ?>
            <tr>
                <td colspan="5">
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <p><?= $keyword ? 'Tugas tidak ditemukan.' : 'Belum ada tugas nih.' ?></p>
                        <small><?= $keyword ? 'Coba kata kunci lain.' : 'Klik "+ Tambah Tugas Baru" untuk mulai.' ?></small>
                    </div>
                </td>
            </tr>
            <?php endif; ?>

            </tbody>
        </table>
    </div><!-- /table-container -->

</div><!-- /container -->

<script>
(function () {
    const btn = document.getElementById('theme-toggle');

    function applyTheme() {
        const t = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', t);
        if (btn) btn.innerHTML = t === 'dark' ? '☀️ Mode Terang' : '🌙 Mode Gelap';
    }

    applyTheme();

    btn.addEventListener('click', function () {
        const cur = document.documentElement.getAttribute('data-theme');
        localStorage.setItem('theme', cur === 'dark' ? 'light' : 'dark');
        applyTheme();
    });
})();
</script>
</body>
</html>