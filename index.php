<?php
session_start();
// Jalankan cron setiap kali user login (max 1x sehari)
$last_cron = isset($_COOKIE['last_cron']) ? $_COOKIE['last_cron'] : '';
if ($last_cron != date('Y-m-d')) {
    include 'cron.php';
    setcookie('last_cron', date('Y-m-d'), time() + 86400);
}
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

$today = date('Y-m-d');

// Notifikasi deadline hari ini
$sql_notif   = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tugas WHERE user_id='$id_user' AND deadline='$today' AND status!='Selesai'");
$data_notif  = mysqli_fetch_assoc($sql_notif);
$tugas_hari_ini = (int)$data_notif['total'];

// Hitung progress
$total_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tugas WHERE user_id='$id_user'");
$total_data = mysqli_fetch_assoc($total_query);
$total_tugas = (int)$total_data['total'];

$selesai_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tugas WHERE user_id='$id_user' AND status='Selesai'");
$selesai_data = mysqli_fetch_assoc($selesai_query);
$selesai_count = (int)$selesai_data['total'];
$progress = $total_tugas > 0 ? round(($selesai_count / $total_tugas) * 100) : 0;

// Search & Filter
$keyword = isset($_POST['keyword']) ? mysqli_real_escape_string($conn, trim($_POST['keyword'])) : "";
$filter  = isset($_GET['filter'])   ? mysqli_real_escape_string($conn, $_GET['filter']) : "";
$filter_deadline = isset($_GET['filter_deadline']) ? $_GET['filter_deadline'] : "";
$sort    = isset($_GET['sort']) ? $_GET['sort'] : "deadline_asc";
$prioritas_filter = isset($_GET['prioritas']) ? $_GET['prioritas'] : "";

$query = "SELECT * FROM tugas WHERE user_id='$id_user'";

if ($keyword !== "") $query .= " AND (matkul LIKE '%$keyword%' OR tugas LIKE '%$keyword%')";
if ($filter  !== "") $query .= " AND status LIKE '%$filter%'";
if ($prioritas_filter !== "") $query .= " AND prioritas = '$prioritas_filter'";

// Filter deadline
if ($filter_deadline == "minggu_ini") {
    $start_week = date('Y-m-d', strtotime('monday this week'));
    $end_week = date('Y-m-d', strtotime('sunday this week'));
    $query .= " AND deadline BETWEEN '$start_week' AND '$end_week'";
} elseif ($filter_deadline == "bulan_ini") {
    $start_month = date('Y-m-01');
    $end_month = date('Y-m-t');
    $query .= " AND deadline BETWEEN '$start_month' AND '$end_month'";
} elseif ($filter_deadline == "overdue") {
    $query .= " AND deadline < '$today' AND status != 'Selesai'";
}

