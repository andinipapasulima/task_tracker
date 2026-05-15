<?php
include 'koneksi.php';
$id = (int)$_GET['id'];
$query = "DELETE FROM tugas WHERE id = $id";
if (mysqli_query($conn, $query)) {
    header("Location: index.php");
}
?>