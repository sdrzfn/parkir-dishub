<?php
include '../config/db.php';
include '../config/auth.php';
include '../config/helper.php';

checkLogin();
allowRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectBack('retribusi-detail.php', ['status' => 'error', 'msg' => 'Metode tidak diizinkan.']);
    exit;
}

$id_jukir = mysqli_real_escape_string($conn, $_POST['id_jukir'] ?? '');
$jenis_surat = mysqli_real_escape_string($conn, $_POST['jenis_surat'] ?? '');
$keterangan = mysqli_real_escape_string($conn, $_POST['keterangan'] ?? '');
$admin = $_SESSION['nama'] ?? 'Admin';

// Validasi input
if (empty($id_jukir) || empty($jenis_surat)) {
    redirectBack('retribusi-detail.php', ['id' => $id_jukir, 'status' => 'error', 'msg' => 'Data tidak lengkap.']);
    exit;
}

// Validasi jenis surat
$allowed_jenis = ['tagihan', 'sp1', 'sp2', 'sp3'];
if (!in_array($jenis_surat, $allowed_jenis)) {
    redirectBack('retribusi-detail.php', ['id' => $id_jukir, 'status' => 'error', 'msg' => 'Jenis surat tidak valid.']);
    exit;
}

// Cek apakah ada file yang diupload
$file_sp = $_FILES['file_sp'] ?? null;
$file_tagihan = $_FILES['file_tagihan'] ?? null;

if (!$file_sp && !$file_tagihan) {
    redirectBack('retribusi-detail.php', ['id' => $id_jukir, 'status' => 'error', 'msg' => 'Pilih minimal satu file untuk diupload.']);
    exit;
}

$upload_dir = '../uploads/surat/';
$file_sp_name = null;
$file_tagihan_name = null;

// Upload file SP jika ada
if ($file_sp && $file_sp['error'] === 0) {
    $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file_sp['type'], $allowed_types)) {
        redirectBack('retribusi-detail.php', ['id' => $id_jukir, 'status' => 'error', 'msg' => 'Tipe file SP tidak didukung. Gunakan PDF atau DOC.']);
        exit;
    }
    
    if ($file_sp['size'] > $max_size) {
        redirectBack('retribusi-detail.php', ['id' => $id_jukir, 'status' => 'error', 'msg' => 'Ukuran file SP terlalu besar. Maksimal 5MB.']);
        exit;
    }
    
    $ext = pathinfo($file_sp['name'], PATHINFO_EXTENSION);
    $file_sp_name = 'SP-' . $id_jukir . '-' . time() . '.' . $ext;
    
    if (!move_uploaded_file($file_sp['tmp_name'], $upload_dir . $file_sp_name)) {
        redirectBack('retribusi-detail.php', ['id' => $id_jukir, 'status' => 'error', 'msg' => 'Gagal upload file SP.']);
        exit;
    }
}

// Upload file tagihan jika ada
if ($file_tagihan && $file_tagihan['error'] === 0) {
    $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file_tagihan['type'], $allowed_types)) {
        redirectBack('retribusi-detail.php', ['id' => $id_jukir, 'status' => 'error', 'msg' => 'Tipe file Tagihan tidak didukung. Gunakan PDF atau DOC.']);
        exit;
    }
    
    if ($file_tagihan['size'] > $max_size) {
        redirectBack('retribusi-detail.php', ['id' => $id_jukir, 'status' => 'error', 'msg' => 'Ukuran file Tagihan terlalu besar. Maksimal 5MB.']);
        exit;
    }
    
    $ext = pathinfo($file_tagihan['name'], PATHINFO_EXTENSION);
    $file_tagihan_name = 'TAGIHAN-' . $id_jukir . '-' . time() . '.' . $ext;
    
    if (!move_uploaded_file($file_tagihan['tmp_name'], $upload_dir . $file_tagihan_name)) {
        redirectBack('retribusi-detail.php', ['id' => $id_jukir, 'status' => 'error', 'msg' => 'Gagal upload file Tagihan.']);
        exit;
    }
}

// Insert ke database
$query = "INSERT INTO log_aksi_jukir (id_jukir, jenis_surat, file_sp, file_tagihan, admin_input, keterangan) 
          VALUES ('$id_jukir', '$jenis_surat', " . ($file_sp_name ? "'$file_sp_name'" : "NULL") . ", " . ($file_tagihan_name ? "'$file_tagihan_name'" : "NULL") . ", '$admin', '$keterangan')";

if (mysqli_query($conn, $query)) {
    redirectBack('retribusi-detail.php', ['id' => $id_jukir, 'status' => 'success_upload']);
} else {
    redirectBack('retribusi-detail.php', ['id' => $id_jukir, 'status' => 'error', 'msg' => mysqli_error($conn)]);
}