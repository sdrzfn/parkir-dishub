<?php
include '../config/db.php';
// include 'config/auth.php';

$action = $_GET['action'] ?? '';

if ($action == 'add' || $action == 'edit') {
    $id = $_POST['id'] ?? null;
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $ttl = mysqli_real_escape_string($conn, $_POST['ttl']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);
    $id_lokasi = mysqli_real_escape_string($conn, $_POST['id_lokasi']);

    if ($action == 'add') {
        $sql = "INSERT INTO jukir_utama (nik, nama_lengkap, ttl, alamat, no_telp, id_lokasi) 
                VALUES ('$nik', '$nama', '$ttl', '$alamat', '$no_telp', '$id_lokasi')";
        $status = "tambah_berhasil";
    } else {
        $sql = "UPDATE jukir_utama SET 
                nik = '$nik', 
                nama_lengkap = '$nama', 
                ttl = '$ttl', 
                alamat = '$alamat', 
                no_telp = '$no_telp', 
                id_lokasi = '$id_lokasi' 
                WHERE id = '$id'";
        $status = "update_berhasil";
    }

    if (mysqli_query($conn, $sql)) {
        $msg = "Data berhasil diubah";
        echo "<script type='text/javascript'>alert('$msg');</script>";
        // header("Location: tukang-parkir.php?status=$status");
        header("Location: ../tukang-parkir.php");

    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>