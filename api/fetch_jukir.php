<?php

// Fungsi search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$kecamatan = isset($_GET['kecamatan']) ? trim($_GET['kecamatan']) : '';
$titik_parkir = isset($_GET['titik_parkir']) ? trim($_GET['titik_parkir']) : '';
$where = "WHERE 1=1";

if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $where .= " AND (jukir_utama.nama_lengkap LIKE '%$s%' 
                  OR jukir_utama.nik LIKE '%$s%'
                  OR lokasi.nama_lokasi LIKE '%$s%'
                  OR lokasi.kode_qris LIKE '%$s%')";
}

if ($kecamatan !== '') {
    $k = mysqli_real_escape_string($conn, $kecamatan);
    $where .= " AND lokasi.kecamatan = '$k'";
}

if ($titik_parkir !== '') {
    $tp = mysqli_real_escape_string($conn, $titik_parkir);
    $where .= " AND lokasi.titik_parkir = '$tp'";
}


// Endpoint jukir utama
$count_sql = "SELECT COUNT(*) AS total 
              FROM jukir_utama
              INNER JOIN lokasi ON jukir_utama.id_lokasi = lokasi.id
              LEFT JOIN koordinator_wilayah ON jukir_utama.id_korwil = koordinator_wilayah.id
              $where";
$total_result = mysqli_query($conn, $count_sql);
$total_row = mysqli_fetch_assoc($total_result)['total'];

// Pagination
$limit = 25;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$total_pages = ceil($total_row / $limit);

$sql = "SELECT 
            jukir_utama.*, 
            lokasi.nama_lokasi, 
            lokasi.kode_qris,
            lokasi.titik_parkir,
            koordinator_wilayah.wilayah AS kecamatan
        FROM jukir_utama
        INNER JOIN lokasi ON jukir_utama.id_lokasi = lokasi.id
        LEFT JOIN koordinator_wilayah ON jukir_utama.id_korwil = koordinator_wilayah.id
        $where
        ORDER BY jukir_utama.id DESC
        LIMIT $offset, $limit";

$result = mysqli_query($conn, $sql);
$list_lokasi = mysqli_query($conn, "SELECT id, nama_lokasi, kode_qris FROM lokasi ORDER BY nama_lokasi ASC");
$list_wilayah = mysqli_query($conn, "SELECT DISTINCT wilayah FROM koordinator_wilayah ORDER BY wilayah ASC");