<?php
include '../config/db.php';

$action = $_GET['action'] ?? '';

// LOGIKA TAMBAH DAN EDIT DATA
if ($action == 'add' || $action == 'edit') {
    $id = $_POST['id'] ?? null;
    $kode_qris = mysqli_real_escape_string($conn, $_POST['kode_qris']);
    $nama_lokasi = mysqli_real_escape_string($conn, $_POST['nama_lokasi']);
    $titik_parkir = mysqli_real_escape_string($conn, $_POST['titik_parkir']);
    $nominal_retribusi = (float) $_POST['nominal_retribusi'];
    $target_bulanan = (float) $_POST['target_bulanan'];
    $terbilang_target = mysqli_real_escape_string($conn, $_POST['terbilang_target']);
    $latitude = $_POST['latitude'] !== "" ? "'" . mysqli_real_escape_string($conn, $_POST['latitude']) . "'" : "NULL";
    $longitude = $_POST['longitude'] !== "" ? "'" . mysqli_real_escape_string($conn, $_POST['longitude']) . "'" : "NULL";

    // Logika Upload Foto
    $nama_file_foto = "";
    if ($action == 'edit') {
        $res = mysqli_query($conn, "SELECT foto FROM lokasi WHERE id = $id");
        $row = mysqli_fetch_assoc($res);
        $nama_file_foto = $row['foto'];
    }

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "../assets/images/lokasi/";
        $file_ext = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
        $new_name = "LOK_" . time() . "." . $file_ext;
        $target_file = $target_dir . $new_name;

        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            if ($action == 'edit' && !empty($nama_file_foto) && file_exists($target_dir . $nama_file_foto)) {
                unlink($target_dir . $nama_file_foto);
            }
            $nama_file_foto = $new_name;
        }
    }

    if ($action == 'add') {
        $sql = "INSERT INTO lokasi (kode_qris, nama_lokasi, titik_parkir, nominal_retribusi, target_bulanan, terbilang_target, foto, latitude, longitude) 
                VALUES ('$kode_qris', '$nama_lokasi', '$titik_parkir', '$nominal_retribusi', '$target_bulanan', '$terbilang_target', '$nama_file_foto', $latitude, $longitude)";
    } else {
        $sql = "UPDATE lokasi SET 
                kode_qris='$kode_qris', nama_lokasi='$nama_lokasi', titik_parkir='$titik_parkir', 
                nominal_retribusi='$nominal_retribusi', target_bulanan='$target_bulanan', 
                terbilang_target='$terbilang_target', foto='$nama_file_foto', latitude=$latitude, 
                longitude=$longitude 
                WHERE id=$id";
    }

    if (mysqli_query($conn, $sql)) {
        echo "Data berhasil diubah";
        header("Location: ../lokasi.php");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Logika Hapus Data
if ($action == 'delete') {
    $id = $_GET['id'];

    $get_foto = mysqli_query($conn, "SELECT foto FROM lokasi WHERE id=$id");
    $data = mysqli_fetch_assoc($get_foto);

    if (!empty($data['foto'])) {
        $path = "../assets/images/lokasi/" . $data['foto'];
        if (file_exists($path)) {
            unlink($path);
        }
    }

    $sql = "DELETE FROM lokasi WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        $message = "Data berhasil dihapus!";
        echo "<script>alert('$message');</script>";

        header("Location: ../lokasi.php");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>