<?php
include '../config/db.php';
include '../config/auth.php';
include '../config/helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_jukir = mysqli_real_escape_string($conn, $_POST['id_jukir']);
    $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
    $termin = mysqli_real_escape_string($conn, $_POST['termin']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $time = strtotime($tanggal);
    $bulan = date('m', $time);
    $tahun = date('Y', $time);

    if (empty($id_jukir) && isset($_POST['id_setoran']) && !empty($_POST['id_setoran'])) {
        $id_setoran_check = mysqli_real_escape_string($conn, $_POST['id_setoran']);
        $q_check = mysqli_query($conn, "SELECT id_jukir FROM transaksi_retribusi WHERE id = '$id_setoran_check'");
        if ($r_check = mysqli_fetch_assoc($q_check)) {
            $id_jukir = $r_check['id_jukir'];
        }
    }

    if (empty($id_jukir)) {
        redirectBack('retribusi-detail.php', ['id' => $id_jukir, 'status' => 'error', 'msg' => mysqli_error($conn)]);
        exit;
    }

    if (isset($_POST['id_setoran']) && !empty($_POST['id_setoran'])) {
        $id_setoran = mysqli_real_escape_string($conn, $_POST['id_setoran']);
        $sql = "UPDATE transaksi_retribusi SET
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
    
    $aksi = isset($_POST['id_setoran']) && !empty($_POST['id_setoran']) ? 'edit' : 'tambah';
    if (mysqli_query($conn, $sql)) {
        redirectBack('retribusi-detail.php', ['id' => $id_jukir, 'status' => $aksi]);
        exit;
    } else {
        redirectBack('retribusi-detail.php', ['id' => $id_jukir, 'status' => 'error', 'msg' => mysqli_error($conn)]);
        exit;
    }
}