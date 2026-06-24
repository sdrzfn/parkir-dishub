<?php
include '../config/db.php';
include '../config/auth.php';
include '../config/helper.php';
checkLogin();

$id_setoran = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id_setoran <= 0) {
    redirectBack('retribusi-parkir.php', ['status' => 'error', 'msg' => 'ID tidak valid.']);
    exit;
}

// Fetch id_jukir before deleting so we can redirect back to the detail page
$q = mysqli_query($conn, "SELECT id_jukir FROM transaksi_retribusi WHERE id = $id_setoran");
$row = mysqli_fetch_assoc($q);
$id_jukir = $row ? (int) $row['id_jukir'] : 0;

if (mysqli_query($conn, "DELETE FROM transaksi_retribusi WHERE id = $id_setoran")) {
    redirectBack('retribusi-detail.php', ['id' => $id_jukir, 'status' => 'hapus']);
} else {
    redirectBack('retribusi-detail.php', ['id' => $id_jukir, 'status' => 'error', 'msg' => mysqli_error($conn)]);
}
exit;
