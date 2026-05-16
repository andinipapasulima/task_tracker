<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$nama_user = $_SESSION['nama'];

// Ambil semua tugas user
$query = "SELECT * FROM tugas WHERE user_id='$id_user' ORDER BY deadline ASC";
$result = mysqli_query($conn, $query);

// Hitung progress
$total = mysqli_num_rows($result);
$selesai_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tugas WHERE user_id='$id_user' AND status='Selesai'");
$selesai_data = mysqli_fetch_assoc($selesai_query);
$selesai_count = (int)$selesai_data['total'];
$progress = $total > 0 ? round(($selesai_count / $total) * 100) : 0;

// Set header untuk download file CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="tugas_' . date('Y-m-d') . '.csv"');

// Buat output
$output = fopen('php://output', 'w');

// Tulis header CSV
fputcsv($output, ['===== TUGAS TRACKER =====', '', '', '']);
fputcsv($output, ['User:', $nama_user, date('d M Y H:i'), '']);
fputcsv($output, ['Progress:', $progress . '% (' . $selesai_count . '/' . $total . ' selesai)', '', '']);
fputcsv($output, ['', '', '', '']);
fputcsv($output, ['No', 'Mata Kuliah', 'Detail Tugas', 'Deadline', 'Status', 'Keterangan']);

// Tulis data
$no = 1;
mysqli_data_seek($result, 0); // Reset pointer hasil query
while ($row = mysqli_fetch_assoc($result)) {
    $today = date('Y-m-d');
    $ts_deadline = strtotime($row['deadline']);
    $ts_today = strtotime($today);
    $selisih = (int)(($ts_deadline - $ts_today) / 86400);
    
    if ($row['status'] === 'Selesai') {
        $keterangan = 'Selesai';
    } elseif ($selisih < 0) {
        $keterangan = 'TERLEWAT ' . abs($selisih) . ' hari';
    } elseif ($selisih === 0) {
        $keterangan = 'DEADLINE HARI INI!';
    } else {
        $keterangan = $selisih . ' hari lagi';
    }
    
    fputcsv($output, [
        $no,
        $row['matkul'],
        $row['tugas'],
        date('d M Y', strtotime($row['deadline'])),
        $row['status'],
        $keterangan
    ]);
    $no++;
}

fputcsv($output, ['', '', '', '', '', '']);
fputcsv($output, ['===== SELESAI =====', '', '', '', '', '']);

fclose($output);
exit;
?>