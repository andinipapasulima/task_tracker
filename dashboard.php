<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Statistik dasar
$total_tugas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tugas WHERE user_id='$id_user'"))['total'];
$tugas_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tugas WHERE user_id='$id_user' AND status='Selesai'"))['total'];
$tugas_belum = $total_tugas - $tugas_selesai;
$progress = $total_tugas > 0 ? round(($tugas_selesai / $total_tugas) * 100) : 0;

// Tugas per prioritas
$prioritas_data = [];
$prioritas_query = mysqli_query($conn, "SELECT prioritas, COUNT(*) as total FROM tugas WHERE user_id='$id_user' GROUP BY prioritas");
while ($row = mysqli_fetch_assoc($prioritas_query)) {
    $prioritas_data[$row['prioritas']] = $row['total'];
}

// Tugas per kategori
$kategori_data = [];
$kategori_query = mysqli_query($conn, "SELECT kategori, COUNT(*) as total FROM tugas WHERE user_id='$id_user' GROUP BY kategori");
while ($row = mysqli_fetch_assoc($kategori_query)) {
    $kategori_data[$row['kategori']] = $row['total'];
}

// Tugas deadline minggu ini
$start_of_week = date('Y-m-d', strtotime('monday this week'));
$end_of_week = date('Y-m-d', strtotime('sunday this week'));
$tugas_minggu_ini = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tugas WHERE user_id='$id_user' AND deadline BETWEEN '$start_of_week' AND '$end_of_week' AND status != 'Selesai'"))['total'];

// Tugas overdue
$tugas_overdue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tugas WHERE user_id='$id_user' AND deadline < CURDATE() AND status != 'Selesai'"))['total'];
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Tracker Tugas</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 20px;
            text-align: center;
        }
        .stat-number {
            font-size: 36px;
            font-weight: 800;
            color: var(--accent);
        }
        .stat-label {
            color: var(--text-muted);
            margin-top: 8px;
        }
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        .chart-container {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 20px;
        }
        .chart-container h3 {
            margin-bottom: 20px;
        }
        .warning-badge {
            background: var(--warning-light);
            color: var(--warning);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        .danger-badge {
            background: var(--danger-light);
            color: var(--danger);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="index.php" class="btn-kembali">← Kembali</a>
    <h2 class="page-title">📊 Dashboard Statistik</h2>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $total_tugas ?></div>
            <div class="stat-label">Total Tugas</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: var(--success);"><?= $tugas_selesai ?></div>
            <div class="stat-label">Selesai</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: var(--danger);"><?= $tugas_belum ?></div>
            <div class="stat-label">Belum Selesai</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $progress ?>%</div>
            <div class="stat-label">Progress</div>
            <div style="background: var(--border); height: 6px; border-radius: 3px; margin-top: 10px;">
                <div style="width: <?= $progress ?>%; background: var(--success); height: 6px; border-radius: 3px;"></div>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number <?= $tugas_minggu_ini > 0 ? 'warning-badge' : '' ?>" style="font-size: 28px;"><?= $tugas_minggu_ini ?></div>
            <div class="stat-label">Tugas Minggu Ini</div>
        </div>
        <div class="stat-card">
            <div class="stat-number <?= $tugas_overdue > 0 ? 'danger-badge' : '' ?>" style="font-size: 28px;"><?= $tugas_overdue ?></div>
            <div class="stat-label">Tugas Terlewat</div>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-container">
            <h3>📈 Progress Tugas</h3>
            <canvas id="progressChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>⚠️ Prioritas Tugas</h3>
            <canvas id="prioritasChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>📁 Kategori Tugas</h3>
            <canvas id="kategoriChart"></canvas>
        </div>
    </div>
</div>

<script>
// Progress Chart
new Chart(document.getElementById('progressChart'), {
    type: 'doughnut',
    data: {
        labels: ['Selesai (<?= $tugas_selesai ?>)', 'Belum Selesai (<?= $tugas_belum ?>)'],
        datasets: [{
            data: [<?= $tugas_selesai ?>, <?= $tugas_belum ?>],
            backgroundColor: ['#10B981', '#EF4444'],
            borderWidth: 0
        }]
    }
});

// Prioritas Chart
new Chart(document.getElementById('prioritasChart'), {
    type: 'bar',
    data: {
        labels: ['Tinggi', 'Sedang', 'Rendah'],
        datasets: [{
            label: 'Jumlah Tugas',
            data: [<?= $prioritas_data['Tinggi'] ?? 0 ?>, <?= $prioritas_data['Sedang'] ?? 0 ?>, <?= $prioritas_data['Rendah'] ?? 0 ?>],
            backgroundColor: ['#EF4444', '#F59E0B', '#10B981'],
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// Kategori Chart
new Chart(document.getElementById('kategoriChart'), {
    type: 'pie',
    data: {
        labels: <?php 
            $labels = array_keys($kategori_data);
            $values = array_values($kategori_data);
            echo json_encode($labels);
        ?>,
        datasets: [{
            data: <?= json_encode($values) ?>,
            backgroundColor: ['#6366F1', '#8B5CF6', '#EC4899', '#F59E0B', '#10B981', '#3B82F6', '#EF4444']
        }]
    }
});
</script>
</body>
</html>