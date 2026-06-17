<?php
$limit = 25;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM jukir_utama");
$total_row = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_row / $limit);

$bulan_ini = date('m');
$tahun_ini = date('Y');

$sql = "SELECT 
            ju.id,
            ju.nama_lengkap AS nama_utama,
            (SELECT GROUP_CONCAT(nama_pembantu SEPARATOR ', ') 
            FROM jukir_pembantu 
            WHERE id_utama = ju.id) AS nama_pembantu,
            l.nama_lokasi AS lokasi,
            ju.target_bulanan AS target,
            IFNULL(SUM(tr.jumlah_setoran), 0) AS realisasi
        FROM jukir_utama ju
        LEFT JOIN lokasi l ON ju.id_lokasi = l.id
        LEFT JOIN transaksi_retribusi tr ON ju.id = tr.id_jukir 
            AND MONTH(tr.tanggal_setoran) = MONTH(CURRENT_DATE())
            AND YEAR(tr.tanggal_setoran) = YEAR(CURRENT_DATE())
        GROUP BY ju.id, l.nama_lokasi
        LIMIT $offset, $limit";

$result = mysqli_query($conn, $sql);
$list_lokasi = mysqli_query($conn, "SELECT id, nama_lokasi, kode_qris FROM lokasi ORDER BY nama_lokasi ASC");

$q_percentage = "";