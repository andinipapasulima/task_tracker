<?php
include 'koneksi.php';
$id = (int)$_GET['id'];
$query = "UPDATE tugas SET status = 'Selesai' WHERE id = $id";
if (mysqli_query($conn, $query)) {
    header("Location: index.php");
}
?>