// Sorting
switch($sort) {
    case 'matkul_asc': $query .= " ORDER BY matkul ASC"; break;
    case 'matkul_desc': $query .= " ORDER BY matkul DESC"; break;
    case 'deadline_asc': $query .= " ORDER BY deadline ASC"; break;
    case 'deadline_desc': $query .= " ORDER BY deadline DESC"; break;
    case 'status_asc': $query .= " ORDER BY status ASC"; break;
    default: $query .= " ORDER BY deadline ASC";
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_query = str_replace("SELECT *", "SELECT COUNT(*) as total", $query);
$count_result = mysqli_query($conn, $count_query);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

$query .= " LIMIT $limit OFFSET $offset";
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
    <style>
        /* Style untuk compact filter */
.btn-filter-compact {
    transition: all 0.2s ease;
    color: var(--text-muted);
}

.btn-filter-compact:hover {
    background: var(--accent-light);
    color: var(--accent);
}

.btn-filter-compact.active {
    background: var(--accent);
    color: white;
}

.filter-select {
    font-family: 'Outfit', sans-serif;
    font-weight: 600;
    background: var(--bg-surface);
    color: var(--text-main);
    border: 1.5px solid var(--border);
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-select:hover {
    border-color: var(--accent);
    color: var(--accent);
}

.btn-reset {
    transition: all 0.2s;
}

.btn-reset:hover {
    border-color: var(--accent);
    color: var(--accent);
    transform: translateY(-1px);
}
        .sort-link {
            text-decoration: none;
            color: var(--text-sub);
            font-weight: 600;
        }
        .sort-link:hover {
            color: var(--accent);
        }
        .sort-active {
            color: var(--accent);
        }
        .prioritas-badge {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .pagination a {
            padding: 8px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: var(--text-sub);
        }
        .pagination a.active {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }
        .filter-select {
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--bg-surface);
            color: var(--text-main);
        }
    </style>
</head>
<body>
<div class="container">

    <div class="top-bar">
        <div class="greeting-block">
            <p class="greeting-name"><?= $sapaan ?>, <?= htmlspecialchars($nama_user) ?>! 👋</p>
            <p class="greeting-sub">Semangat ngerjain tugasnya!</p>
        </div>
        <div class="top-controls">
    <button id="theme-toggle">🌙 Mode Gelap</button>
    <a href="edit_profile.php" class="btn-profile">👤 Profile</a>
    <a href="dashboard.php" class="btn-dashboard">📊 Dashboard</a>
    <a href="logout.php" onclick="return confirm('Yakin mau logout?')" class="btn-logout">🚪 Logout</a>
</div>
    </div>

    <!-- Progress Bar -->
    <div style="background: var(--bg-surface-alt); padding: 16px; border-radius: var(--radius-md); margin-bottom: 24px; border: 1px solid var(--border);">
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
            <strong>📊 Progress Tugas</strong>
            <span><?= $progress ?>% (<?= $selesai_count ?>/<?= $total_tugas ?> selesai)</span>
        </div>
        <div style="background: var(--border); border-radius: 10px; height: 10px; overflow: hidden;">
            <div style="width: <?= $progress ?>%; background: var(--success); height: 100%; transition: width 0.3s;"></div>
        </div>
    </div>

    <!-- Notifikasi Deadline -->
    <?php if ($tugas_hari_ini > 0): ?>
    <div class="notif-deadline">
        <div class="notif-icon">⚠️</div>
        <div class="notif-text">
            <strong>Tugas mendesak hari ini!</strong>
            <p>Kamu punya <strong><?= $tugas_hari_ini ?></strong> tugas yang harus selesai hari ini.</p>
        </div>
    </div>
    <?php endif; ?>

    <h2 class="page-title">📚 Daftar Tugas Kuliah</h2>

    <!-- Search -->
    <form class="search-form" method="POST">
        <input type="text" name="keyword" placeholder="🔍 Cari mata kuliah atau tugas..." value="<?= htmlspecialchars($keyword) ?>">
        <button type="submit" name="cari">Cari</button>
    </form>

    <!-- Filter Bar COMPACT (Gabung jadi satu) -->
<div class="filter-bar" style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 20px;">
    <!-- Status Filter (Semua/Belum/Selesai) -->
    <div style="display: flex; gap: 4px; background: var(--bg-surface); border: 1.5px solid var(--border); border-radius: 50px; padding: 3px;">
        <a href="index.php" class="btn-filter-compact <?= !isset($_GET['filter']) && !isset($_GET['filter_deadline']) && !isset($_GET['prioritas']) ? 'active' : '' ?>" style="padding: 6px 14px; border-radius: 50px; text-decoration: none; font-size: 12px; font-weight: 600;">Semua</a>
        <a href="index.php?filter=Belum" class="btn-filter-compact <?= (($_GET['filter'] ?? '') === 'Belum') ? 'active' : '' ?>" style="padding: 6px 14px; border-radius: 50px; text-decoration: none; font-size: 12px; font-weight: 600;">🔴 Belum</a>
        <a href="index.php?filter=Selesai" class="btn-filter-compact <?= (($_GET['filter'] ?? '') === 'Selesai') ? 'active' : '' ?>" style="padding: 6px 14px; border-radius: 50px; text-decoration: none; font-size: 12px; font-weight: 600;">🟢 Selesai</a>
    </div>
    
    <!-- Dropdown gabungan (Filter Deadline + Prioritas + Sorting) -->
    <select class="filter-select" id="quickFilter" style="padding: 7px 14px; font-size: 12px; min-width: 140px;">
        <option value="">📋 Filter & Sortir</option>
        <optgroup label="📅 Filter Deadline">
    <option value="index.php?filter_deadline=minggu_ini" <?= ($filter_deadline == 'minggu_ini') ? 'selected' : '' ?>>Minggu ini</option>
    <option value="index.php?filter_deadline=bulan_ini" <?= ($filter_deadline == 'bulan_ini') ? 'selected' : '' ?>>Bulan ini</option>
    <option value="index.php?filter_deadline=overdue" <?= ($filter_deadline == 'overdue') ? 'selected' : '' ?>>Terlewat</option>
</optgroup>
<optgroup label="⚡ Filter Prioritas">
    <option value="index.php?prioritas=Tinggi" <?= ($prioritas_filter == 'Tinggi') ? 'selected' : '' ?>>🔥 Tinggi</option>
    <option value="index.php?prioritas=Sedang" <?= ($prioritas_filter == 'Sedang') ? 'selected' : '' ?>>⚡ Sedang</option>
    <option value="index.php?prioritas=Rendah" <?= ($prioritas_filter == 'Rendah') ? 'selected' : '' ?>>✅ Rendah</option>
</optgroup>
<optgroup label="📌 Sorting">
    <option value="index.php?sort=matkul_asc" <?= ($sort == 'matkul_asc') ? 'selected' : '' ?>>Mata Kuliah (A-Z)</option>
    <option value="index.php?sort=matkul_desc" <?= ($sort == 'matkul_desc') ? 'selected' : '' ?>>Mata Kuliah (Z-A)</option>
    <option value="index.php?sort=deadline_asc" <?= ($sort == 'deadline_asc') ? 'selected' : '' ?>>Deadline (Terdekat)</option>
    <option value="index.php?sort=deadline_desc" <?= ($sort == 'deadline_desc') ? 'selected' : '' ?>>Deadline (Terjauh)</option>
</optgroup>
    </select>
    
    <!-- Reset Filter Button -->
    <a href="index.php" class="btn-reset" style="padding: 7px 14px; border-radius: 50px; text-decoration: none; font-size: 12px; font-weight: 600; background: var(--bg-surface); border: 1.5px solid var(--border); color: var(--text-muted);">
        ↺ Reset
    </a>
</div>

<script>
// Auto redirect saat dropdown berubah
document.getElementById('quickFilter').addEventListener('change', function() {
    if (this.value) {
        window.location.href = this.value;
    }
});
</script>

    <!-- Tombol Tambah + Export -->
    <div style="display: flex; gap: 12px; margin-bottom: 20px;">
        <a href="tambah.php" class="btn-tambah" style="flex: 2;">＋ Tambah Tugas Baru</a>
        <a href="export.php" class="btn-tambah" style="flex: 1; background: var(--accent-light); color: var(--accent);">📎 Export</a>
    </div>

    <!-- Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th><a href="?sort=matkul_asc&<?= http_build_query(array_diff_key($_GET, ['sort'=>1])) ?>" class="sort-link">Mata Kuliah</a></th>
                    <th>Detail Tugas</th>
                    <th><a href="?sort=deadline_asc&<?= http_build_query(array_diff_key($_GET, ['sort'=>1])) ?>" class="sort-link">Deadline</a></th>
                    <th>Status</th>
                    <th>Prioritas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $row_count = 0;
            while ($row = mysqli_fetch_assoc($result)):
                $row_count++;
                $ts_deadline = strtotime($row['deadline']);
                $ts_today    = strtotime($today);
                $selisih     = (int)(($ts_deadline - $ts_today) / 86400);
                
                $warna_prioritas = [
                    'Tinggi' => '#EF4444',
                    'Sedang' => '#F59E0B',
                    'Rendah' => '#10B981'
                ];
                $prioritas_color = $warna_prioritas[$row['prioritas']] ?? '#6366F1';

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
                <td><strong><?= htmlspecialchars($row['matkul']) ?></strong><br>
                    <small style="color: var(--text-muted);"><?= htmlspecialchars($row['kategori']) ?></small>
                </td>
                <td style="white-space:normal; max-width:200px;">
                    <?= htmlspecialchars($row['tugas']) ?>
                    <?php if ($row['catatan']): ?>
                        <br><small style="color: var(--text-muted);">📝 <?= htmlspecialchars(substr($row['catatan'], 0, 50)) ?></small>
                    <?php endif; ?>
                </td>
                <td class="deadline-cell">
                    <span class="deadline-date"><?= date('d M Y', $ts_deadline) ?></span>
                    <span class="deadline-hint <?= $hint_class ?>"><?= $hint_text ?></span>
                </td>
                <td>
                    <span class="badge <?= $row['status'] === 'Selesai' ? 'selesai' : 'belum' ?>">
                        <?= $row['status'] ?>
                    </span>
                </td>
                <td>
                    <span class="prioritas-badge" style="background: <?= $prioritas_color ?>;"></span>
                    <?= $row['prioritas'] ?>
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
                <td colspan="6">
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
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>&<?= http_build_query(array_diff_key($_GET, ['page'=>1])) ?>">« Prev</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>&<?= http_build_query(array_diff_key($_GET, ['page'=>1])) ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page+1 ?>&<?= http_build_query(array_diff_key($_GET, ['page'=>1])) ?>">Next »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<script>
(function () {
    const btn = document.getElementById('theme-toggle');
    function applyTheme() {
        const t = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', t);
        if (btn) btn.innerHTML = t === 'dark' ? '☀️ Mode Terang' : '🌙 Mode Gelap';
    }
    applyTheme();
    if (btn) {
        btn.addEventListener('click', function () {
            const cur = document.documentElement.getAttribute('data-theme');
            localStorage.setItem('theme', cur === 'dark' ? 'light' : 'dark');
            applyTheme();
        });
    }

    <?php if ($tugas_hari_ini > 0): ?>
    if ('Notification' in window && Notification.permission !== 'denied') {
        Notification.requestPermission().then(function(perm) {
            if (perm === 'granted') {
                new Notification('⚠️ Deadline Hari Ini!', {
                    body: 'Kamu punya <?= $tugas_hari_ini ?> tugas yang harus diselesaikan hari ini.',
                    icon: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23D97706"%3E%3Cpath d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/%3E%3C/svg%3E'
                });
            }
        });
    }
    <?php endif; ?>
})();
</script>
</body>
</html>