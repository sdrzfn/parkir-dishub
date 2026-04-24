<?php
include '../config/db.php';
include '../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_jukir = $_POST['id_jukir'];
    $jumlah = $_POST['jumlah'];
    $termin = $_POST['termin'];
    $tanggal = $_POST['tanggal'];
    $time = strtotime($tanggal);
    $bulan = date('m', $time);
    $tahun = date('Y', $time);

    if (isset($_POST['id_setoran']) && !empty($_POST['id_setoran'])) {
        $id_setoran = mysqli_real_escape_string($conn, $_POST['id_setoran']);
        $sql = "UPDATE transaksi_retribusi SET
                id_jukir='$id_jukir',
                jumlah_setoran = '$jumlah', 
                tanggal_setoran = '$tanggal', 
                termin = '$termin', 
                bulan = '$bulan', 
                tahun = '$tahun' 
                WHERE id = '$id_setoran'";
    } else {
        $sql = "INSERT INTO transaksi_retribusi (id_jukir, jumlah_setoran, tanggal_setoran, metode_pembayaran, termin, bulan, tahun, keterangan) 
                VALUES ('$id_jukir', '$jumlah', '$tanggal', 'tunai', '$termin', '$bulan', '$tahun', 'Input Manual Admin')";
    }

    if (mysqli_query($conn, $sql)) {
        $aksi = isset($_POST['id_setoran']) && !empty($_POST['id_setoran']) ? 'edit' : 'tambah';
        header("Location: ../retribusi-detail.php?id=$id_jukir&status=$aksi");
        exit;
    } else {
        header("Location: ../retribusi-detail.php?id=$id_jukir&status=error&msg=" . urlencode(mysqli_error($conn)));
        exit;
    }
}