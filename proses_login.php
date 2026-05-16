<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query  = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {
            $_SESSION['login']    = true;
            $_SESSION['id_user']  = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['nama']     = $row['nama'];

            header("Location: index.php");
            exit;
        }
    }

    header("Location: login.php?error=1");
    exit;
}

header("Location: login.php");
exit;
?>