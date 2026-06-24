<?php
include '../config/db.php';
include '../config/auth.php';
include '../config/helper.php';
checkLogin();
allowRole(['admin']);

$action = $_GET['action'] ?? '';

// ── Add / Edit ────────────────────────────────────────────
if ($action === 'add' || $action === 'edit') {
    $id         = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $wilayah    = mysqli_real_escape_string($conn, trim($_POST['wilayah']   ?? ''));
    $nama_korwil= mysqli_real_escape_string($conn, trim($_POST['nama_korwil'] ?? ''));
    $no_telp    = mysqli_real_escape_string($conn, trim($_POST['no_telp']   ?? ''));
    $email      = mysqli_real_escape_string($conn, trim($_POST['email']     ?? ''));

    if (empty($wilayah) || empty($nama_korwil)) {
        redirectBack('koordinator-wilayah.php', ['status' => 'error', 'msg' => 'Wilayah dan Nama Koordinator wajib diisi.']);
        exit;
    }

    if ($action === 'add') {
        $sql = "INSERT INTO koordinator_wilayah (wilayah, nama_korwil, no_telp, email)
                VALUES ('$wilayah', '$nama_korwil', '$no_telp', '$email')";
        $status = 'success_add';
    } else {
        $sql = "UPDATE koordinator_wilayah
                SET wilayah='$wilayah', nama_korwil='$nama_korwil', no_telp='$no_telp', email='$email'
                WHERE id=$id";
        $status = 'success_edit';
    }

    if (mysqli_query($conn, $sql)) {
        redirectBack('koordinator-wilayah.php', ['status' => $status]);
    } else {
        redirectBack('koordinator-wilayah.php', ['status' => 'error', 'msg' => mysqli_error($conn)]);
    }
    exit;
}

// ── Delete ───────────────────────────────────────────────
if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
        redirectBack('koordinator-wilayah.php', ['status' => 'error', 'msg' => 'ID tidak valid.']);
        exit;
    }

    // Check if any jukir is assigned to this korwil
    $cek = mysqli_query($conn, "SELECT COUNT(*) AS total FROM jukir_utama WHERE id_korwil = $id");
    $row = mysqli_fetch_assoc($cek);
    if ($row['total'] > 0) {
        redirectBack('koordinator-wilayah.php', ['status' => 'error', 'msg' => 'Tidak dapat menghapus — masih ada ' . $row['total'] . ' petugas parkir yang terdaftar di wilayah ini.']);
        exit;
    }

    if (mysqli_query($conn, "DELETE FROM koordinator_wilayah WHERE id = $id")) {
        redirectBack('koordinator-wilayah.php', ['status' => 'success_delete']);
    } else {
        redirectBack('koordinator-wilayah.php', ['status' => 'error', 'msg' => mysqli_error($conn)]);
    }
    exit;
}

redirectBack('koordinator-wilayah.php', ['status' => 'error', 'msg' => 'Aksi tidak dikenali.']);
exit;
