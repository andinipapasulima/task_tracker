<?php
include 'koneksi.php';

// 1. Handle recurring tasks (tugas berulang)
$recurring = mysqli_query($conn, "SELECT * FROM tugas WHERE is_recurring = 1 AND status = 'Selesai'");

while ($task = mysqli_fetch_assoc($recurring)) {
    $new_deadline = $task['deadline'];
    
    if ($task['recurring_type'] == 'mingguan') {
        $new_deadline = date('Y-m-d', strtotime($task['deadline'] . ' +7 days'));
    } elseif ($task['recurring_type'] == 'bulanan') {
        $new_deadline = date('Y-m-d', strtotime($task['deadline'] . ' +1 month'));
    }
    
    if ($new_deadline >= date('Y-m-d')) {
        $insert = "INSERT INTO tugas (user_id, matkul, tugas, deadline, status, prioritas, kategori, is_recurring, recurring_type) 
                   VALUES ('{$task['user_id']}', '{$task['matkul']}', '{$task['tugas']}', '$new_deadline', 'Belum', '{$task['prioritas']}', '{$task['kategori']}', 1, '{$task['recurring_type']}')";
        mysqli_query($conn, $insert);
    }
}

// 2. Reminder email (H-1 deadline)
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$tasks_tomorrow = mysqli_query($conn, "SELECT t.*, u.email, u.nama FROM tugas t JOIN users u ON t.user_id = u.id WHERE t.deadline = '$tomorrow' AND t.status != 'Selesai'");

while ($task = mysqli_fetch_assoc($tasks_tomorrow)) {
    // Log reminder (implementasi email pakai PHPMailer)
    $log = "INSERT INTO activity_logs (user_id, action) VALUES ('{$task['user_id']}', 'Reminder email dikirim untuk tugas: {$task['matkul']}')";
    mysqli_query($conn, $log);
    
    // Untuk email, bisa pakai PHPMailer atau mail()
    // mail($task['email'], "Reminder Tugas: {$task['matkul']}", "Halo {$task['nama']}, tugas '{$task['matkul']}' deadline besok!");
}

echo "Cron job selesai dijalankan pada " . date('Y-m-d H:i:s');
?>