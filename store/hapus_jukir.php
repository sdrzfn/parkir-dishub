<?php
include '../config/db.php';
// include 'config/auth.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    $sql = "DELETE FROM jukir_utama WHERE id = '$id'";

    if (mysqli_query($conn, $sql)) {
        echo "Data berhasil dihapus";
        header("Location: ../tukang-parkir.php");
    } else {
        echo "Error saat menghapus: " . mysqli_error($conn);
    }
} else {
    header("Location: ../tukang-parkir.php");
}
?>