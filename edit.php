<?php
include 'koneksi.php';

$id = $_GET['id'];
$query = "SELECT * FROM tugas WHERE id = $id";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

if (isset($_POST['update'])) {
    $matkul = $_POST['matkul'];
    $tugas = $_POST['tugas'];
    $deadline = $_POST['deadline'];

    $updateQuery = "UPDATE tugas SET matkul='$matkul', tugas='$tugas', deadline='$deadline' WHERE id=$id";
    
    if (mysqli_query($conn, $updateQuery)) {
        header("Location: index.php");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Edit Tugas</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>
<div class="container">
    <a href="index.php" class="btn-kembali"><span>←</span> Batal</a>
    <h2>Edit Tugas</h2>

    <form method="POST">
        <label>Mata Kuliah</label>
        <input type="text" name="matkul" value="<?= $row['matkul']; ?>" required>

        <label>Detail Tugas</label>
        <textarea name="tugas" rows="4" required><?= $row['tugas']; ?></textarea>

        <label>Deadline</label>
        <input type="date" name="deadline" value="<?= $row['deadline']; ?>" required>

        <button type="submit" name="update">Simpan Perubahan</button>
    </form>
</div>
</body>
</html>