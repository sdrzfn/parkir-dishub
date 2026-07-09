<?php
include '../config/db.php';
include '../config/helper.php';
include '../config/auth.php';

$action = $_GET['action'] ?? '';
$type = $_GET['type'] ?? 'utama';

function uploadBerkas($fileInputName, $targetFolder, $allowedExtensions, $prefix)
{
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] != 0) {
        return null;
    }

    $file = $_FILES[$fileInputName];
    $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExtensions)) {
        return false; // Ekstensi tidak diizinkan
    }

    // Pastikan folder tujuan ada
    if (!file_exists($targetFolder)) {
        mkdir($targetFolder, 0755, true);
    }

    $newFileName = $prefix . "_" . time() . "_" . rand(100, 999) . "." . $ext;
    $targetPath = $targetFolder . "/" . $newFileName;

    if (move_uploaded_file($file["tmp_name"], $targetPath)) {
        return $newFileName;
    }

    return null;
}

if ($action == 'add' || $action == 'edit') {
    if ($type === 'pembantu') {
        $id_pembantu = $_POST['id_pembantu'] ?? null;
        $id_utama = mysqli_real_escape_string($conn, $_POST['id_utama']);
        $nik = mysqli_real_escape_string($conn, $_POST['nik_pembantu']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama_pembantu']);
        $alamat = mysqli_real_escape_string($conn, $_POST['alamat_pembantu'] ?? '');
        $no_rekening = mysqli_real_escape_string($conn, $_POST['no_rekening_pembantu']);

        $foto_lama = "";
        $pks_lama = "";
        if ($action == 'edit' && $id_pembantu) {
            $res = mysqli_query($conn, "SELECT foto_id_card, file_pks FROM jukir_pembantu WHERE id = $id_pembantu");
            if ($row = mysqli_fetch_assoc($res)) {
                $foto_lama = $row['foto_id_card'];
                $pks_lama = $row['file_pks'];
            }
        }

        $foto_baru = uploadBerkas('foto_id_card', '../assets/img/jukir/pembantu', ['jpg', 'jpeg', 'png'], 'FOTO_PBT');
        $pks_baru = uploadBerkas('file_pks', '../assets/docs/pks/pembantu', ['pdf'], 'PKS_PBT');

        $final_foto = ($foto_baru !== null) ? $foto_baru : $foto_lama;
        $final_pks = ($pks_baru !== null) ? $pks_baru : $pks_lama;

        if ($action == 'add') {
            $sql = "INSERT INTO jukir_pembantu (id_utama, nik, nama_pembantu, alamat_pembantu, foto_id_card, file_pks, no_rekening) 
                    VALUES ('$id_utama', '$nik', '$nama', '$alamat', '$final_foto', '$final_pks', '$no_rekening')";
            $status = "success_add_pembantu";
        } else {
            $sql = "UPDATE jukir_pembantu SET 
                    id_utama = '$id_utama', nik = '$nik', nama_pembantu = '$nama', alamat_pembantu = '$alamat',
                    foto_id_card = '$final_foto', file_pks = '$final_pks', no_rekening = '$no_rekening'
                    WHERE id = '$id_pembantu'";
            $status = "success_edit_pembantu";
        }

    } else {
        $id = $_POST['id'] ?? null;
        $nik = mysqli_real_escape_string($conn, $_POST['nik']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
        $ttl = mysqli_real_escape_string($conn, $_POST['ttl']);
        $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
        $no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);
        $id_lokasi = mysqli_real_escape_string($conn, $_POST['id_lokasi']);
        $no_rekening = mysqli_real_escape_string($conn, $_POST['no_rekening']);

        $foto_lama = "";
        $pks_lama = "";
        if ($action == 'edit' && $id) {
            $res = mysqli_query($conn, "SELECT foto_id_card, file_pks FROM jukir_utama WHERE id = $id");
            if ($row = mysqli_fetch_assoc($res)) {
                $foto_lama = $row['foto_id_card'];
                $pks_lama = $row['file_pks'];
            }
        }

        $foto_baru = uploadBerkas('foto_id_card', '../assets/img/jukir/utama', ['jpg', 'jpeg', 'png'], 'FOTO_UTM');
        $pks_baru = uploadBerkas('file_pks', '../assets/docs/pks/utama', ['pdf'], 'PKS_UTM');

        $final_foto = ($foto_baru !== null) ? $foto_baru : $foto_lama;
        $final_pks = ($pks_baru !== null) ? $pks_baru : $pks_lama;

        if ($action == 'add') {
            $sql = "INSERT INTO jukir_utama (nik, nama_lengkap, ttl, alamat, no_telp, id_lokasi, foto_id_card, file_pks, no_rekening) 
                    VALUES ('$nik', '$nama', '$ttl', '$alamat', '$no_telp', '$id_lokasi', '$final_foto', '$final_pks', '$no_rekening')";
            $status = "success_add_utama";
        } else {
            $sql = "UPDATE jukir_utama SET 
                    nik = '$nik', nama_lengkap = '$nama', ttl = '$ttl', alamat = '$alamat', 
                    no_telp = '$no_telp', id_lokasi = '$id_lokasi', foto_id_card = '$final_foto', file_pks = '$final_pks',
                    no_rekening = '$no_rekening'
                    WHERE id = '$id'";
            $status = "success_edit_utama";
        }
    }

    if (mysqli_query($conn, $sql)) {
        redirectBack('tukang-parkir.php', ['status' => $status]);
        exit;
    } else {
        redirectBack('tukang-parkir.php', ['status' => 'error', 'msg' => mysqli_error($conn)]);
        exit;
    }
}

if ($action == 'delete') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($type === 'pembantu') {
        $res = mysqli_query($conn, "SELECT foto_id_card, file_pks FROM jukir_pembantu WHERE id = $id");
        if ($r = mysqli_fetch_assoc($res)) {
            @unlink("../assets/img/jukir/pembantu/" . $r['foto_id_card']);
            @unlink("../assets/docs/pks/pembantu/" . $r['file_pks']);
        }
        $sql = "DELETE FROM jukir_pembantu WHERE id = $id";
        $status = "success_delete_pembantu";
    } else {
        $res_pbt = mysqli_query($conn, "SELECT foto_id_card, file_pks FROM jukir_pembantu WHERE id_utama = $id");
        while ($r = mysqli_fetch_assoc($res_pbt)) {
            @unlink("../assets/img/jukir/pembantu/" . $r['foto_id_card']);
            @unlink("../assets/docs/pks/pembantu/" . $r['file_pks']);
        }
        $res_utm = mysqli_query($conn, "SELECT foto_id_card, file_pks FROM jukir_utama WHERE id = $id");
        if ($r = mysqli_fetch_assoc($res_utm)) {
            @unlink("../assets/img/jukir/utama/" . $r['foto_id_card']);
            @unlink("../assets/docs/pks/utama/" . $r['file_pks']);
        }
        mysqli_query($conn, "DELETE FROM jukir_pembantu WHERE id_utama = $id");
        $sql = "DELETE FROM jukir_utama WHERE id = $id";
        $status = "success_delete_utama";
    }

    if (mysqli_query($conn, $sql)) {
        redirectBack('tukang-parkir.php', ['status' => $status]);
        exit;
    } else {
        redirectBack('tukang-parkir.php', ['status' => 'error', 'msg' => mysqli_error($conn)]);
        exit;
    }
}
?>